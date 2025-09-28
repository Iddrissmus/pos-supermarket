<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Auth;

class ReorderRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // If user manages branches, show transfers to those branches; otherwise show pending transfers
        $transfers = StockTransfer::where('status', 'pending')
            ->when($user, function ($q) use ($user) {
                $q->whereHas('toBranch', function ($qb) use ($user) {
                    $qb->where('manager_id', $user->id);
                });
            })
            ->with(['product', 'toBranch'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('reorder_requests.index', ['transfers' => $transfers]);
    }
}
