<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class CashierDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // 1. Current Session Status
        $currentSession = \App\Models\CashDrawerSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        // 2. Today's Statistics
        $todayQuery = $user->sales()->whereDate('created_at', today());
        $todayStats = [
            'count' => $todayQuery->count(),
            'revenue' => $todayQuery->sum('total'),
        ];
        $todayStats['avg_ticket'] = $todayStats['count'] > 0 
            ? $todayStats['revenue'] / $todayStats['count'] 
            : 0;

        // 3. Monthly Statistics
        $monthQuery = $user->sales()->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        $monthStats = [
            'count' => $monthQuery->count(),
            'revenue' => $monthQuery->sum('total'),
        ];

        // 4. Sales Chart Data (Last 7 Days)
        $dates = collect(range(6, 0))->map(fn($days) => now()->subDays($days)->format('Y-m-d'));
        $chartData = [
            'labels' => $dates->map(fn($date) => \Carbon\Carbon::parse($date)->format('D d'))->all(),
            'data' => $dates->map(function($date) use ($user) {
                return $user->sales()
                    ->whereDate('created_at', $date)
                    ->sum('total');
            })->all(),
        ];

        // 5. Recent Sales
        $recent_sales = $user->sales()
            ->with([
                'branch' => fn ($query) => $query->with('business:id,name'),
                'items.product',
            ])
            ->latest()
            ->take(10)
            ->get();

        // 6. Top Products Today
        $topProducts = \App\Models\SaleItem::whereHas('sale', function($q) use ($user) {
                $q->where('cashier_id', $user->id)
                  ->whereDate('created_at', today());
            })
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'), \Illuminate\Support\Facades\DB::raw('SUM(total) as total_rev'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard.cashier', compact(
            'user', 
            'currentSession', 
            'todayStats', 
            'monthStats', 
            'chartData', 
            'recent_sales',
            'topProducts'
        ));
    }
}