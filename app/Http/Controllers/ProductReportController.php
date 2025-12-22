<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\BranchProduct;
use App\Models\StockLog;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductReportController extends Controller
{
    /**
     * Product Analytics Dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $dateRange = $this->getDateRange($request);
        
        // Get top performing products
        $topProducts = $this->getTopProducts($user, $dateRange, 10);
        
        // Get product category breakdown
        $categoryBreakdown = $this->getCategoryBreakdown($user, $dateRange);
        
        // Get stock alerts
        $lowStockProducts = $this->getLowStockProducts($user);
        $overstockProducts = $this->getOverstockProducts($user);
        
        // Get quick stats
        $totalProducts = $this->getTotalProductsCount($user);
        $activeProducts = $this->getActiveProductsCount($user, $dateRange);
        $averageMargin = $this->getAverageMargin($user, $dateRange);
        
        return view('product-reports.index', compact(
            'topProducts',
            'categoryBreakdown',
            'lowStockProducts',
            'overstockProducts',
            'totalProducts',
            'activeProducts',
            'averageMargin',
            'dateRange'
        ));
    }

    /**
     * Product Performance Report
     */
    public function performance(Request $request)
    {
        $user = Auth::user();
        $dateRange = $this->getDateRange($request);
        $categoryId = $request->get('category_id');
        $branchId = $request->get('branch_id');
        
        // Get product performance data
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Apply business filter
        if ($user->role === 'business_admin') {
            $query->where('branches.business_id', $user->business_id);
        } elseif ($user->role === 'manager') {
            $query->where('sales.branch_id', $user->branch_id);
        }
        
        // Apply category filter
        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        
        // Apply branch filter (for business admin)
        if ($branchId && $user->role === 'business_admin') {
            $query->where('sales.branch_id', $branchId);
        }
        
        $products = $query->select(
            'products.id',
            'products.name',
            'products.barcode',
            'products.category_id',
            DB::raw('SUM(sale_items.quantity) as total_quantity_sold'),
            DB::raw('SUM(sale_items.total) as total_revenue'),
            DB::raw('SUM(sale_items.total_cost) as total_cost'),
            DB::raw('SUM(sale_items.total - sale_items.total_cost) as total_profit'),
            DB::raw('COUNT(DISTINCT sales.id) as number_of_transactions'),
            DB::raw('AVG(sale_items.price) as average_selling_price')
        )
        ->groupBy('products.id', 'products.name', 'products.barcode', 'products.category_id')
        ->orderBy('total_revenue', 'desc')
        ->paginate(20);
        
        // Get categories and branches for filters
        $categories = Category::orderBy('name')->get();
        $branches = $user->role === 'business_admin' 
            ? Branch::where('business_id', $user->business_id)->orderBy('name')->get()
            : collect();
        
        return view('product-reports.performance', compact(
            'products',
            'categories',
            'branches',
            'dateRange'
        ));
    }

    /**
     * Product Movement Report
     */
    public function movement(Request $request)
    {
        $user = Auth::user();
        $dateRange = $this->getDateRange($request);
        $productId = $request->get('product_id');
        $branchId = $request->get('branch_id');
        
        $query = StockLog::with(['product', 'branch'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Apply business filter
        if ($user->role === 'business_admin') {
            $query->whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } elseif ($user->role === 'manager') {
            $query->where('branch_id', $user->branch_id);
        }
        
        // Apply product filter
        if ($productId) {
            $query->where('product_id', $productId);
        }
        
        // Apply branch filter
        if ($branchId && $user->role === 'business_admin') {
            $query->where('branch_id', $branchId);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get movement summary
        $summary = StockLog::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->when($user->role === 'business_admin', function($q) use ($user) {
                $q->whereHas('branch', function($q2) use ($user) {
                    $q2->where('business_id', $user->business_id);
                });
            })
            ->when($user->role === 'manager', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->select(
                'action as type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('action')
            ->get();
        
        // Get products and branches for filters
        $products = Product::orderBy('name')->get();
        $branches = $user->role === 'business_admin' 
            ? Branch::where('business_id', $user->business_id)->orderBy('name')->get()
            : collect();
        
        return view('product-reports.movement', compact(
            'movements',
            'summary',
            'products',
            'branches',
            'dateRange'
        ));
    }

    /**
     * Product Profitability Analysis
     */
    public function profitability(Request $request)
    {
        $user = Auth::user();
        $dateRange = $this->getDateRange($request);
        
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Apply business filter
        if ($user->role === 'business_admin') {
            $query->where('branches.business_id', $user->business_id);
        } elseif ($user->role === 'manager') {
            $query->where('sales.branch_id', $user->branch_id);
        }
        
        $products = $query->select(
            'products.id',
            'products.name',
            'products.barcode',
            DB::raw('SUM(sale_items.quantity) as total_quantity_sold'),
            DB::raw('SUM(sale_items.total) as total_revenue'),
            DB::raw('SUM(sale_items.total_cost) as total_cost'),
            DB::raw('SUM(sale_items.total - sale_items.total_cost) as total_profit'),
            DB::raw('CASE WHEN SUM(sale_items.total) > 0 THEN ((SUM(sale_items.total - sale_items.total_cost) / SUM(sale_items.total)) * 100) ELSE 0 END as profit_margin'),
            DB::raw('CASE WHEN SUM(sale_items.total_cost) > 0 THEN ((SUM(sale_items.total - sale_items.total_cost) / SUM(sale_items.total_cost)) * 100) ELSE 0 END as roi_percentage')
        )
        ->groupBy('products.id', 'products.name', 'products.barcode')
        ->orderBy('profit_margin', 'desc')
        ->paginate(50);
        
        // Calculate overall profitability metrics
        $overallMetrics = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']])
            ->when($user->role === 'business_admin', function ($q) use ($user) {
                $q->join('branches', 'sales.branch_id', '=', 'branches.id')
                  ->where('branches.business_id', $user->business_id);
            })
            ->when($user->role === 'manager', function($q) use ($user) {
                $q->where('sales.branch_id', $user->branch_id);
            })
            ->select(
                DB::raw('SUM(sale_items.total) as total_revenue'),
                DB::raw('SUM(sale_items.total_cost) as total_cost'),
                DB::raw('SUM(sale_items.total - sale_items.total_cost) as total_profit')
            )
            ->first();
        
        return view('product-reports.profitability', compact(
            'products',
            'overallMetrics',
            'dateRange'
        ));
    }

    /**
     * Product Sales Trends
     */
    public function trends(Request $request)
    {
        $user = Auth::user();
        $dateRange = $this->getDateRange($request);
        $productId = $request->get('product_id');
        $groupBy = $request->get('group_by', 'day'); // day, week, month
        
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        
        // Apply business filter
        if ($user->role === 'business_admin') {
            $query->where('branches.business_id', $user->business_id);
        } elseif ($user->role === 'manager') {
            $query->where('sales.branch_id', $user->branch_id);
        }
        
        // Apply product filter
        if ($productId) {
            $query->where('sale_items.product_id', $productId);
        }
        
        // Group by time period
        $dateFormat = $groupBy === 'day' ? '%Y-%m-%d' : ($groupBy === 'week' ? '%Y-%u' : '%Y-%m');
        
        $trends = $query->select(
            DB::raw("DATE_FORMAT(sales.created_at, '{$dateFormat}') as period"),
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('SUM(sale_items.total) as total_revenue'),
            DB::raw('SUM(sale_items.total - sale_items.total_cost) as total_profit'),
            DB::raw('COUNT(DISTINCT sales.id) as transaction_count')
        )
        ->groupBy('period')
        ->orderBy('period')
        ->get();
        
        // Get top products for trend comparison
        $topProducts = $this->getTopProducts($user, $dateRange, 10);
        
        // Get products for filter
        $products = Product::orderBy('name')->get();
        
        return view('product-reports.trends', compact(
            'trends',
            'topProducts',
            'products',
            'dateRange',
            'groupBy'
        ));
    }

    /**
     * Product Inventory Status
     */
    public function inventory(Request $request)
    {
        $user = Auth::user();
        $categoryId = $request->get('category_id');
        $branchId = $request->get('branch_id');
        $status = $request->get('status'); // low, overstock, normal
        
        $query = BranchProduct::with(['product', 'branch'])
            ->join('products', 'branch_products.product_id', '=', 'products.id');
        
        // Apply business filter
        if ($user->role === 'business_admin') {
            $query->whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } elseif ($user->role === 'manager') {
            $query->where('branch_products.branch_id', $user->branch_id);
        }
        
        // Apply category filter
        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        
        // Apply branch filter
        if ($branchId && $user->role === 'business_admin') {
            $query->where('branch_products.branch_id', $branchId);
        }
        
        // Apply status filter
        if ($status === 'low') {
            $query->whereRaw('branch_products.stock_quantity <= branch_products.reorder_level');
        } elseif ($status === 'overstock') {
            $query->whereRaw('branch_products.stock_quantity > (branch_products.reorder_level * 3)');
        } elseif ($status === 'normal') {
            $query->whereRaw('branch_products.stock_quantity > branch_products.reorder_level')
                  ->whereRaw('branch_products.stock_quantity <= (branch_products.reorder_level * 3)');
        }
        
        $inventory = $query->select(
            'branch_products.*',
            'products.name as product_name',
            'products.barcode',
            'products.category_id',
            DB::raw('COALESCE(branch_products.cost_price, products.cost_price, 0) as effective_cost_price'),
            DB::raw('(branch_products.stock_quantity * COALESCE(branch_products.cost_price, products.cost_price, 0)) as stock_value'),
            DB::raw('CASE 
                WHEN branch_products.reorder_level > 0 
                     THEN (branch_products.stock_quantity / branch_products.reorder_level) * 100
                ELSE 0
            END as stock_percentage'),
            DB::raw('CASE 
                WHEN branch_products.stock_quantity <= branch_products.reorder_level THEN "low"
                WHEN branch_products.stock_quantity > (branch_products.reorder_level * 3) THEN "overstock"
                ELSE "normal"
            END as stock_status')
        )
        ->orderBy('stock_status')
        ->orderBy('products.name')
        ->paginate(20);
        
        // Get summary stats
        $summaryQuery = BranchProduct::join('products', 'branch_products.product_id', '=', 'products.id');
        
        if ($user->role === 'business_admin') {
            $summaryQuery->whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } elseif ($user->role === 'manager') {
            $summaryQuery->where('branch_products.branch_id', $user->branch_id);
        }
        
        $summary = [
            'total_products' => $summaryQuery->count(),
            'low_stock' => (clone $summaryQuery)->whereRaw('branch_products.stock_quantity <= branch_products.reorder_level')->count(),
            'overstock' => (clone $summaryQuery)->whereRaw('branch_products.stock_quantity > (branch_products.reorder_level * 3)')->count(),
            'total_stock_value' => (clone $summaryQuery)->sum(DB::raw('branch_products.stock_quantity * branch_products.cost_price'))
        ];
        
        // Get categories and branches for filters
        $categories = Category::orderBy('name')->get();
        $branches = $user->role === 'business_admin' 
            ? Branch::where('business_id', $user->business_id)->orderBy('name')->get()
            : collect();
        
        return view('product-reports.inventory', compact(
            'inventory',
            'summary',
            'categories',
            'branches'
        ));
    }

    /**
     * Helper: Get date range
     */
    protected function getDateRange(Request $request): array
    {
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();
        
        return [
            'start' => $startDate,
            'end' => $endDate,
            'start_formatted' => $startDate->format('Y-m-d'),
            'end_formatted' => $endDate->format('Y-m-d')
        ];
    }

    /**
     * Helper: Get top performing products
     */
    protected function getTopProducts($user, $dateRange, $limit = 10)
    {
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        
        if ($user->role === 'business_admin') {
            $query->where('branches.business_id', $user->business_id);
        } elseif ($user->role === 'manager') {
            $query->where('sales.branch_id', $user->branch_id);
        }
        
        return $query->select(
            'products.id',
            'products.name',
            'products.barcode',
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('SUM(sale_items.total) as total_revenue'),
            DB::raw('SUM(sale_items.total - sale_items.total_cost) as total_profit')
        )
        ->groupBy('products.id', 'products.name', 'products.barcode')
        ->orderBy('total_revenue', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Helper: Get category breakdown
     */
    protected function getCategoryBreakdown($user, $dateRange)
    {
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        
        if ($user->role === 'business_admin') {
            $query->where('branches.business_id', $user->business_id);
        } elseif ($user->role === 'manager') {
            $query->where('sales.branch_id', $user->branch_id);
        }
        
        return $query->select(
            'categories.name as category_name',
            DB::raw('SUM(sale_items.total) as total_revenue'),
            DB::raw('SUM(sale_items.quantity) as total_quantity'),
            DB::raw('COUNT(DISTINCT products.id) as product_count')
        )
        ->groupBy('categories.id', 'categories.name')
        ->orderBy('total_revenue', 'desc')
        ->get();
    }

    /**
     * Helper: Get low stock products
     */
    protected function getLowStockProducts($user)
    {
        $query = BranchProduct::with(['product', 'branch'])
            ->whereRaw('stock_quantity <= reorder_level');
        
        if ($user->role === 'business_admin') {
            $query->whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } elseif ($user->role === 'manager') {
            $query->where('branch_id', $user->branch_id);
        }
        
        return $query->orderBy('stock_quantity')->limit(10)->get();
    }

    /**
     * Helper: Get overstock products
     */
    protected function getOverstockProducts($user)
    {
        $query = BranchProduct::with(['product', 'branch'])
            ->whereRaw('stock_quantity > (reorder_level * 3)');
        
        if ($user->role === 'business_admin') {
            $query->whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } elseif ($user->role === 'manager') {
            $query->where('branch_id', $user->branch_id);
        }
        
        return $query->orderBy('stock_quantity', 'desc')->limit(10)->get();
    }

    /**
     * Helper: Get total products count
     */
    protected function getTotalProductsCount($user)
    {
        if ($user->role === 'business_admin') {
            return BranchProduct::whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            })->distinct('product_id')->count('product_id');
        } elseif ($user->role === 'manager') {
            return BranchProduct::where('branch_id', $user->branch_id)->count();
        }
        
        return 0;
    }

    /**
     * Helper: Get active products count (sold in period)
     */
    protected function getActiveProductsCount($user, $dateRange)
    {
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        
        if ($user->role === 'business_admin') {
            $query->where('branches.business_id', $user->business_id);
        } elseif ($user->role === 'manager') {
            $query->where('sales.branch_id', $user->branch_id);
        }
        
        return $query->distinct('product_id')->count('product_id');
    }

    /**
     * Helper: Get average profit margin
     */
    protected function getAverageMargin($user, $dateRange)
    {
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']]);
        
        if ($user->role === 'business_admin') {
            $query->where('branches.business_id', $user->business_id);
        } elseif ($user->role === 'manager') {
            $query->where('sales.branch_id', $user->branch_id);
        }
        
        $result = $query->select(
            DB::raw('SUM(sale_items.total) as total_revenue'),
            DB::raw('SUM(sale_items.total_cost) as total_cost')
        )->first();
        
        if ($result && $result->total_revenue > 0) {
            return (($result->total_revenue - $result->total_cost) / $result->total_revenue) * 100;
        }
        
        return 0;
    }
}
