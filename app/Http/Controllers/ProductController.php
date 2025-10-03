<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request()->query('per_page', 25);
        $products = Product::with('business')
            ->orderBy('created_at', 'desc')
            ->paginate((int)$perPage);

        // If request expects JSON (API/AJAX), return JSON
        if (request()->wantsJson() || request()->ajax() || request()->expectsJson()) {
            return response()->json($products);
        }

        // Otherwise return the blade view and pass products for server-side rendering
        return view('layouts.product', compact('products'));
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

            // Resolve business_id for the authenticated owner
            $businessId = Business::where('owner_id', auth()->id())->value('id');
            if (!$businessId) {
                $error_message = 'No business found for the authenticated owner.';
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
            \Log::error('Product creation failed: ' . $e->getMessage());

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
            'owner_id' => 'sometimes|required|exists:users,id',
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
