<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Category;
use App\Models\SaleItem;
use App\Models\User;
use App\Notifications\HighValueSaleNotification;
use App\Notifications\SaleCompletedNotification;
use App\Notifications\RegisterClosedNotification;
use Illuminate\Support\Facades\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use App\Models\BranchProduct;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SalesReportExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ReceiveStockService;
use App\Models\CashDrawerSession;
use App\Services\PaystackService;
use App\Services\SmsService;

class SalesController extends Controller
{
    protected $receiveStockService;
    protected $paystackService;
    protected $smsService;

    public function __construct(
        ReceiveStockService $receiveStockService,
        PaystackService $paystackService,
        SmsService $smsService
    ) {
        $this->receiveStockService = $receiveStockService;
        $this->paystackService = $paystackService;
        $this->smsService = $smsService;
    }

    /**
     * Display sales for the current user's context
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $sales = Sale::with(['branch.business', 'cashier', 'items.product'])
            ->when($user->role === 'cashier', function ($query) use ($user) {
                // Cashiers can only see their own sales
                return $query->where('cashier_id', $user->id);
            })
            ->when($user->role === 'manager', function ($query) use ($user) {
                // Managers see all sales from their branch
                return $query->where('branch_id', $user->branch_id);
            })
            ->when($user->role === 'business_admin', function ($query) use ($user) {
                // Business admins see all sales in their business
                return $query->whereHas('branch', function ($q) use ($user) {
                    $q->where('business_id', $user->business_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = [
            'total_sales' => $sales->total(),
            'total_revenue' => $sales->sum('total'),
            'total_subtotal' => $sales->sum('subtotal'),
            'total_tax_amount' => $sales->sum('tax_amount'),
            'total_cogs' => $sales->sum(function ($sale) {
                return $sale->items->sum('total_cost');
            }),
            'total_profit' => 0,
            'items_sold' => $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            }),
        ];
        // Calculate profit from subtotal (before tax)
        $summary['total_profit'] = ($summary['total_subtotal'] ?: $summary['total_revenue']) - $summary['total_cogs'];


        return view('sales.index', compact('sales', 'summary'));
    }

    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        $branches = $this->resolveBranchesForUser();
        $products = $this->buildProductCatalog($branches);
        $customers = \App\Models\Customer::active()->orderBy('name')->get();

        return view('sales.create', compact('branches', 'products', 'customers'));
    }

    /**
     * Point-of-sale terminal interface for cashiers.
     */
    public function terminal()
    {
        $branches = $this->resolveBranchesForUser();
        $catalog = $this->buildProductCatalog($branches, true);

        $user = Auth::user();
        $categoryId = request()->input('category_id');
        $filterUncategorized = $categoryId === 'null';
        $branchIds = $branches->pluck('id');

        // Check if cashier has an active cash drawer session for today
        $hasActiveSession = true; // Default true for non-cashiers
        $activeSession = null;
        
        if ($user->role === 'cashier') {
            $activeSession = CashDrawerSession::where('user_id', $user->id)
                ->where('session_date', Carbon::today())
                ->where('status', 'open')
                ->first();
            
            $hasActiveSession = !is_null($activeSession);
        }

        // Get categories that have products available in the accessible branches
        // This ensures only relevant categories are shown in the filter
        $categories = Category::forBusiness($user->business_id)
            ->active()
            ->parents()
            ->whereHas('products', function ($query) use ($branchIds) {
                $query->whereHas('branchProducts', function ($q) use ($branchIds) {
                    $q->whereIn('branch_id', $branchIds);
                });
            })
            ->withCount([
                'products' => function ($query) use ($branchIds) {
                    $query->whereHas('branchProducts', function ($q) use ($branchIds) {
                        $q->whereIn('branch_id', $branchIds);
                    });
                }
            ])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        // Count uncategorized products
    $uncategorizedCount = 0;
    if ($user->role === 'cashier' && $user->branch_id) {
        $uncategorizedCount = BranchProduct::where('branch_id', $user->branch_id)
            ->where('stock_quantity', '>', 0)
            ->whereHas('product', function($q) {
                $q->whereNull('category_id');
            })->count();

        $productsQuery = BranchProduct::where('branch_id', $user->branch_id)
            ->where('stock_quantity', '>', 0)
            ->with(['product.category']);
        if ($filterUncategorized) {
            $productsQuery->whereHas('product', function($q) {
                $q->whereNull('category_id');
            });
        } elseif ($categoryId) {
            $productsQuery->whereHas('product', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        $products = $productsQuery->paginate(15);

    } else {
        $uncategorizedCount = BranchProduct::whereHas('product', function($q) {
            $q->whereNull('category_id');
        })
        ->where('stock_quantity', '>', 0)
        ->count();

        $productsQuery = BranchProduct::with(['product.category', 'branch'])
            ->where('stock_quantity', '>', 0);
            
        if ($filterUncategorized) {
            $productsQuery->whereHas('product', function($q) {
                $q->whereNull('category_id');
            });
        } elseif ($categoryId) {
            $productsQuery->whereHas('product', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        $products = $productsQuery->paginate(15);
    }

    return view('sales.terminal', [
        'branches' => $branches,
        'catalog' => $catalog,
        'categories' => $categories,
        'selectedCategory' => $categoryId,
        'products' => $products,
        'uncategorizedCount' => $uncategorizedCount,
        'hasActiveSession' => $hasActiveSession,
        'activeSession' => $activeSession,
    ]);
    }


    /**
     * Open cash drawer for the current cashier
     */
    public function openDrawer(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'cashier') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'opening_notes' => 'nullable|string|max:500',
        ]);

        try {
            // Check if there's already an open session today
            $existingSession = CashDrawerSession::where('user_id', $user->id)
                ->where('session_date', Carbon::today())
                ->where('status', 'open')
                ->first();

            if ($existingSession) {
                return response()->json([
                    'error' => 'You already have an open cash drawer session for today.'
                ], 422);
            }

            // Create new cash drawer session
            $session = CashDrawerSession::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'opening_amount' => $validated['opening_amount'],
                'session_date' => Carbon::today(),
                'opened_at' => Carbon::now(),
                'status' => 'open',
                'opening_notes' => $validated['opening_notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash drawer opened successfully.',
                'session' => $session,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to open cash drawer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close cash drawer and reconcile
     */
    public function closeDrawer(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'cashier') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'actual_amount' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        try {
            // Find the active session for today
            $session = CashDrawerSession::where('user_id', $user->id)
                ->where('session_date', Carbon::today())
                ->where('status', 'open')
                ->first();

            if (!$session) {
                return response()->json([
                    'error' => 'No open cash drawer session found for today.'
                ], 422);
            }

            // Calculate expected amount: opening amount + cash sales
            $cashSales = Sale::where('cashier_id', $user->id)
                ->where('payment_method', 'cash')
                ->whereDate('created_at', $session->session_date)
                ->sum('total');

            $expectedAmount = $session->opening_amount + $cashSales;
            $actualAmount = $validated['actual_amount'];
            $difference = $actualAmount - $expectedAmount;

            // Handle unique constraint: Check if closed session exists and delete it first
            // The unique constraint on (user_id, session_date, status) prevents updating
            // from 'open' to 'closed' if a closed session already exists
            $existingClosedSession = CashDrawerSession::where('user_id', $user->id)
                ->where('session_date', Carbon::today())
                ->where('status', 'closed')
                ->first();

            DB::transaction(function () use ($session, $expectedAmount, $actualAmount, $difference, $validated, $existingClosedSession) {
                if ($existingClosedSession) {
                    Log::warning('Deleting existing closed cash drawer session to allow status update', [
                        'existing_session_id' => $existingClosedSession->id,
                        'user_id' => $existingClosedSession->user_id,
                        'session_date' => $existingClosedSession->session_date,
                    ]);
                    $existingClosedSession->delete();
                }

                $session->update([
                    'expected_amount' => $expectedAmount,
                    'actual_amount' => $actualAmount,
                    'difference' => $difference,
                    'closed_at' => Carbon::now(),
                    'status' => 'closed',
                    'closing_notes' => $validated['closing_notes'] ?? null,
                ]);
            });

            // Notify Manager(s) of the branch about the closure
            $managers = User::where('branch_id', $user->branch_id)
                ->where('role', 'manager')
                ->get();
            
            // Also notify Business Admin if needed, or just managers
            // For now, let's notify managers
            if ($managers->count() > 0) {
                Notification::send($managers, new RegisterClosedNotification($session, [
                    'opening_amount' => $session->opening_amount,
                    'cash_sales' => $cashSales,
                    'expected_amount' => $expectedAmount,
                    'actual_amount' => $actualAmount,
                    'difference' => $difference,
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Cash drawer closed successfully.',
                'session' => $session->fresh(),
                'summary' => [
                    'opening_amount' => $session->opening_amount,
                    'cash_sales' => $cashSales,
                    'expected_amount' => $expectedAmount,
                    'actual_amount' => $actualAmount,
                    'difference' => $difference,
                    'is_over' => $difference > 0,
                    'is_short' => $difference < 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to close cash drawer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current cash drawer session status
     */
    public function getDrawerStatus(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'cashier') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $session = CashDrawerSession::where('user_id', $user->id)
                ->where('session_date', Carbon::today())
                ->where('status', 'open')
                ->first();

            if (!$session) {
                return response()->json([
                    'has_session' => false,
                    'message' => 'No open cash drawer session'
                ]);
            }

            // Calculate current expected amount
            $cashSales = Sale::where('cashier_id', $user->id)
                ->where('payment_method', 'cash')
                ->whereDate('created_at', $session->session_date)
                ->sum('total');

            $expectedAmount = $session->opening_amount + $cashSales;
            $totalSales = Sale::where('cashier_id', $user->id)
                ->whereDate('created_at', $session->session_date)
                ->count();
            $totalRevenue = Sale::where('cashier_id', $user->id)
                ->whereDate('created_at', $session->session_date)
                ->sum('total');

            return response()->json([
                'has_session' => true,
                'session' => $session,
                'current_expected' => $expectedAmount,
                'opening_amount' => $session->opening_amount,
                'cash_sales' => $cashSales,
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get drawer status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created sale with COGS tracking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string|in:cash,card,mobile_money',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'amount_tendered' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string',
        ]);

        // Verify Paystack Payment if reference is provided
        $paystackData = null;
        if (!empty($validated['payment_reference'])) {
            $verification = $this->paystackService->verifyTransaction($validated['payment_reference']);
            if (($verification['status'] ?? false) && ($verification['data']['status'] ?? '') === 'success') {
                $paystackData = $verification['data'];
                // Verify amount matches (optional but recommended)
                // $paidAmount = $paystackData['amount'] / 100;
            } else {
                Log::warning('Invalid Paystack reference used in sale attempt', ['ref' => $validated['payment_reference']]);
                // We could block here, but for now we proceed or handle as needed. 
                // Any logic to block the sale if verification fails?
                // For safety, if a reference is sent but invalid, we probably shouldn't assume it's paid.
                // However, to keep it simple as requested, we'll verify primarily for the phone number extraction.
            }
        }

    try {
    $sale = DB::transaction(function () use ($validated) {
                $sale = Sale::create([
                    'branch_id' => $validated['branch_id'],
                    'customer_id' => $validated['customer_id'] ?? null,
                    'cashier_id' => Auth::id(),
                    'payment_method' => $validated['payment_method'],
                    'total' => 0,
                ]);

                $totalAmount = 0;

                foreach ($validated['items'] as $itemData) {
                    $cogsResult = $this->receiveStockService->processSale(
                        $validated['branch_id'],
                        $itemData['product_id'],
                        $itemData['quantity']
                    );

                    $itemTotal = $itemData['quantity'] * $itemData['price'];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'total' => $itemTotal,
                        'unit_cost' => $cogsResult['unit_cost'],
                        'total_cost' => $cogsResult['total_cost'],
                    ]);

                    $totalAmount += $itemTotal;
                }

                // Calculate taxes automatically after all items are added
                $sale->calculateTotals();

                // Store amount tendered and change
                $sale->amount_tendered = $validated['amount_tendered'];
                $sale->change = max(0, $validated['amount_tendered'] - $sale->total);
                $sale->save();

                // Send notification for high-value sales (> GHS 500)
                if ($sale->total > 500) {
                    $this->notifyHighValueSale($sale);
                }

                // Notify Managers about every sale (General Notification)
                $managers = User::where('branch_id', $sale->branch_id)
                    ->where('role', 'manager')
                    ->get();
                
                if ($managers->count() > 0) {
                    Notification::send($managers, new SaleCompletedNotification($sale));
                }

                // Log sale creation activity
                ActivityLogger::logModel('create', $sale, [], [
                    'total' => $sale->total,
                    'items_count' => count($validated['items']),
                    'payment_method' => $sale->payment_method,
                    'branch_id' => $sale->branch_id,
                ]);



                return $sale; // Return instance for use outside transaction
            });

            // Post-transaction actions (SMS)
            if ($paystackData && $validated['payment_method'] === 'mobile_money') {
                // Try to get phone number from Paystack response
                $customerPhone = $paystackData['customer']['phone'] ?? null;
                
                // Sometimes it's in authorization for MoMo
                if (!$customerPhone && isset($paystackData['authorization']['mobile_money_number'])) {
                    $customerPhone = $paystackData['authorization']['mobile_money_number'];
                }

                if ($customerPhone) {
                    $branchName = $sale->branch->name ?? 'POS Shop';
                    $amount = number_format($sale->total, 2);
                    $date = $sale->created_at->format('d/m/y H:i');
                    $msg = "Payment Received: GHS {$amount} at {$branchName} on {$date}. Ref: {$sale->sale_number}. Thanks for your patronage!";
                    
                    $this->smsService->sendSms($customerPhone, $msg);
                    Log::info("Sent MoMo receipt SMS to {$customerPhone}");
                } else {
                    Log::info("Could not extract phone number for MoMo SMS. Ref: " . ($validated['payment_reference'] ?? 'N/A'));
                }
            }

            if ($request->expectsJson()) {
                $taxBreakdown = $sale->getTaxBreakdown();
                return response()->json([
                    'message' => 'Sale completed successfully!',
                    'sale' => [
                        'id' => $sale->id,
                        'subtotal' => $taxBreakdown['subtotal'],
                        'tax_amount' => $taxBreakdown['tax_amount'],
                        'total' => $taxBreakdown['total'],
                        'tax_components' => $taxBreakdown['tax_components'],
                        'payment_method' => $sale->payment_method,
                        'amount_tendered' => $sale->amount_tendered,
                        'change' => $sale->change,
                        'created_at' => $sale->created_at->toDateTimeString(),
                        'branch' => optional($sale->branch)->display_label,
                        'cashier' => optional($sale->cashier)->name,
                    ],
                    'receipt_url' => route('sales.receipt', $sale),
                    'redirect_url' => route('sales.show', $sale),
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Sale completed successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                report($e);

                return response()->json([
                    'message' => 'Failed to complete sale.',
                    'errors' => [$e->getMessage()],
                ], 422);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to complete sale: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale)
    {
        $user = Auth::user();
        
        // Authorization: Cashiers can only view their own sales
        if ($user->role === 'cashier' && $sale->cashier_id !== $user->id) {
            return redirect()->back()->with('error', 'You can only view your own sales.');
        }
        
        // Managers can only view sales from their branch
        if ($user->role === 'manager' && $sale->branch_id !== $user->branch_id) {
            return redirect()->back()->with('error', 'You can only view sales from your branch.');
        }

        $sale->load(['items.product', 'branch.business', 'cashier', 'customer']);
        // Get comprehensive analysis including tax breakdown
        $profitAnalysis = $sale->getProfitAnalysis();
        $taxBreakdown = $sale->getTaxBreakdown();
        
        // Ensure tax_components is always an array
        if (!isset($taxBreakdown['tax_components']) || !is_array($taxBreakdown['tax_components'])) {
            $taxBreakdown['tax_components'] = [];
        }
        
        $totals = array_merge($profitAnalysis, $taxBreakdown, [
            'amount_tendered' => $sale->amount_tendered,
            'change' => $sale->change,
        ]);
        return view('sales.show', compact('sale', 'totals'));
    }

    /**
     * Printable receipt for a completed sale.
     */
    public function receipt(Sale $sale)
    {
        $user = Auth::user();
        
        // Authorization: Cashiers can only view receipts for their own sales
        if ($user->role === 'cashier' && $sale->cashier_id !== $user->id) {
            return redirect()->back()->with('error', 'You can only view receipts for your own sales.');
        }
        
        // Managers can only view receipts from their branch
        if ($user->role === 'manager' && $sale->branch_id !== $user->branch_id) {
            return redirect()->back()->with('error', 'You can only view receipts from your branch.');
        }

        $sale->load(['items.product', 'branch.business', 'cashier', 'customer']);
        $taxBreakdown = $sale->getTaxBreakdown();
        $totals = [
            'subtotal' => $taxBreakdown['subtotal'],
            'tax_components' => $taxBreakdown['tax_components'],
            'tax_amount' => $taxBreakdown['tax_amount'],
            'total' => $taxBreakdown['total'],
            'cogs' => $sale->items->sum('total_cost'),
            'amount_tendered' => $sale->amount_tendered,
            'change' => $sale->change,
        ];
        return view('sales.receipt', [
            'sale' => $sale,
            'totals' => $totals,
        ]);
    }

    /**
     * API endpoint to get available stock for a product at a branch
     */
    public function getProductStock(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $branchProduct = BranchProduct::where('branch_id', $request->branch_id)
            ->where('product_id', $request->product_id)
            ->with('product')
            ->first();

        if (!$branchProduct) {
            return response()->json([
                'available' => false,
                'message' => 'Product not available at this branch'
            ]);
        }

        return response()->json([
            'available' => $branchProduct->stock_quantity > 0,
            'stock_quantity' => $branchProduct->stock_quantity,
            'selling_price' => $branchProduct->price ?? $branchProduct->selling_price,
            'cost_price' => $branchProduct->cost_price,
            'product_name' => $branchProduct->product->name,
            'sku' => $branchProduct->product->sku,
        ]);
    }

    /**
     * API endpoint to calculate taxes for a given subtotal
     */
    public function calculateTaxes(Request $request)
    {
        $request->validate([
            'subtotal' => 'required|numeric|min:0',
        ]);

        $subtotal = $request->subtotal;
        
        // Use the same tax calculation as Sale model
        $taxRate = Sale::DEFAULT_TAX_RATE;
        $taxAmount = ($subtotal * $taxRate) / 100;
        $total = $subtotal + $taxAmount;

        return response()->json([
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'tax_components' => [
                [
                    'name' => 'Sales Tax',
                    'rate' => $taxRate,
                    'amount' => round($taxAmount, 2)
                ]
            ],
        ]);
    }

    //export sales report methods
    public function exportCsv(Request $request)
    {
        // Prevent cashiers from accessing sales reports
        $user = Auth::user();
        if ($user->role === 'cashier') {
            return redirect()->back()->with('error', 'You do not have permission to export sales reports.');
        }

        $data = $this->buildReportData($request);

        $filename = sprintf(
            'sales-report-%s-%s.csv',
            $data['startDate']->format('Ymd'),
            $data['endDate']->format('Ymd')
        );

        return Excel::download(
            new SalesReportExport(
                $data['sales'], 
                $data['summary'], 
                $data['startDate'], 
                $data['endDate'],
                $data['branchComparison'],
                $data['topProducts']
            ),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        // Prevent cashiers from accessing sales reports
        $user = Auth::user();
        if ($user->role === 'cashier') {
            return redirect()->back()->with('error', 'You do not have permission to export sales reports.');
        }

        // $data = $this->buildReportData($request);

        // $pdf = Pdf::loadView('sales.pdf', [
        //     'sales' => $data['sales'],
        //     'summary' => $data['summary'],
        //     'startDate' => $data['startDate'],
        //     'endDate' => $data['endDate'],
        //     'chartData' => $data['chartData'],
        // ])->setPaper('a4', 'portrait');

        // return $pdf->download(sprintf(
        //     'sales-report-%s-%s.pdf',
        //     $data['startDate']->format('Ymd'),
        //     $data['endDate']->format('Ymd')
        // ));
        $data = $this->buildReportData($request);

        $pdf = Pdf::loadView('sales.pdf', $data)
            ->setPaper('a4', 'landscape') // Changed to landscape for better table fit
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        return $pdf->download(sprintf(
            'sales-report-%s-%s.pdf',
            $data['startDate']->format('Ymd'),
            $data['endDate']->format('Ymd')
        ));
    }

    protected function resolveBranchesForUser()
    {
        $user = Auth::user();

        if ($user && $user->branch_id) {
            return Branch::with('business:id,name')->where('id', $user->branch_id)->get();
        }

        return Branch::with('business:id,name')->get();
    }

    protected function buildProductCatalog($branches, bool $onlyInStock = true)
    {
        $branchIds = $branches->pluck('id');

        if ($branchIds->isEmpty()) {
            return collect();
        }

    $query = BranchProduct::with(['product', 'branch.business'])
            ->whereIn('branch_id', $branchIds);

        if ($onlyInStock) {
            $query->where('stock_quantity', '>', 0);
        }

        return $query->get()->map(function (BranchProduct $branchProduct) {
            $price = $branchProduct->price ?? data_get($branchProduct, 'selling_price', 0);

            return [
                'id' => $branchProduct->product_id,
                'name' => optional($branchProduct->product)->name,
                'sku' => optional($branchProduct->product)->sku,
                'barcode' => optional($branchProduct->product)->barcode,
                'branch_id' => $branchProduct->branch_id,
                'branch_name' => optional($branchProduct->branch)->display_label,
                'stock_quantity' => $branchProduct->stock_quantity,
                'selling_price' => $price,
                'price' => $price,
                'cost_price' => $branchProduct->cost_price,
                'image' => optional($branchProduct->product)->image,
                // expose category info for client-side filtering
                'category_id' => optional($branchProduct->product)->category_id,
                'category_name' => optional(optional($branchProduct->product)->category)->name,
            ];
        })->values();
    }


    /**
     * Sales report with margin analysis
     */
    // protected function buildReportData(Request $request): array
    // {
    //     $user = Auth::user();
    //     $startDateInput = $request->input('start_date');
    //     $endDateInput = $request->input('end_date');

    //     $startDate = $startDateInput
    //         ? Carbon::parse($startDateInput)->startOfDay()
    //         : now()->startOfMonth();

    //     $endDate = $endDateInput
    //         ? Carbon::parse($endDateInput)->endOfDay()
    //         : now()->endOfMonth();

    //     $sales = Sale::with(['items', 'branch.business', 'cashier'])
    //         ->when($user->branch_id, function ($query) use ($user) {
    //             return $query->where('branch_id', $user->branch_id);
    //         })
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->orderBy('created_at')
    //         ->get();

    //     $summary = [
    //         'total_sales' => $sales->count(),
    //         'total_revenue' => $sales->sum('total'),
    //         'total_cogs' => $sales->sum(function ($sale) {
    //             return $sale->items->sum('total_cost');
    //         }),
    //         'total_profit' => 0,
    //         'average_margin' => 0,
    //     ];

    //     $summary['total_profit'] = $summary['total_revenue'] - $summary['total_cogs'];
    //     if ($summary['total_revenue'] > 0) {
    //         $summary['average_margin'] = ($summary['total_profit'] / $summary['total_revenue']) * 100;
    //     }

    //     $dailyData = $sales->groupBy(fn ($sale) => $sale->created_at->format('Y-m-d'))
    //         ->sortKeys();

    //     $chartData = [
    //         'labels' => $dailyData->keys()->values()->all(),
    //         'revenue' => $dailyData->map(fn ($daySales) => (float) $daySales->sum('total'))->values()->all(),
    //         'cogs' => $dailyData->map(fn ($daySales) => (float) $daySales->sum(fn ($sale) => $sale->items->sum('total_cost')))->values()->all(),
    //         'profit' => $dailyData->map(function ($daySales) {
    //             $revenue = $daySales->sum('total');
    //             $cogs = $daySales->sum(fn ($sale) => $sale->items->sum('total_cost'));
    //             return (float) ($revenue - $cogs);
    //         })->values()->all(),
    //         'loss' => $dailyData->map(function ($daySales) {
    //             $revenue = $daySales->sum('total');
    //             $cogs = $daySales->sum(fn ($sale) => $sale->items->sum('total_cost'));
    //             $net = (float) ($revenue - $cogs);
    //             return $net < 0 ? abs($net) : 0.0;
    //         })->values()->all(),
    //         'margin' => $dailyData->map(function ($daySales) {
    //             $revenue = $daySales->sum('total');
    //             if ($revenue <= 0) {
    //                 return 0.0;
    //             }

    //             $cogs = $daySales->sum(fn ($sale) => $sale->items->sum('total_cost'));
    //             $profit = $revenue - $cogs;

    //             return round(($profit / $revenue) * 100, 2);
    //         })->values()->all(),
    //     ];

    //     return [
    //         'startDate' => $startDate,
    //         'endDate' => $endDate,
    //         'sales' => $sales,
    //         'summary' => $summary,
    //         'chartData' => $chartData,
    //     ];
    // }

    /**
     * Build comprehensive report data with optimized queries
     */
    protected function buildReportData(Request $request): array
    {
        $user = Auth::user();
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');
        
        // Additional Filters
        $branchIdInput = $request->input('branch_id');
        $categoryIdInput = $request->input('category_id');
        $paymentMethodInput = $request->input('payment_method');

        // Date range handling
        $startDate = $startDateInput
            ? Carbon::parse($startDateInput)->startOfDay()
            : now()->startOfDay();

        $endDate = $endDateInput
            ? Carbon::parse($endDateInput)->endOfDay()
            : now()->endOfDay();

        // Base query - respect role-based restrictions
        $baseQuery = Sale::with(['items.product.primarySupplier', 'branch', 'cashier'])
            ->when($user->role === 'manager' || $user->role === 'cashier', function ($query) use ($user) {
                // Managers and cashiers only see their branch
                return $query->where('branch_id', $user->branch_id);
            })
            ->when($user->role === 'business_admin', function ($query) use ($user) {
                // Business admins see all branches in their business
                return $query->whereHas('branch', function ($q) use ($user) {
                    $q->where('business_id', $user->business_id);
                });
            })
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply additional filters
        if ($branchIdInput && ($user->role === 'business_admin' || $user->role === 'superadmin')) {
            $baseQuery->where('branch_id', $branchIdInput);
        }

        if ($categoryIdInput) {
            $baseQuery->whereHas('items.product', function ($q) use ($categoryIdInput) {
                $q->where('category_id', $categoryIdInput);
            });
        }

        if ($paymentMethodInput) {
            $baseQuery->where('payment_method', $paymentMethodInput);
        }

        // Get detailed sales for table
        $sales = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate summary metrics (optimized aggregation)
        $summary = $this->calculateSummary($sales);

        // Generate chart data
        $chartData = $this->generateChartData($sales);

        // Branch comparison (only for users with access to multiple branches)
        $branchComparison = null;
        if ($user->role === 'business_admin' || $user->role === 'superadmin') {
            $branchComparison = $this->generateBranchComparison($startDate, $endDate, $user);
        }

        // Top products analysis
        $topProducts = $this->getTopProducts($sales);

        // Supplier breakdown
        $supplierBreakdown = $this->getSupplierBreakdown($sales);

        // Cashier performance
        $cashierStats = $this->getCashierPerformance($sales);

        // Period comparison
        $periodComparison = $this->compareToPreviousPeriod($startDate, $endDate, $user);

        // Filter Data for Dropdowns
        $branches = collect();
        if ($user->role === 'business_admin' || $user->role === 'superadmin') {
            $branches = Branch::where('business_id', $user->business_id)->get();
        }

        $categories = Category::where('business_id', $user->business_id)->get();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sales' => $sales,
            'summary' => $summary,
            'chartData' => $chartData,
            'branchComparison' => $branchComparison,
            'topProducts' => $topProducts,
            'supplierBreakdown' => $supplierBreakdown,
            'cashierStats' => $cashierStats,
            'periodComparison' => $periodComparison,
            'userBranchId' => $user->branch_id,
            'branches' => $branches,
            'categories' => $categories,
            'filters' => [
                'branch_id' => $branchIdInput,
                'category_id' => $categoryIdInput,
                'payment_method' => $paymentMethodInput,
            ]
        ];
    }

     /**
     * Calculate summary metrics with optimized aggregation
     */
    protected function calculateSummary($sales): array
    {
        // Single pass through sales collection
        $metrics = $sales->reduce(function ($carry, $sale) {
            $cogs = $sale->items->sum('total_cost');
            $carry['revenue'] += $sale->total;
            $carry['cogs'] += $cogs;
            $carry['items'] += $sale->items->count();
            $carry['quantity'] += $sale->items->sum('quantity');
            return $carry;
        }, ['revenue' => 0, 'cogs' => 0, 'items' => 0, 'quantity' => 0]);

        $totalRevenue = $metrics['revenue'];
        $totalCogs = $metrics['cogs'];
        $totalProfit = $totalRevenue - $totalCogs;
        $averageMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
        $salesCount = $sales->count();

        return [
            'total_sales' => $salesCount,
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalProfit,
            'average_margin' => $averageMargin,
            'average_transaction' => $salesCount > 0 ? $totalRevenue / $salesCount : 0,
            'total_items_sold' => $metrics['items'],
            'total_quantity_sold' => $metrics['quantity'],
        ];
    }

    /**
     * Generate daily chart data using optimized aggregation
     */
    protected function generateChartData($sales): array
    {
        // Pre-calculate COGS for each sale to avoid nested loops
        $salesWithCogs = $sales->map(function ($sale) {
            $cogs = $sale->items->sum('total_cost');
            return [
                'date' => $sale->created_at->format('Y-m-d'),
                'revenue' => (float) $sale->total,
                'cogs' => (float) $cogs,
                'profit' => (float) ($sale->total - $cogs),
            ];
        });

        // Group by date and aggregate
        $dailyData = $salesWithCogs->groupBy('date')
            ->map(function ($dayData) {
                $revenue = $dayData->sum('revenue');
                $cogs = $dayData->sum('cogs');
                $profit = $dayData->sum('profit');
                $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;
                
                return [
                    'revenue' => $revenue,
                    'cogs' => $cogs,
                    'profit' => $profit,
                    'margin' => $margin,
                    'count' => $dayData->count(),
                ];
            })
            ->sortKeys();

        return [
            'labels' => $dailyData->keys()->map(fn($date) => Carbon::parse($date)->format('M d'))->values()->all(),
            'revenue' => $dailyData->pluck('revenue')->values()->all(),
            'cogs' => $dailyData->pluck('cogs')->values()->all(),
            'profit' => $dailyData->pluck('profit')->values()->all(),
            'margin' => $dailyData->pluck('margin')->values()->all(),
            'transaction_count' => $dailyData->pluck('count')->values()->all(),
        ];
    }

    /**
     * Generate branch comparison data with optimized queries
     */
    protected function generateBranchComparison(Carbon $startDate, Carbon $endDate, $user = null): array
    {
        $branches = Branch::with(['business'])
            ->when($user && $user->role === 'business_admin', function ($query) use ($user) {
                return $query->where('business_id', $user->business_id);
            })
            ->whereHas('sales', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        $comparison = [];

        foreach ($branches as $branch) {
            $sales = Sale::with('items')
                ->where('branch_id', $branch->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Aggregate in single pass
            $metrics = $sales->reduce(function ($carry, $sale) {
                $cogs = $sale->items->sum('total_cost');
                $carry['revenue'] += $sale->total;
                $carry['cogs'] += $cogs;
                $carry['count']++;
                return $carry;
            }, ['revenue' => 0, 'cogs' => 0, 'count' => 0]);

            $revenue = $metrics['revenue'];
            $cogs = $metrics['cogs'];
            $profit = $revenue - $cogs;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            $comparison[] = [
                'branch_name' => $branch->display_label,
                'sales_count' => $metrics['count'],
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $profit,
                'margin' => $margin,
            ];
        }

        // Sort by revenue descending
        usort($comparison, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        
        return $comparison;
    }

    /**
     * Get top performing products with optimized aggregation
     */
    protected function getTopProducts($sales, $limit = 10): array
    {
        $productStats = [];

        // Single pass through all sale items
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productId = $item->product_id;
                
                if (!isset($productStats[$productId])) {
                    $product = $item->product;
                    $productStats[$productId] = [
                        'product_name' => $product->name ?? 'Unknown',
                        'quantity_sold' => 0,
                        'revenue' => 0,
                        'profit' => 0,
                        'is_local' => $product->is_local_supplier_product ?? false,
                        'supplier_name' => $product->primarySupplier->name ?? null,
                    ];
                }

                $productStats[$productId]['quantity_sold'] += $item->quantity;
                $productStats[$productId]['revenue'] += $item->total;
                $productStats[$productId]['profit'] += ($item->total - $item->total_cost);
            }
        }

        // Sort by revenue and limit results
        usort($productStats, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        
        return array_slice($productStats, 0, $limit);
    }

    /**
     * Generate supplier breakdown statistics
     */
    protected function getSupplierBreakdown($sales): array
    {
        $localStats = [
            'quantity_sold' => 0,
            'revenue' => 0,
            'profit' => 0,
            'products_count' => 0,
        ];

        $centralStats = [
            'quantity_sold' => 0,
            'revenue' => 0,
            'profit' => 0,
            'products_count' => 0,
        ];

        $productIds = ['local' => [], 'central' => []];

        // Single pass through all sale items
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $product = $item->product;
                $isLocal = $product->is_local_supplier_product ?? false;

                if ($isLocal) {
                    $localStats['quantity_sold'] += $item->quantity;
                    $localStats['revenue'] += $item->total;
                    $localStats['profit'] += ($item->total - $item->total_cost);
                    if (!in_array($product->id, $productIds['local'])) {
                        $productIds['local'][] = $product->id;
                        $localStats['products_count']++;
                    }
                } else {
                    $centralStats['quantity_sold'] += $item->quantity;
                    $centralStats['revenue'] += $item->total;
                    $centralStats['profit'] += ($item->total - $item->total_cost);
                    if (!in_array($product->id, $productIds['central'])) {
                        $productIds['central'][] = $product->id;
                        $centralStats['products_count']++;
                    }
                }
            }
        }

        // Calculate margins
        $localMargin = $localStats['revenue'] > 0 
            ? ($localStats['profit'] / $localStats['revenue']) * 100 
            : 0;
        $centralMargin = $centralStats['revenue'] > 0 
            ? ($centralStats['profit'] / $centralStats['revenue']) * 100 
            : 0;

        return [
            'local' => array_merge($localStats, ['margin' => $localMargin]),
            'central' => array_merge($centralStats, ['margin' => $centralMargin]),
            'total_revenue' => $localStats['revenue'] + $centralStats['revenue'],
        ];
    }

    /**
     * Get cashier performance statistics with optimized grouping
     */
    protected function getCashierPerformance($sales): array
    {
        $cashierStats = [];

        // Single pass aggregation
        foreach ($sales as $sale) {
            $cashierId = $sale->cashier_id;
            $cogs = $sale->items->sum('total_cost');
            
            if (!isset($cashierStats[$cashierId])) {
                $cashierStats[$cashierId] = [
                    'cashier_name' => $sale->cashier->name ?? 'Unknown',
                    'branch_name' => optional($sale->branch)->display_label ?? 'No Branch',
                    'sales_count' => 0,
                    'revenue' => 0,
                    'cogs' => 0,
                ];
            }
            
            $cashierStats[$cashierId]['sales_count']++;
            $cashierStats[$cashierId]['revenue'] += $sale->total;
            $cashierStats[$cashierId]['cogs'] += $cogs;
        }

        // Calculate profit and avg_transaction, then sort by revenue
        $results = array_map(function ($stats) {
            $profit = $stats['revenue'] - $stats['cogs'];
            $avgTransaction = $stats['sales_count'] > 0 ? $stats['revenue'] / $stats['sales_count'] : 0;
            
            return [
                'cashier_name' => $stats['cashier_name'],
                'branch_name' => $stats['branch_name'],
                'sales_count' => $stats['sales_count'],
                'revenue' => $stats['revenue'],
                'profit' => $profit,
                'avg_transaction' => $avgTransaction,
            ];
        }, $cashierStats);

        usort($results, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        
        return $results;
    }

    /**
     * Compare current period to previous period with optimized queries
     */
    protected function compareToPreviousPeriod(Carbon $startDate, Carbon $endDate, $user): array
    {
        // Calculate the duration of the current period
        $days = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates
        
        // Previous period ends one day before current period starts
        $previousEnd = $startDate->copy()->subDay()->endOfDay();
        // Previous period starts N days before that
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        $previousSales = Sale::with('items')
            ->when($user->role === 'manager' || $user->role === 'cashier', function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->when($user->role === 'business_admin', function ($query) use ($user) {
                return $query->whereHas('branch', function ($q) use ($user) {
                    $q->where('business_id', $user->business_id);
                });
            })
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->get();

        // Single pass aggregation
        $metrics = $previousSales->reduce(function ($carry, $sale) {
            $cogs = $sale->items->sum('total_cost');
            $carry['revenue'] += $sale->total;
            $carry['cogs'] += $cogs;
            return $carry;
        }, ['revenue' => 0, 'cogs' => 0]);

        return [
            'previous_revenue' => $metrics['revenue'],
            'previous_profit' => $metrics['revenue'] - $metrics['cogs'],
            'previous_sales_count' => $previousSales->count(),
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
        ];
    }

    public function report(Request $request)
    {
        // Prevent cashiers from accessing sales reports
        $user = Auth::user();
        if ($user->role === 'cashier') {
            return redirect()->back()->with('error', 'You do not have permission to access sales reports.');
        }

        $data = $this->buildReportData($request);

        return view('sales.report', $data);
    }

    /**
     * Notify business admin and managers about high-value sale
     */
    protected function notifyHighValueSale(Sale $sale)
    {
        try {
            $branch = $sale->branch;
            
            // Notify business admin
            $businessAdmin = User::where('role', 'business_admin')
                ->where('business_id', $branch->business_id)
                ->first();
            
            if ($businessAdmin) {
                $businessAdmin->notify(new HighValueSaleNotification($sale));
            }
            
            // Notify branch manager
            $manager = User::where('role', 'manager')
                ->where('branch_id', $branch->id)
                ->first();
            
            if ($manager) {
                $manager->notify(new HighValueSaleNotification($sale));
            }
        } catch (\Exception $e) {
            logger()->error('Failed to send high value sale notification: ' . $e->getMessage());
        }
    }
}