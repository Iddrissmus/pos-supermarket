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
        $products = Product::with('owner')->paginate((int)$perPage);
        return response()->json($products);
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
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();

            // Resolve business_id for the authenticated owner
            $businessId = Business::where('owner_id', auth()->id())->value('id');
            if (!$businessId) {
                return response()->json(['error' => 'No business found for this owner'], 422);
            }
            $validatedData['business_id'] = $businessId;

            // Handle product image upload (stored in 'image' field)
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product-images', 'public');
                $validatedData['image'] = $imagePath;
            }

            $product = Product::create($validatedData);
            
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Product creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create product'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('owner')->findorFail($id);
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



