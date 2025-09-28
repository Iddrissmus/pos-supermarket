<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use App\Models\Sale;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_businesses' => Business::count(),
            'total_users' => User::count(),
            'total_sales' => Sale::count(),
            'recent_sales' => Sale::with(['branch', 'cashier'])->latest()->take(5)->get(),
        ];

        return view('dashboard.admin', compact('stats'));
    }
}