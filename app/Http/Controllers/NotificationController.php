<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications (for AJAX/API)
     */
    public function unread()
    {
        $notifications = Auth::user()->unreadNotifications;
        
        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications->take(5)->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $notification->data['icon'] ?? 'fa-bell',
                    'color' => $notification->data['color'] ?? 'blue',
                    'urgency' => $notification->data['urgency'] ?? 'normal',
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        }
        
        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        }
        
        return redirect()->back()->with('success', 'Notification deleted successfully');
    }
}
