<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ActivityLog::with('user')
            ->latest();

        // Business Admin: Only see logs from their business, exclude superadmin logs
        if ($user->role === 'business_admin') {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('business_id', $user->business_id)
                  ->where('role', '!=', 'superadmin');
            });
        }
        // SuperAdmin: Can see all logs, but can filter by business
        elseif ($user->role === 'superadmin') {
            if ($request->filled('business_id')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('business_id', $request->business_id);
                });
            }
        }

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

        // Get distinct action types for filter dropdown (filtered by role)
        $actionTypesQuery = ActivityLog::select('action')->distinct();
        if ($user->role === 'business_admin') {
            $actionTypesQuery->whereHas('user', function($q) use ($user) {
                $q->where('business_id', $user->business_id)
                  ->where('role', '!=', 'superadmin');
            });
        } elseif ($user->role === 'superadmin' && $request->filled('business_id')) {
            $actionTypesQuery->whereHas('user', function($q) use ($request) {
                $q->where('business_id', $request->business_id);
            });
        }
        $actionTypes = $actionTypesQuery->orderBy('action')->pluck('action');

        // Get users who have activities for filter dropdown (filtered by role)
        $usersQuery = User::whereHas('activityLogs');
        if ($user->role === 'business_admin') {
            $usersQuery->where('business_id', $user->business_id)
                       ->where('role', '!=', 'superadmin');
        } elseif ($user->role === 'superadmin' && $request->filled('business_id')) {
            $usersQuery->where('business_id', $request->business_id);
        }
        $users = $usersQuery->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Get businesses for superadmin filter dropdown
        $businesses = null;
        if ($user->role === 'superadmin') {
            $businesses = Business::orderBy('name')->get();
        }

        $logs = $query->paginate(50);

        // Calculate statistics (filtered by role)
        $statsQuery = ActivityLog::query();
        if ($user->role === 'business_admin') {
            $statsQuery->whereHas('user', function($q) use ($user) {
                $q->where('business_id', $user->business_id)
                  ->where('role', '!=', 'superadmin');
            });
        } elseif ($user->role === 'superadmin' && $request->filled('business_id')) {
            $statsQuery->whereHas('user', function($q) use ($request) {
                $q->where('business_id', $request->business_id);
            });
        }

        $stats = [
            'total_activities' => (clone $statsQuery)->count(),
            'today_activities' => (clone $statsQuery)->whereDate('created_at', today())->count(),
            'failed_logins' => (clone $statsQuery)->where('action', 'failed_login')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count(),
            'critical_actions' => (clone $statsQuery)->whereJsonContains('properties->metadata->severity', 'critical')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count(),
        ];

        return view('activity-logs.index', compact('logs', 'actionTypes', 'users', 'stats', 'businesses'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user', 'subject');
        
        return response()->json($activityLog);
    }
}
