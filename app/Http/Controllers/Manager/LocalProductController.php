<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\BranchProduct;
use App\Models\Supplier;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\Category;
use App\Services\ReceiveStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LocalProductController extends Controller
{
    protected $receiveStockService;

    public function __construct(ReceiveStockService $receiveStockService)
    {
        $this->receiveStockService = $receiveStockService;
    }

    /**
     * Show the form for creating a new product from local supplier
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Get only local suppliers (non-central)
        $suppliers = Supplier::where('is_active', true)
            ->where('is_central', false)
            ->orderBy('name')
            ->get();
        
        // Get categories for the user's business
        $categories = Category::where('business_id', $user->business_id)
            ->active()
            ->parents()
            ->with('subcategories')
            ->orderBy('display_order')
            ->get();
        
        // Get selected supplier from query parameter if provided
        $selectedSupplierId = $request->query('supplier_id');
        
        return view('managers.create-local-product', compact('suppliers', 'categories', 'selectedSupplierId'));
    }

    /**
     * Store a new product and create stock receipt
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|unique:products,sku',
            'image' => 'nullable|image|max:2048',
            'cost_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'reorder_level' => 'nullable|integer|min:0',
            'receipt_number' => 'nullable|string|max:50',
            'received_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if supplier is central (managers can't add products from central suppliers)
        $supplier = Supplier::find($request->supplier_id);
        if ($supplier->is_central) {
            return redirect()->back()
                ->with('error', 'You cannot add products from central suppliers. Only local suppliers are allowed.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // 1. Create the product
            $productData = [
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'sku' => $request->sku,
                'business_id' => $user->business_id,
                'primary_supplier_id' => $request->supplier_id,
                'is_local_supplier_product' => true,
                'added_by' => $user->id,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product-images', 'public');
                $productData['image'] = $imagePath;
            }

            $product = Product::create($productData);

            // 2. Create branch product entry with initial stock
            $branchProduct = BranchProduct::create([
                'branch_id' => $user->branch_id,
                'product_id' => $product->id,
                'stock_quantity' => $request->stock_quantity,
                'cost_price' => $request->cost_price,
                'price' => $request->price,
                'reorder_level' => $request->reorder_level ?? 10,
            ]);

            // 3. Create stock receipt
            $receiptNumber = $request->receipt_number ?: 'SR-' . strtoupper(uniqid());
            $stockReceipt = StockReceipt::create([
                'branch_id' => $user->branch_id,
                'supplier_id' => $request->supplier_id,
                'receipt_number' => $receiptNumber,
                'received_date' => $request->received_date,
                'received_at' => $request->received_date, // Add received_at field
                'notes' => $request->notes,
                'created_by' => $user->id,
                'status' => 'received',
            ]);

            // 4. Create stock receipt item
            $totalCost = $request->cost_price * $request->stock_quantity;
            StockReceiptItem::create([
                'stock_receipt_id' => $stockReceipt->id,
                'product_id' => $product->id,
                'quantity' => $request->stock_quantity,
                'unit_cost' => $request->cost_price,
                'total_cost' => $totalCost,
            ]);

            DB::commit();

            return redirect()->route('suppliers.index')
                ->with('success', "Product '{$product->name}' created successfully with {$request->stock_quantity} units in stock! Receipt #{$receiptNumber}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create product: ' . $e->getMessage())
                ->withInput();
        }
    }
}
