<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $arrivals = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_in_date', $today)
            ->where('status', 'confirmed')
            ->get();

        $departures = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
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
                'checked_in' => false,
            ]),
            'departures' => $departures->map(fn($r) => [
                'id' => $r->id,
                'guest_name' => $r->guest->full_name ?? 'N/A',
                'room_number' => $r->room->room_number ?? 'N/A',
                'room_type' => $r->roomType->name ?? 'N/A',
                'expected_departure' => $r->check_out_date->format('Y-m-d') . 'T' . ($r->preferred_check_out_time ?? '11:00:00'),
                'checked_out' => false,
            ]),
            'roomStatus' => $roomStatus,
            'guestRequests' => [],
        ]);
    }
}
