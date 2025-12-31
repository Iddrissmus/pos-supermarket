<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Branch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\BranchProduct;
use App\Models\Product; // Assuming product model exists

class BusinessAdminDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->business_id) {
            return view('dashboard.business-admin', ['error' => 'No business assigned']);
        }

        $businessId = $user->business_id;
        $branchIds = Branch::where('business_id', $businessId)->pluck('id');

        // 1. Business-Wide Stats
        $totalRevenue = Sale::whereIn('branch_id', $branchIds)->sum('total');
        $totalOrders = Sale::whereIn('branch_id', $branchIds)->count();
        $totalProducts = BranchProduct::whereIn('branch_id', $branchIds)->count(); // Total items across all branches
        
        // 2. Stock Health
        $lowStockCount = BranchProduct::whereIn('branch_id', $branchIds)
            ->whereRaw('stock_quantity <= reorder_level')
            ->count();
            
        $outOfStockCount = BranchProduct::whereIn('branch_id', $branchIds)
            ->where('stock_quantity', '<=', 0)
            ->count();

        // 3. Top Selling Products (Business Wide)
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sales.branch_id', $branchIds)
            ->groupBy('products.id', 'products.name', 'products.barcode')
            ->select(
                'products.name as product_name',
                'products.barcode',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.total) as total_revenue')
            )
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // 4. Recent Sales (Business Wide)
        $recentSales = Sale::with(['branch', 'cashier'])
            ->whereIn('branch_id', $branchIds)
            ->latest()
            ->take(5)
            ->get();

        // 5. Branch Performance (Sales by Branch)
        $branchPerformance = Sale::whereIn('branch_id', $branchIds)
            ->select('branch_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('branch_id')
            ->with('branch')
            ->orderByDesc('revenue')
            ->get();

        return view('dashboard.business-admin', compact(
            'totalRevenue',
            'totalOrders',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'topProducts',
            'recentSales',
            'branchPerformance',
            'user'
        ));
    }
}
