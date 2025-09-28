<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $businessesQuery = $user->businesses();

    $totalBusinesses = $businessesQuery->count();

    // Use aggregate queries to compute totals without loading full collections
    $totalBranches = \App\Models\Branch::whereIn('business_id', $businessesQuery->pluck('id'))->count();
    $totalProducts = \App\Models\Product::whereIn('business_id', $businessesQuery->pluck('id'))->count();

        $recent_sales = Sale::whereHas('branch', function($query) use ($user) {
            $query->whereHas('business', function($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        })->with(['branch', 'cashier'])->latest()->take(5)->get();

        $stats = [
            'total_businesses' => $totalBusinesses,
            'total_branches' => $totalBranches,
            'total_products' => $totalProducts,
            'recent_sales' => $recent_sales,
        ];

        return view('dashboard.owner', compact('stats', 'businesses'));
    }
}