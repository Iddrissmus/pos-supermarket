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
        $branches = Branch::where('manager_id', $user->id)->with(['business', 'branchProducts'])->get();
        
        $stats = [
            'total_businesses' => Business::count(),
            'total_branches' => $branches->count(),
            'total_users' => User::count(),
            'total_sales' => Sale::count(),
            'total_products' => $branches->sum(function($branch) {
                return $branch->branchProducts->count();
            }),
            'recent_sales' => Sale::with(['branch', 'cashier'])->latest()->take(5)->get(),
        ];

        return view('dashboard.manager', compact('stats', 'branches'));
    }
}