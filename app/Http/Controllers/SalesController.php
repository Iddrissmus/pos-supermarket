<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Services\ReceiveStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class SalesController extends Controller
{
    protected $receiveStockService;

    public function __construct(ReceiveStockService $receiveStockService)
    {
        $this->receiveStockService = $receiveStockService;
    }

    /**
     * Display sales for the current user's context
     */
    public function index(Request $request)
    {
        $user = Auth::user();
    $sales = Sale::with(['branch.business', 'cashier', 'items.product'])
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
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
        $catalog = $this->buildProductCatalog($branches, false);

        return view('sales.terminal', [
            'branches' => $branches,
            'catalog' => $catalog,
        ]);
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
        ]);

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

                return $sale->fresh(['items.product', 'branch.business', 'cashier']);
            });

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
        $sale->load(['items.product', 'branch.business', 'cashier', 'customer']);
        
        // Get comprehensive analysis including tax breakdown
        $profitAnalysis = $sale->getProfitAnalysis();
        $taxBreakdown = $sale->getTaxBreakdown();
        
        $totals = array_merge($profitAnalysis, [
            'tax_breakdown' => $taxBreakdown,
        ]);

        return view('sales.show', compact('sale', 'totals'));
    }

    /**
     * Printable receipt for a completed sale.
     */
    public function receipt(Sale $sale)
    {
        $sale->load(['items.product', 'branch.business', 'cashier', 'customer']);

        $taxBreakdown = $sale->getTaxBreakdown();
        $totals = [
            'subtotal' => $taxBreakdown['subtotal'],
            'tax_components' => $taxBreakdown['tax_components'],
            'tax_amount' => $taxBreakdown['tax_amount'],
            'total' => $taxBreakdown['total'],
            'cogs' => $sale->items->sum('total_cost'),
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
        $data = $this->buildReportData($request);

        $filename = sprintf(
            'sales-report-%s-%s.csv',
            $data['startDate']->format('Ymd'),
            $data['endDate']->format('Ymd')
        );

        return Excel::download(
            new SalesReportExport($data['sales'], $data['summary'], $data['startDate'], $data['endDate']),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);

        $pdf = Pdf::loadView('sales.pdf', [
            'sales' => $data['sales'],
            'summary' => $data['summary'],
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
            'chartData' => $data['chartData'],
        ])->setPaper('a4', 'portrait');

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
                'branch_id' => $branchProduct->branch_id,
                'branch_name' => optional($branchProduct->branch)->display_label,
                'stock_quantity' => $branchProduct->stock_quantity,
                'selling_price' => $price,
                'price' => $price,
                'cost_price' => $branchProduct->cost_price,
                'image' => optional($branchProduct->product)->image,
            ];
        })->values();
    }


    /**
     * Sales report with margin analysis
     */
    protected function buildReportData(Request $request): array
    {
        $user = Auth::user();
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $startDate = $startDateInput
            ? Carbon::parse($startDateInput)->startOfDay()
            : now()->startOfMonth();

        $endDate = $endDateInput
            ? Carbon::parse($endDateInput)->endOfDay()
            : now()->endOfMonth();

    $sales = Sale::with(['items', 'branch.business', 'cashier'])
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $summary = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_cogs' => $sales->sum(function ($sale) {
                return $sale->items->sum('total_cost');
            }),
            'total_profit' => 0,
            'average_margin' => 0,
        ];

        $summary['total_profit'] = $summary['total_revenue'] - $summary['total_cogs'];
        if ($summary['total_revenue'] > 0) {
            $summary['average_margin'] = ($summary['total_profit'] / $summary['total_revenue']) * 100;
        }

        $dailyData = $sales->groupBy(fn ($sale) => $sale->created_at->format('Y-m-d'))
            ->sortKeys();

        $chartData = [
            'labels' => $dailyData->keys()->values()->all(),
            'revenue' => $dailyData->map(fn ($daySales) => (float) $daySales->sum('total'))->values()->all(),
            'cogs' => $dailyData->map(fn ($daySales) => (float) $daySales->sum(fn ($sale) => $sale->items->sum('total_cost')))->values()->all(),
            'profit' => $dailyData->map(function ($daySales) {
                $revenue = $daySales->sum('total');
                $cogs = $daySales->sum(fn ($sale) => $sale->items->sum('total_cost'));
                return (float) ($revenue - $cogs);
            })->values()->all(),
            'loss' => $dailyData->map(function ($daySales) {
                $revenue = $daySales->sum('total');
                $cogs = $daySales->sum(fn ($sale) => $sale->items->sum('total_cost'));
                $net = (float) ($revenue - $cogs);
                return $net < 0 ? abs($net) : 0.0;
            })->values()->all(),
            'margin' => $dailyData->map(function ($daySales) {
                $revenue = $daySales->sum('total');
                if ($revenue <= 0) {
                    return 0.0;
                }

                $cogs = $daySales->sum(fn ($sale) => $sale->items->sum('total_cost'));
                $profit = $revenue - $cogs;

                return round(($profit / $revenue) * 100, 2);
            })->values()->all(),
        ];

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sales' => $sales,
            'summary' => $summary,
            'chartData' => $chartData,
        ];
    }

    public function report(Request $request)
    {
        $data = $this->buildReportData($request);

        return view('sales.report', $data);
    }
}