<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;

class RoomLookupController extends Controller
{
    /**
     * Lookup guest information by room number
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:20',
        ]);

        $room = Room::where('room_number', $validated['room_number'])->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }

        // Find active reservation for this room
        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->whereDate('check_in_date', '<=', now())
            ->whereDate('check_out_date', '>=', now())
            ->with(['guest', 'room', 'roomType'])
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'No active guest in this room',
                'room' => [
                    'number' => $room->room_number,
                    'status' => $room->status,
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'guest' => [
                'id' => $reservation->guest->id,
                'name' => $reservation->guest->full_name,
                'phone' => $reservation->guest->phone,
                'email' => $reservation->guest->email,
            ],
            'room' => [
                'id' => $room->id,
                'number' => $room->room_number,
                'type' => $room->roomType->name ?? 'N/A',
            ],
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
            ]
        ]);
    }
}
