<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');

        // Get dashboard statistics
        $stats = [
            'totalReservations' => Reservation::count(),
            'activeReservations' => Reservation::whereIn('status', ['confirmed', 'checked_in'])->count(),
            'todayCheckins' => Reservation::whereDate('check_in_date', today())->count(),
            'todayCheckouts' => Reservation::whereDate('check_out_date', today())->count(),
            'totalRooms' => Room::count(),
            'occupiedRooms' => Room::where('status', 'occupied')->count(),
            'availableRooms' => Room::where('status', 'available')->count(),
            'totalGuests' => Guest::count(),
            'totalUsers' => User::count(),
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

        // Get room occupancy data for the last 7 days
        $occupancyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $occupiedRooms = Room::where('status', 'occupied')->count();
            $totalRooms = Room::count();
            $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;
            
            $occupancyData[] = [
                'date' => $date->format('M d'),
                'occupancy' => round($occupancyRate, 1),
            ];
        }

        // Get revenue data for the last 30 days
        $revenueData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Reservation::whereDate('created_at', $date->format('Y-m-d'))
                ->sum('total_amount');
            
            $revenueData[] = [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
            ];
        }

        return Inertia::render('Manager/Dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentReservations' => $recentReservations,
            'occupancyData' => $occupancyData,
            'revenueData' => $revenueData,
        ]);
    }
}
