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
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'manager' && $user->branch_id) {
            // Manager - show only their branch's products
            $branchProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->with('product')
                ->get();
            
            // Calculate summary statistics for manager's branch
            $totalProducts = $branchProducts->count();
            $inStoreProducts = $branchProducts->where('stock_quantity', '>', 0)->count();
            $lowStockProducts = $branchProducts->filter(function($item) {
                return $item->stock_quantity <= $item->reorder_level || $item->stock_quantity <= 10;
            })->count();
            
            // Calculate total value
            $totalValue = $branchProducts->sum(function($item) {
                return $item->stock_quantity * $item->cost_price;
            });

            $products = $branchProducts;
            
        } else {
            // Admin - show all products across all branches
            $totalProducts = Product::count();
            $inStoreProducts = Product::whereHas('branchProducts')->count();
            $lowStockProducts = BranchProduct::where(function($query) {
                $query->whereColumn('stock_quantity', '<=', 'reorder_level')
                      ->orWhere('stock_quantity', '<=', 10);
            })->distinct('product_id')->count('product_id');
            
            // Calculate total value across all branches
            $totalValue = BranchProduct::sum(DB::raw('stock_quantity * cost_price'));
            
            $products = BranchProduct::with(['product', 'branch'])->get();
        }
        
        // Get recent stock activities
        $recentActivities = $this->getRecentActivities($user);
        
        $stats = [
            'total_products' => $totalProducts,
            'in_store' => $inStoreProducts,
            'low_stock' => $lowStockProducts,
            'total_value' => $totalValue,
            'recent_activities' => $recentActivities,
        ];

        return view('layouts.productman', compact('products', 'stats'));
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