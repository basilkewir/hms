<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\IptvDevice;
use App\Models\InventoryRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $user = $request->user();
        
        // Work Orders Stats
        $workOrderStats = [
            'open' => MaintenanceRequest::where('status', 'pending')->count(),
            'inProgress' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'completedToday' => MaintenanceRequest::where('status', 'resolved')
                ->whereDate('updated_at', $today)->count(),
        ];

        // IPTV Stats
        $totalDevices = IptvDevice::count();
        $onlineDevices = IptvDevice::where('status', 'online')
            ->orWhere(function($q) {
                $q->where('last_activity', '>=', Carbon::now()->subMinutes(15));
            })->count();

        $iptvStats = [
            'totalDevices' => $totalDevices,
            'onlineDevices' => $onlineDevices,
        ];

        // My Work Orders (assigned to current user)
        $myWorkOrders = MaintenanceRequest::with('room')
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($wo) => [
                'id' => $wo->id,
                'title' => $wo->title ?? ucfirst($wo->category ?? 'Maintenance'),
                'location' => $wo->room ? 'Room ' . $wo->room->room_number : 'General',
                'priority' => $wo->priority ?? 'medium',
                'status' => $wo->status,
                'type' => $wo->category ?? 'general',
            ]);

        // IPTV Devices (recent activity)
        $iptvDevices = IptvDevice::with('room')
            ->orderByDesc('last_activity')
            ->limit(5)
            ->get()
            ->map(function($device) {
                $lastActivity = $device->last_activity;
                $online = $device->is_active && (
                    $device->status === 'online'
                    || ($lastActivity && $lastActivity->gte(Carbon::now()->subMinutes(15)))
                );

                return [
                    'id' => $device->id,
                    'room_number' => $device->room?->room_number ?? 'N/A',
                    'device_type' => $device->device_type ?? 'Android Box',
                    'ip_address' => $device->ip_address,
                    'online' => $online,
                    'last_seen' => $lastActivity ? $lastActivity->diffForHumans() : 'Never',
                ];
            });

        // Scheduled Maintenance Tasks
        $scheduledTasks = MaintenanceRequest::whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '>=', $today)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderBy('scheduled_date')
            ->limit(5)
            ->get()
            ->map(function($task) use ($today) {
                $status = 'pending';
                if (in_array($task->status, ['resolved', 'closed'])) {
                    $status = 'completed';
                } elseif ($task->scheduled_date && Carbon::parse($task->scheduled_date)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $task->id,
                    'description' => $task->title ?? 'Scheduled Maintenance',
                    'equipment' => ucfirst($task->category ?? 'General'),
                    'due_date' => $task->scheduled_date?->format('Y-m-d'),
                    'status' => $status,
                ];
            });

        // Inventory (low stock items)
        $inventory = InventoryRequest::where('department', 'maintenance')
            ->where('status', 'pending')
            ->where('priority', 'high')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->item_name,
                'category' => ucfirst($item->category ?? 'General'),
                'quantity' => $item->quantity ?? 0,
                'min_quantity' => 5, // Default minimum
            ]);

        return Inertia::render('Maintenance/Dashboard', [
            'user' => $user->load('roles'),
            'workOrderStats' => $workOrderStats,
            'iptvStats' => $iptvStats,
            'myWorkOrders' => $myWorkOrders,
            'iptvDevices' => $iptvDevices,
            'scheduledTasks' => $scheduledTasks,
            'inventory' => $inventory,
        ]);
    }
}
