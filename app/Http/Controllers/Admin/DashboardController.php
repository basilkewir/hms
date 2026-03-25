<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');

        // Get dashboard statistics
        $totalRooms = Room::where('is_active', true)->count();
        $occupiedRooms = Room::where('is_active', true)
            ->where('status', 'occupied')
            ->count();
        // Count API bookings that are sold for today but have no assigned room yet
        $today = today();
        $unassignedActiveToday = Reservation::whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->whereNull('room_id')
            ->count();
        $availableRooms = max(0, Room::where('is_active', true)
            ->where('status', 'available')
            ->count() - $unassignedActiveToday);

        $stats = [
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'available_rooms' => $availableRooms,
            'total_guests' => Guest::count(),
            'total_reservations' => Reservation::whereNotIn('status', ['cancelled', 'canceled'])->count(),
            'todays_revenue' => DB::table('payments')
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('amount') ?: 0,
        ];

        // Get recent reservations
        $recentReservations = Reservation::with(['guest', 'room'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'guest_name' => $reservation->guest?->name ?? 'N/A',
                    'room_number' => $reservation->room?->room_number ?? 'N/A',
                    'status' => $reservation->status,
                    'check_in' => $reservation->check_in_date?->format('M d, Y'),
                    'check_out' => $reservation->check_out_date?->format('M d, Y'),
                    'total_amount' => $reservation->total_amount,
                ];
            });

        // Get room occupancy data for the last 30 days
        $occupancyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            // Count reservations for this specific date
            $reservationsOnDate = Reservation::where('check_in_date', '<=', $dateStr)
                ->where('check_out_date', '>', $dateStr)
                ->whereNotIn('status', ['cancelled', 'canceled'])
                ->count();

            $occupancyRate = $totalRooms > 0 ? ($reservationsOnDate / $totalRooms) * 100 : 0;

            $occupancyData[] = [
                'date' => $date->format('M d'),
                'rate' => round($occupancyRate, 1),
            ];
        }

        // Get revenue data for the last 30 days from payments table
        $revenueData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            $revenue = DB::table('payments')
                ->whereDate('created_at', $dateStr)
                ->where('status', 'completed')
                ->sum('amount') ?: 0;

            $revenueData[] = [
                'date' => $date->format('M d'),
                'amount' => (float) $revenue,
            ];
        }

        // Calculate performance metrics
        $avgOccupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // Calculate average daily rate (ADR) from completed reservations
        $completedReservations = Reservation::whereIn('status', ['completed', 'checked_out'])
            ->whereNotNull('total_amount')
            ->where('total_amount', '>', 0)
            ->get();

        $totalRevenue = $completedReservations->sum('total_amount');
        $totalRoomNights = $completedReservations->sum(function ($reservation) {
            if ($reservation->check_in_date && $reservation->check_out_date) {
                return max(1, $reservation->check_in_date->diffInDays($reservation->check_out_date));
            }
            return 1;
        });

        $avgDailyRate = $totalRoomNights > 0 ? round($totalRevenue / $totalRoomNights, 2) : 0;

        // Calculate Revenue Per Available Room (RevPAR) - last 30 days
        $last30DaysRevenue = DB::table('payments')
            ->where('created_at', '>=', now()->subDays(30))
            ->where('status', 'completed')
            ->sum('amount') ?: 0;
        $revPAR = $totalRooms > 0 ? round($last30DaysRevenue / $totalRooms / 30, 2) : 0;

        $performanceMetrics = [
            'avgOccupancy' => $avgOccupancy,
            'avgDailyRate' => $avgDailyRate,
            'revPAR' => $revPAR,
        ];

        // Prepare charts data
        $charts = [
            'revenue' => $revenueData,
            'occupancy' => $occupancyData,
        ];

        // Get real recent activities from ActivityLog or fallback to reservations/payments
        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->action,
                    'description' => $activity->description ?? ucfirst($activity->action) . ' performed',
                    'created_at' => $activity->created_at,
                    'user' => $activity->user?->name ?? 'System'
                ];
            });

        // If no activity logs, create fallback activities from recent reservations
        if ($recentActivities->isEmpty()) {
            $recentActivities = Reservation::with('guest')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'type' => 'reservation',
                        'description' => 'New reservation created for ' . ($reservation->guest?->name ?? 'Guest'),
                        'created_at' => $reservation->created_at,
                        'user' => 'System'
                    ];
                });
        }

        // Get real alerts from system data
        $alerts = [
            'maintenance_required' => Room::where('status', 'maintenance')
                ->orWhere('status', 'out_of_order')
                ->orWhere('housekeeping_status', 'maintenance_required')
                ->count(),
            'system_errors' => 0,
            'pending_approvals' => Reservation::where('status', 'pending')->count(),
            'offline_devices' => Room::where('iptv_active', true)
                ->where('iptv_last_seen', '<', now()->subHours(24))
                ->whereNotNull('iptv_device_id')
                ->count(),
        ];

        // Get real system status
        $systemStatus = [
            'database' => $this->checkDatabaseConnection() ? 'Online' : 'Offline',
            'api' => 'Online', // Could be checked with API health check
            'backup' => $this->getLastBackupStatus(),
            'storage' => $this->getStorageStatus(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'navigation' => [], // Empty for now, can be populated later
            'stats' => $stats,
            'charts' => $charts,
            'recentActivities' => $recentActivities,
            'alerts' => $alerts,
            'systemStatus' => $systemStatus,
            'performanceMetrics' => $performanceMetrics,
        ]);
    }

    /**
     * Check if database connection is working
     */
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get last backup status
     */
    private function getLastBackupStatus()
    {
        // This could be enhanced to check actual backup files
        // For now, return a simple status based on recent activity
        $recentActivity = ActivityLog::where('action', 'backup')
            ->where('created_at', '>=', now()->subDays(1))
            ->exists();

        return $recentActivity ? 'Completed' : 'Pending';
    }

    /**
     * Get storage status
     */
    private function getStorageStatus()
    {
        // This could be enhanced to check actual disk space
        // For now, return a simple status
        return 'Normal';
    }
}
