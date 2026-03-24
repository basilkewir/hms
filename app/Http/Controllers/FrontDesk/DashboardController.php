<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $arrivals = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_in_date', $today)
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->orWhere(function ($nested) {
                        $nested->whereNotNull('actual_check_in')
                            ->whereNull('actual_check_out');
                    });
            })
            ->orderBy('check_in_date')
            ->get();

        $departures = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_out_date', $today)
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->orderBy('check_out_date')
            ->get();

        $currentGuests = Reservation::where('status', 'checked_in')->count();

        $roomStatus = [
            'available' => Room::where('status', 'available')->count(),
            'occupied' => Room::where('status', 'occupied')->count(),
            'cleaning' => Room::where('housekeeping_status', 'dirty')->count(), // Dirty rooms need cleaning
            'maintenance' => Room::where('status', 'maintenance')->count(),
        ];

        return Inertia::render('FrontDesk/Dashboard', [
            'user' => auth()->user()->load('roles'),
            'todaysActivities' => [
                'arrivals' => $arrivals->count(),
                'departures' => $departures->count(),
                'currentGuests' => $currentGuests,
                'availableRooms' => $roomStatus['available'],
            ],
            'arrivals' => $arrivals->map(fn($r) => [
                'id' => $r->id,
                'guest_name' => $r->guest->full_name ?? 'N/A',
                'room_number' => $r->room->room_number ?? 'TBA',
                'room_type' => $r->roomType->name ?? 'N/A',
                'expected_arrival' => $r->check_in_date->format('Y-m-d') . 'T' . ($r->preferred_check_in_time ?? '14:00:00'),
                'checked_in' => $r->status === 'checked_in' || !empty($r->actual_check_in),
            ]),
            'departures' => $departures->map(fn($r) => [
                'id' => $r->id,
                'guest_name' => $r->guest->full_name ?? 'N/A',
                'room_number' => $r->room->room_number ?? 'N/A',
                'room_type' => $r->roomType->name ?? 'N/A',
                'expected_departure' => $r->check_out_date->format('Y-m-d') . 'T' . ($r->preferred_check_out_time ?? '11:00:00'),
                'checked_out' => $r->status === 'checked_out' || !empty($r->actual_check_out),
            ]),
            'roomStatus' => $roomStatus,
            'guestRequests' => [],
        ]);
    }
}
