<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->latest();

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Get distinct action types for filter dropdown
        $actionTypes = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Get users who have activities for filter dropdown
        $users = User::whereHas('activityLogs')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $logs = $query->paginate(50);

        // Calculate statistics
        $stats = [
            'total_activities' => ActivityLog::count(),
            'today_activities' => ActivityLog::whereDate('created_at', today())->count(),
            'failed_logins' => ActivityLog::where('action', 'failed_login')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count(),
            'critical_actions' => ActivityLog::whereJsonContains('properties->metadata->severity', 'critical')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count(),
        ];

        return view('activity-logs.index', compact('logs', 'actionTypes', 'users', 'stats'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user', 'subject');
        
        return response()->json($activityLog);
    }
}
