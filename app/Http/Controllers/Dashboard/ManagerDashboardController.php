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
        $branchesQuery = Branch::where('id', $user->branch_id);
        $branches = $branchesQuery->with(['business:id,name', 'branchProducts'])->get();

        $businessCount = Business::count();
        $branchCount = $branchesQuery->count();
        $userCount = User::count();
        $saleCount = Sale::count();

        // Compute total products across the manager's branches via aggregate query
        $branchIds = $branchesQuery->pluck('id');
        $productCount = \App\Models\BranchProduct::where('branch_id', $user->branch_id)->count();

        $stats = [
            'total_businesses' => $businessCount,
            'total_branches' => $branchCount,
            'total_users' => $userCount,
            'total_sales' => $saleCount,
            'total_products' => $productCount,
            'recent_sales' => Sale::with([
                'branch' => fn ($query) => $query->with('business:id,name'),
                'cashier',
            ])->latest()->take(5)->get(),
        ];

        return view('dashboard.manager', compact('stats', 'branches'));
    }
}