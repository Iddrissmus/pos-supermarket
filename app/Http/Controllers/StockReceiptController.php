<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockReceipt;
use App\Services\ReceiveStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockReceiptController extends Controller
{
    protected $receiveStockService;

    public function __construct(ReceiveStockService $receiveStockService)
    {
        $this->receiveStockService = $receiveStockService;
    }

    /**
     * Display a listing of stock receipts
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $receipts = StockReceipt::with(['branch', 'supplier', 'createdBy'])
            ->when($user->branch_id, function ($query) use ($user) {
                // If user is tied to a specific branch, only show their receipts
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('received_at', 'desc')
            ->paginate(20);

        return view('inventory.receipts.index', compact('receipts'));
    }

    /**
     * Show the form for creating a new stock receipt
     */
    public function create()
    {
        $user = Auth::user();
        $branches = $user->branch_id 
            ? Branch::where('id', $user->branch_id)->get()
            : Branch::all();
        
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::with('branchProducts')->get();

        return view('inventory.receipts.create', compact('branches', 'suppliers', 'products'));
    }

    /**
     * Store a newly created stock receipt
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'receipt_number' => 'nullable|string|max:50',
            'received_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $receipt = $this->receiveStockService->receiveStock($validated);
            
            return redirect()->route('stock-receipts.show', $receipt)
                ->with('success', 'Stock received successfully! Receipt #' . $receipt->receipt_number);
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to receive stock: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified stock receipt
     */
    public function show(StockReceipt $stockReceipt)
    {
        $stockReceipt->load(['items.product', 'supplier', 'branch', 'createdBy']);
        
        return view('inventory.receipts.show', compact('stockReceipt'));
    }

    /**
     * API endpoint to get current cost price for a product at a branch
     */
    public function getCurrentCost(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $currentCost = $this->receiveStockService->getCurrentCostPrice(
            $request->branch_id,
            $request->product_id
        );

        return response()->json([
            'current_cost' => $currentCost,
            'formatted_cost' => number_format($currentCost, 2),
        ]);
    }

    /**
     * API endpoint to get product info including current stock
     */
    public function getProductInfo(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::with(['branchProducts' => function ($query) use ($request) {
            $query->where('branch_id', $request->branch_id);
        }])->find($request->product_id);

        $branchProduct = $product->branchProducts->first();

        return response()->json([
            'product' => [
                'name' => $product->name,
                'sku' => $product->sku,
                'current_stock' => $branchProduct ? $branchProduct->stock_quantity : 0,
                'current_cost' => $branchProduct ? $branchProduct->cost_price : 0,
                'selling_price' => $branchProduct ? $branchProduct->selling_price : 0,
            ]
        ]);
    }
}
