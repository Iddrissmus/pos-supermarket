<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $businessesQuery = $user->businesses();
        $businesses = $businessesQuery->with(['branches', 'products'])->get();

        $businessIds = $businesses->pluck('id');
        $totalBusinesses = $businesses->count();
        $totalBranches = $businessIds->isNotEmpty()
            ? Branch::whereIn('business_id', $businessIds)->count()
            : 0;
        $totalProducts = $businessIds->isNotEmpty()
            ? Product::whereIn('business_id', $businessIds)->count()
            : 0;

        $recent_sales = Sale::whereHas('branch', function ($query) use ($user) {
            $query->whereHas('business', function ($q) use ($user) {
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
