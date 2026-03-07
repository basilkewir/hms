<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HousekeepingNotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = HousekeepingNotification::where('user_id', $user->id)
            ->with(['room', 'reservation', 'housekeepingTask'])
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'is_read' => $notification->is_read,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'room' => $notification->room ? [
                        'id' => $notification->room->id,
                        'room_number' => $notification->room->room_number,
                    ] : null,
                    'reservation' => $notification->reservation ? [
                        'id' => $notification->reservation->id,
                        'reservation_number' => $notification->reservation->reservation_number,
                    ] : null,
                    'task' => $notification->housekeepingTask ? [
                        'id' => $notification->housekeepingTask->id,
                        'status' => $notification->housekeepingTask->status,
                    ] : null,
                    'metadata' => $notification->metadata,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => HousekeepingNotification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count(),
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = HousekeepingNotification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        HousekeepingNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        $count = HousekeepingNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
