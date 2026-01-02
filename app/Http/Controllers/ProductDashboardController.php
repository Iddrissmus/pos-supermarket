<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\BranchProduct;
use App\Models\StockLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = collect();
        $selectedBranchId = $request->query('branch_id');

        if ($user->role === 'superadmin') {
            // Fetch all branches for superadmin filter
            $branches = \App\Models\Branch::orderBy('name')->get();
            
            $query = BranchProduct::with(['product', 'branch']);
            
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            }
            
            $allBranchProducts = $query->get();
            
            // Stats based on current filter
            $totalProducts = $allBranchProducts->count(); 
            $inStoreProducts = $allBranchProducts->where('stock_quantity', '>', 0)->count();
            $lowStockProducts = $allBranchProducts->filter(function($item) {
                return $item->stock_quantity <= ($item->reorder_level ?? 10) || $item->stock_quantity <= 10;
            })->count();
            
            $totalSalesValue = $allBranchProducts->sum(function($item) {
                return $item->stock_quantity * ($item->price ?? 0);
            });
            
            $totalCost = $allBranchProducts->sum(DB::raw('stock_quantity * cost_price'));
            
            $potentialProfit = $totalSalesValue - $totalCost;
            $avgMargin = $totalSalesValue > 0 ? ($potentialProfit / $totalSalesValue) * 100 : 0;
            $outOfStock = $allBranchProducts->where('stock_quantity', 0)->count();
            $products = $allBranchProducts;
            
        } elseif ($user->role === 'business_admin' || $user->role === 'manager') {
            // Fetch branches for this business
            $branches = \App\Models\Branch::where('business_id', $user->business_id)->orderBy('name')->get();
            
            $branchId = $selectedBranchId ?: $user->branch_id;
            
            $query = BranchProduct::with(['product', 'branch'])
                ->whereHas('branch', function($q) use ($user) {
                    $q->where('business_id', $user->business_id);
                });

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            
            $branchProducts = $query->get();
            
            // Calculate summary statistics
            $totalProducts = $branchProducts->count();
            $inStoreProducts = $branchProducts->where('stock_quantity', '>', 0)->count();
            $lowStockProducts = $branchProducts->filter(function($item) {
                return $item->stock_quantity <= ($item->reorder_level ?? 10) || $item->stock_quantity <= 10;
            })->count();
            
            // Calculate total value (Sales Value)
            $totalSalesValue = $branchProducts->sum(function($item) {
                return $item->stock_quantity * ($item->price ?? 0);
            });

            // Calculate total cost
            $totalCost = $branchProducts->sum(function($item) {
                return $item->stock_quantity * ($item->cost_price ?? 0);
            });

            $potentialProfit = $totalSalesValue - $totalCost;
            $avgMargin = $totalSalesValue > 0 ? ($potentialProfit / $totalSalesValue) * 100 : 0;
            $outOfStock = $branchProducts->where('stock_quantity', 0)->count();

            $products = $branchProducts;
            
        } else {
            // No branch assigned - show empty
            $totalProducts = 0;
            $inStoreProducts = 0;
            $lowStockProducts = 0;
            $totalSalesValue = 0;
            $totalCost = 0;
            $potentialProfit = 0;
            $avgMargin = 0;
            $outOfStock = 0;
            $products = collect();
        }
        
        // Get recent stock activities
        $recentActivities = $this->getRecentActivities($user);
        
        $stats = [
            'total_products' => $totalProducts,
            'in_store' => $inStoreProducts,
            'low_stock' => $lowStockProducts,
            'out_of_stock' => $outOfStock,
            'total_sales_value' => $totalSalesValue,
            'total_cost' => $totalCost,
            'potential_profit' => $potentialProfit,
            'avg_margin' => $avgMargin,
            'recent_activities' => $recentActivities,
        ];

        return view('layouts.productman', compact('products', 'stats', 'branches', 'selectedBranchId'));
    }
    
    private function getRecentActivities($user)
    {
        $query = StockLog::with(['product', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(5);
        
        // Filter by branch for managers
        if ($user->role === 'manager' && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        
        $stockLogs = $query->get();
        
        $activities = [];
        foreach ($stockLogs as $log) {
            $product = $log->product;
            $branch = $log->branch;
            
            // Determine icon and color based on action
            $iconData = $this->getActivityIconData($log->action);
            
            // Build message
            $message = $this->buildActivityMessage($log, $product, $branch);
            
            $activities[] = [
                'message' => $message,
                'time' => $log->created_at->diffForHumans(),
                'icon' => $iconData['icon'],
                'icon_color' => $iconData['icon_color'],
                'bg_color' => $iconData['bg_color'],
            ];
        }
        
        return $activities;
    }
    
    private function getActivityIconData($action)
    {
        $iconMap = [
            'stock_addition' => [
                'icon' => 'fa-plus',
                'icon_color' => 'text-blue-600',
                'bg_color' => 'bg-blue-100',
            ],
            'stock_adjustment' => [
                'icon' => 'fa-edit',
                'icon_color' => 'text-green-600',
                'bg_color' => 'bg-green-100',
            ],
            'sale' => [
                'icon' => 'fa-shopping-cart',
                'icon_color' => 'text-purple-600',
                'bg_color' => 'bg-purple-100',
            ],
            'transfer_in' => [
                'icon' => 'fa-arrow-down',
                'icon_color' => 'text-green-600',
                'bg_color' => 'bg-green-100',
            ],
            'transfer_out' => [
                'icon' => 'fa-arrow-up',
                'icon_color' => 'text-orange-600',
                'bg_color' => 'bg-orange-100',
            ],
            'reorder' => [
                'icon' => 'fa-redo',
                'icon_color' => 'text-indigo-600',
                'bg_color' => 'bg-indigo-100',
            ],
        ];
        
        return $iconMap[$action] ?? [
            'icon' => 'fa-box',
            'icon_color' => 'text-gray-600',
            'bg_color' => 'bg-gray-100',
        ];
    }
    
    private function buildActivityMessage($log, $product, $branch)
    {
        $actionMessages = [
            'stock_addition' => 'Stock added',
            'stock_adjustment' => 'Stock adjusted',
            'sale' => 'Sale recorded',
            'transfer_in' => 'Stock received',
            'transfer_out' => 'Stock transferred',
            'reorder' => 'Reorder triggered',
        ];
        
        $actionText = $actionMessages[$log->action] ?? 'Stock activity';
        $productName = $product->name ?? 'Unknown Product';
        $branchName = $branch->name ?? 'Unknown Branch';
        
        $quantity = abs($log->quantity_change);
        
        return "{$actionText}: {$productName} ({$quantity} units) - {$branchName}";
    }
}