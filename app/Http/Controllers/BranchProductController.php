<?php

namespace App\Http\Controllers;
use App\Models\BranchProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchProductController extends Controller
{
    public function index()
    {
        $perPage = request()->query('per_page', 25);
        $branchProducts = BranchProduct::with(['branch', 'product'])->paginate((int)$perPage);
        return response()->json($branchProducts);
    }

    /**
     * Assign product to a branch with price, stock, and reorder level.
     */
    public function assign(Request $request, Product $product)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'reorder_level' => 'required|integer'
        ]);

        $product->branches()->attach($validated['branch_id'], [
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'reorder_level' => $validated['reorder_level'],
        ]);

        return response()->json(['message' => 'Product assigned to branch']);
    }
}