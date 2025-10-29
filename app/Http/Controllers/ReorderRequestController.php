<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Auth;

class ReorderRequestController extends Controller
{
    /**
     * Redirect to notifications page
     * (Reorder requests are now shown as notifications)
     */
    public function index(Request $request)
    {
        return redirect()->route('notifications.index')
            ->with('info', 'Low stock alerts are now shown in notifications. You can take action from there.');
    }
}
