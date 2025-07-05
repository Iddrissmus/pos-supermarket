<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class CashierDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recent_sales = $user->sales()->with(['branch', 'items.product'])->latest()->take(10)->get();
        
        $stats = [
            'total_sales' => $user->sales()->count(),
            'today_sales' => $user->sales()->whereDate('created_at', today())->count(),
            'monthly_sales' => $user->sales()->whereMonth('created_at', now()->month)->count(),
        ];

        return view('dashboard.cashier', compact('stats', 'recent_sales'));
    }
}