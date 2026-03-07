<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with(['roomType', 'floor', 'reservations' => function($query) {
            $query->where('status', 'checked_in')->latest('check_in_date')->limit(1);
        }])->get();

        $roomStats = [
            'dirty' => $rooms->where('housekeeping_status', 'dirty')->count(),
            'inProgress' => $rooms->where('housekeeping_status', 'inspected')->count(), // Using inspected as in-progress
            'clean' => $rooms->where('housekeeping_status', 'clean')->where('status', 'available')->count(),
            'maintenance' => $rooms->where('housekeeping_status', 'maintenance_required')->count(),
            'completedToday' => $rooms->where('housekeeping_status', 'clean')
                ->whereDate('last_cleaned_at', today())
                ->count(),
        ];

        $roomsData = $rooms->map(function($room) {
            $currentReservation = $room->reservations->first();
            $statusMap = [
                'inspected' => 'in_progress',
                'maintenance_required' => 'maintenance',
            ];
            $displayStatus = $statusMap[$room->housekeeping_status] ?? $room->housekeeping_status;
            return [
                'id' => $room->id,
                'number' => $room->room_number,
                'type' => $room->roomType->name ?? 'N/A',
                'status' => $displayStatus,
                'room_status' => $room->status, // Actual room status
                'floor' => $room->floor ? ($room->floor->name ?? "Floor {$room->floor->floor_number}") : ($room->floor ?? 'Unknown'),
                'assigned_to' => null, // Can be added if housekeeping tasks are linked
                'checkout_time' => $currentReservation ? $currentReservation->check_out_date?->format('g:i A') : null,
                'notes' => $room->notes ?? '',
                'last_cleaned_at' => $room->last_cleaned_at?->format('Y-m-d H:i'),
                'last_cleaned_by' => $room->last_cleaned_by ? $room->lastCleanedBy->full_name : null,
            ];
        });

        return Inertia::render('Housekeeping/Rooms/Index', [
            'user' => auth()->user()->load('roles'),
            'rooms' => $roomsData,
            'roomStats' => $roomStats,
            'filters' => [
                'status' => $request->input('status'),
                'floor' => $request->input('floor'),
            ],
        ]);
    }

    public function markClean(Request $request, Room $room)
    {
        $room->update([
            'housekeeping_status' => 'clean',
            'status' => $room->status === 'cleaning' ? 'available' : $room->status,
            'last_cleaned_at' => now(),
            'last_cleaned_by' => auth()->id(),
        ]);

        return back()->with('success', "Room {$room->room_number} has been marked as clean and is now available.");
    }

    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:dirty,in_progress,clean,maintenance',
            'notes' => 'nullable|string'
        ]);

        $statusMap = [
            'dirty' => 'dirty',
            'in_progress' => 'inspected',
            'clean' => 'clean',
            'maintenance' => 'maintenance_required'
        ];

        $housekeepingStatus = $statusMap[$validated['status']];
        $roomStatus = $room->status;

        if ($housekeepingStatus === 'maintenance_required') {
            $roomStatus = 'maintenance';
        } elseif (in_array($housekeepingStatus, ['dirty', 'inspected'], true)) {
            if (!in_array($room->status, ['occupied', 'reserved'], true)) {
                $roomStatus = 'cleaning';
            }
        } elseif ($housekeepingStatus === 'clean') {
            if ($room->status === 'cleaning') {
                $roomStatus = 'available';
            }
        }

        $room->update([
            'housekeeping_status' => $housekeepingStatus,
            'status' => $roomStatus,
            'notes' => $validated['notes'] ?? $room->notes,
            'last_cleaned_at' => $housekeepingStatus === 'clean' ? now() : $room->last_cleaned_at,
            'last_cleaned_by' => $housekeepingStatus === 'clean' ? auth()->id() : $room->last_cleaned_by,
        ]);

        return back()->with('success', "Room {$room->room_number} status has been updated.");
    }
}
