<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\BranchProduct;


class ProductDashboardController extends Controller
{
    public function index()
    {
        $total_products = Product::count();

        // Total across all branches
        $in_store = (int) BranchProduct::sum('stock_quantity');

        // Low stock: use reorder_level when available, otherwise fallback to threshold 5
        $low_stock = BranchProduct::whereColumn('stock_quantity', '<=', 'reorder_level')->count();
        // If none are defined with reorder_level, fallback to simple threshold
        if ($low_stock === 0) {
            $low_stock = BranchProduct::where('stock_quantity', '<=', 5)->count();
        }

        $total_value = (float) BranchProduct::selectRaw('COALESCE(SUM(price * stock_quantity),0) as total')->value('total');

        $stats = [
            'total_products' => $total_products,
            'in_store' => $in_store,
            'low_stock' => $low_stock,
            'total_value' => $total_value,
        ];

        return view('layouts.productman', compact('stats'));
    }
    
}
