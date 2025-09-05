<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('owner')->get();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'required|string|max:255',
            // 'owner_id' => 'required|exists:users,id',
            // 'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors '=> $validator->errors()], 422);
        }

        // Handle product image upload 
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validator->validated()['logo'] = $logoPath; // Add logo path to validated data
        }

        $product = Product::create($validator->validated());
        return response()->json($product, 201);
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
            // 'name' => 'sometimes|required|string|max:255',
            // 'owner_id' => 'sometimes|required|exists:users,id',
            // 'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($validator->validated());
        return response()->json($product);
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



