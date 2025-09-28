<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Assuming managers are assigned to specific branches
        $branchesQuery = Branch::where('manager_id', $user->id);
        $branches = $branchesQuery->with('business')->get();

        $businessCount = Business::count();
        $branchCount = $branchesQuery->count();
        $userCount = User::count();
        $saleCount = Sale::count();

        // Compute total products across the manager's branches via aggregate query
        $branchIds = $branchesQuery->pluck('id');
        $productCount = \App\Models\BranchProduct::whereIn('branch_id', $branchIds)->count();

        $stats = [
            'total_businesses' => $businessCount,
            'total_branches' => $branchCount,
            'total_users' => $userCount,
            'total_sales' => $saleCount,
            'total_products' => $productCount,
            'recent_sales' => Sale::with(['branch', 'cashier'])->latest()->take(5)->get(),
        ];

        return view('dashboard.manager', compact('stats', 'branches'));
    }
}