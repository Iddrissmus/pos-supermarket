<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemTransaction;
use App\Models\Business;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemTransaction::with('business')->latest();

        // Filters
        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        if ($request->filled('payout_status')) {
            $query->where('payout_status', $request->payout_status);
        }
        
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $transactions = $query->paginate(20);
        $businesses = Business::orderBy('name')->get();

        // Stats for cards (contextual based on filter or global)
        $msg = "Showing all transactions";
        $totalPending = SystemTransaction::where('payout_status', 'pending')->sum('amount');
        $totalRevenue = SystemTransaction::sum('amount'); // This is total volume, not platform revenue

        return view('superadmin.transactions.index', compact('transactions', 'businesses', 'totalPending', 'totalRevenue'));
    }
}
