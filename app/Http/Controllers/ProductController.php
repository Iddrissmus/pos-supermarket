<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Business;
use App\Models\BranchProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $categoryId = request()->input('category_id');

        // Determine which branches the user has access to
        $branchIds = collect();
        if (($user->role === 'manager' || $user->role === 'business_admin') && $user->branch_id) {
            $branchIds = collect([$user->branch_id]);
        } else {
            // Superadmin or business admin without specific branch - get all branches
            $branchIds = \App\Models\Branch::where('business_id', $user->business_id)->pluck('id');
        }

        // Get categories that have products available in the accessible branches
        $categories = \App\Models\Category::forBusiness($user->business_id)
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

        if (($user->role === 'manager' || $user->role === 'business_admin') && $user->branch_id) {
            $productsQuery = BranchProduct::where('branch_id', $user->branch_id)
                ->with(['product.category']);
            if ($categoryId) {
                $productsQuery->whereHas('product', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
            $products = $productsQuery->paginate(15);

            $totalProducts = BranchProduct::where('branch_id', $user->branch_id)->count();
            $inStoreProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->where('stock_quantity', '>', 0)->count();
            $lowStockProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->where(function($query) {
                    $query->whereColumn('stock_quantity', '<=', 'reorder_level')
                          ->orWhere('stock_quantity', '<=', 10);
                })->count();
        } else {
            $productsQuery = BranchProduct::with(['product.category', 'branch']);
            if ($categoryId) {
                $productsQuery->whereHas('product', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
            $products = $productsQuery->paginate(15);

            $totalProducts = Product::count();
            $inStoreProducts = Product::whereHas('branchProducts')->count();
            $lowStockProducts = BranchProduct::where(function($query) {
                $query->whereColumn('stock_quantity', '<=', 'reorder_level')
                      ->orWhere('stock_quantity', '<=', 10);
            })->distinct('product_id')->count('product_id');
        }

        $stats = [
            'total_products' => $totalProducts,
            'in_store_products' => $inStoreProducts,
            'low_stock_products' => $lowStockProducts,
        ];

        if (request()->wantsJson() || request()->ajax() || request()->expectsJson()) {
            return response()->json($products);
        }

        return view('layouts.product', [
            'products' => $products,
            'stats' => $stats,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sku' => 'nullable|string|unique:products,sku',
            'image' => 'nullable|image|max:2048',

            // optional branch/stock fields
            'branch_id' => 'nullable|exists:branches,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            // if caller expects JSON (AJAX), return JSON errors
            if ($request->wantsJson()|| $request->ajax() || $request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            //Otherwise redirect back with errors and old input
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validatedData = $validator->validated();

            // Get business_id from the selected branch or default to the first available business
            $businessId = null;
            
            if (!empty($validatedData['branch_id'])) {
                // Get business_id from the selected branch
                $businessId = \App\Models\Branch::where('id', $validatedData['branch_id'])->value('business_id');
            } else {
                // If no branch selected, get the first available business (for admin/general products)
                $businessId = \App\Models\Business::first()?->id;
            }
            
            if (!$businessId) {
                $error_message = 'No business found. Please ensure branches and businesses are properly set up.';
                if ($request->wantsJson()|| $request->ajax() || $request->expectsJson()) {
                    return response()->json(['error' => $error_message], 422);
                }
                return redirect()->back()->with('error',$error_message)->withInput();
            }
            
            $validatedData['business_id'] = $businessId;

            // Handle product image upload (stored in 'image' field)
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product-images', 'public');
                $validatedData['image'] = $imagePath;
            }

            $product = Product::create($validatedData);

            // If branch info provided, create or update branch_products row
            $branchId = $request->input('branch_id');
            $stockQty = $request->input('stock_quantity');
            $bpData = [];
            if (!is_null($stockQty)) $bpData['stock_quantity'] = (int) $stockQty;
            if ($request->filled('price')) $bpData['price'] = $request->input('price');
            if ($request->filled('cost_price')) $bpData['cost_price'] = $request->input('cost_price');
            if ($request->filled('reorder_level')) $bpData['reorder_level'] = $request->input('reorder_level');

            if ($branchId && count($bpData)) {
                // create or update existing BranchProduct record
                $branchProduct = \App\Models\BranchProduct::firstOrNew([
                    'branch_id' => $branchId,
                    'product_id' => $product->id,
                ]);
                foreach ($bpData as $k => $v) $branchProduct->$k = $v;
                $branchProduct->save();
            }
            
            // JSON for AJAX, redirect for regular form submit
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Product created successfully',
                    'product' => $product
                ], 201);
            }

            return redirect()->route('layouts.product')->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['error' => 'Failed to create product'], 500);
            }
            return redirect()->back()->with('error', 'Failed to create product')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('business')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'business_admin_id' => 'sometimes|required|exists:users,id',
            'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($validator->validated());
        return response()->json($product)->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Business deleted successfully'], 204);
    }


    
}
