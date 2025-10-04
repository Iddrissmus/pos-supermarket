<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Services\ReceiveStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $sales = Sale::with(['branch', 'cashier', 'items.product'])
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        $user = Auth::user();
        $branches = $user->branch_id 
            ? Branch::where('id', $user->branch_id)->get()
            : Branch::all();
        
        // Get products with current stock for the user's branch(es)
        $products = collect();
        foreach ($branches as $branch) {
            $branchProducts = BranchProduct::where('branch_id', $branch->id)
                ->where('stock_quantity', '>', 0)
                ->with('product')
                ->get();
            
            foreach ($branchProducts as $bp) {
                $products->push([
                    'id' => $bp->product->id,
                    'name' => $bp->product->name,
                    'sku' => $bp->product->sku,
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'stock_quantity' => $bp->stock_quantity,
                    'selling_price' => $bp->selling_price,
                    'cost_price' => $bp->cost_price,
                ]);
            }
        }

        return view('sales.create', compact('branches', 'products'));
    }

    /**
     * Store a newly created sale with COGS tracking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'payment_method' => 'required|string|in:cash,card,mobile_money',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Create the sale
                $sale = Sale::create([
                    'branch_id' => $validated['branch_id'],
                    'cashier_id' => Auth::id(),
                    'payment_method' => $validated['payment_method'],
                    'total' => 0, // Will be calculated
                ]);

                $totalAmount = 0;

                // Process each sale item with COGS calculation
                foreach ($validated['items'] as $itemData) {
                    // Get COGS and reduce stock
                    $cogsResult = $this->receiveStockService->processSale(
                        $validated['branch_id'],
                        $itemData['product_id'],
                        $itemData['quantity']
                    );

                    // Create sale item with COGS data
                    $itemTotal = $itemData['quantity'] * $itemData['price'];
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'total' => $itemTotal,
                        'unit_cost' => $cogsResult['unit_cost'],
                        'total_cost' => $cogsResult['total_cost'],
                        // Margins will be calculated automatically by the model
                    ]);

                    $totalAmount += $itemTotal;
                }

                // Update sale total
                $sale->update(['total' => $totalAmount]);

                return redirect()->route('sales.show', $sale)
                    ->with('success', 'Sale completed successfully!');
            });
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to complete sale: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'branch', 'cashier']);
        
        // Calculate totals for display
        $totals = [
            'revenue' => $sale->total,
            'cogs' => $sale->items->sum('total_cost'),
            'gross_profit' => $sale->total - $sale->items->sum('total_cost'),
        ];
        
        if ($totals['revenue'] > 0) {
            $totals['margin_percent'] = ($totals['gross_profit'] / $totals['revenue']) * 100;
        } else {
            $totals['margin_percent'] = 0;
        }

        return view('sales.show', compact('sale', 'totals'));
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
            'selling_price' => $branchProduct->selling_price,
            'cost_price' => $branchProduct->cost_price,
            'product_name' => $branchProduct->product->name,
            'sku' => $branchProduct->product->sku,
        ]);
    }

    /**
     * Sales report with margin analysis
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $sales = Sale::with(['items', 'branch'])
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
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

        return view('sales.report', compact('sales', 'summary', 'startDate', 'endDate'));
    }
}