<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\KeyCard;
use Inertia\Inertia;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with([
            'roomType',
            'currentReservation.guest',
            'currentReservation.activeKeyCard',
            'floor',
            'reservations' => function($query) {
                $query->where('status', 'checked_in')->with(['guest', 'activeKeyCard'])->latest('check_in_date')->limit(1);
            },
            'pendingReservations' => function($query) {
                $query->whereIn('status', ['confirmed', 'pending'])
                    ->whereDate('check_in_date', '<=', now())
                    ->with('guest')
                    ->latest('check_in_date')
                    ->limit(1);
            }
        ])->get();

        // For occupied rooms, ensure we have the reservation loaded even if currentReservation doesn't match
        // This handles cases where check-out date has passed but room is still marked as occupied
        foreach ($rooms as $room) {
            if ($room->status === 'occupied') {
                // If we don't have a currentReservation or any reservations loaded, try to find one
                if (!$room->currentReservation && $room->reservations->isEmpty()) {
                    // First try to find a checked_in reservation
                    $checkedInReservation = Reservation::where('room_id', $room->id)
                        ->where('status', 'checked_in')
                        ->with(['guest', 'activeKeyCard'])
                        ->latest('check_in_date')
                        ->first();

                    // If no checked_in reservation, try to find any recent reservation for this room
                    // (in case status is wrong or reservation needs to be checked out)
                    if (!$checkedInReservation) {
                        $checkedInReservation = Reservation::where('room_id', $room->id)
                            ->whereIn('status', ['checked_in', 'confirmed', 'pending'])
                            ->whereNull('actual_check_out')
                            ->with(['guest', 'activeKeyCard'])
                            ->latest('check_in_date')
                            ->first();
                    }

                    if ($checkedInReservation) {
                        $room->setRelation('reservations', collect([$checkedInReservation]));
                    }
                }
            }
        }

        $roomStatus = [
            'available' => $rooms->where('status', 'available')->count(),
            'occupied' => $rooms->where('status', 'occupied')->count(),
            'cleaning' => $rooms->where('housekeeping_status', 'dirty')->count(),
            'maintenance' => $rooms->where('status', 'maintenance')->count(),
        ];

        // Group rooms by floor for graphical display
        $roomsByFloor = $rooms->groupBy(function($room) {
            if ($room->floor) {
                return $room->floor->floor_number ?? $room->floor->name ?? 'Unknown';
            }
            // Fallback for legacy floor column
            return $room->floor ?? 'Unknown';
        })->map(function($floorRooms, $floorNumber) {
            return [
                'floor_number' => $floorNumber,
                'floor_name' => $floorRooms->first()->floor?->name ?? "Floor $floorNumber",
                'rooms' => $floorRooms->map(fn($room) => [
                    'id' => $room->id,
                    'number' => $room->room_number,
                    'floor' => $room->floor ? ($room->floor->name ?? "Floor {$room->floor->floor_number}") : ($room->floor ?? 'Unknown'),
                    'floor_number' => $room->floor?->floor_number ?? $room->floor ?? 0,
                    'type' => $room->roomType->name ?? 'N/A',
                    'status' => $room->status,
                    'housekeeping_status' => $room->housekeeping_status,
                    // Use reservation's room_rate if available, otherwise fall back to room type base_price
                    'price' => ($room->currentReservation?->room_rate ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->room_rate : null)) ?? ($room->roomType->base_price ?? 0),
                    'capacity' => $room->roomType->max_occupancy ?? 0,
                    'guest' => $room->currentReservation?->guest?->full_name ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->full_name : null),
                    'guest_phone' => $room->currentReservation?->guest?->phone ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->phone : null),
                    'guest_email' => $room->currentReservation?->guest?->email ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->email : null),
                    'check_in' => $room->currentReservation?->check_in_date?->format('Y-m-d H:i') ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->check_in_date?->format('Y-m-d H:i') : null),
                    'check_out' => $room->currentReservation?->check_out_date?->format('Y-m-d H:i') ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->check_out_date?->format('Y-m-d H:i') : null),
                    'reservation_id' => $room->currentReservation?->id ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->id : null),
                    'nights' => ($room->currentReservation ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first() : null)) ? (($room->currentReservation ?? $room->reservations->first())->check_in_date->diffInDays(($room->currentReservation ?? $room->reservations->first())->check_out_date)) : 0,
                    'total_amount' => $room->currentReservation?->total_amount ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->total_amount ?? 0 : 0),
                    'balance' => $room->currentReservation?->balance_amount ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->balance_amount ?? 0 : 0),
                'amenities' => $room->roomType?->amenities ?? [],
                'bed_type' => $room->roomType?->bed_type ?? 'N/A',
                'view_type' => $room->roomType?->view_type ?? 'N/A',
                'last_cleaned' => $room->last_cleaned_at?->diffForHumans(),
                'key_card' => ($room->currentReservation?->activeKeyCard ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->activeKeyCard : null)) ? [
                    'id' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->id,
                    'card_number' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->card_number,
                    'card_type' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->card_type,
                ] : null,
                ])->sortBy('number')->values(),
            ];
        })->sortBy('floor_number')->values();

        // Get available key cards for check-in
        $availableKeyCards = KeyCard::available()->get();

        return Inertia::render('FrontDesk/Rooms/Index', [
            'user' => auth()->user()->load('roles'),
            'availableKeyCards' => $availableKeyCards->map(fn($card) => [
                'id' => $card->id,
                'card_number' => $card->card_number,
                'card_type' => $card->card_type,
            ]),
            'rooms' => $rooms->map(fn($room) => [
                'id' => $room->id,
                'number' => $room->room_number,
                'floor' => $room->floor ? ($room->floor->name ?? "Floor {$room->floor->floor_number}") : ($room->floor ?? 'Unknown'),
                'floor_number' => $room->floor?->floor_number ?? $room->floor ?? 0,
                'type' => $room->roomType->name ?? 'N/A',
                'status' => $room->status,
                'housekeeping_status' => $room->housekeeping_status,
                'price' => $room->roomType->base_price ?? 0,
                'capacity' => $room->roomType->max_occupancy ?? 0,
                'guest' => $room->currentReservation?->guest?->full_name ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->full_name : null),
                'guest_phone' => $room->currentReservation?->guest?->phone ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->phone : null),
                'guest_email' => $room->currentReservation?->guest?->email ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->email : null),
                'check_in' => $room->currentReservation?->check_in_date?->format('Y-m-d H:i') ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->check_in_date?->format('Y-m-d H:i') : null),
                'check_out' => $room->currentReservation?->check_out_date?->format('Y-m-d H:i') ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->check_out_date?->format('Y-m-d H:i') : null),
                'reservation_id' => $room->currentReservation?->id ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->id : null),
                'pending_reservation' => $room->pendingReservations->isNotEmpty() ? [
                    'id' => $room->pendingReservations->first()->id,
                    'reservation_number' => $room->pendingReservations->first()->reservation_number,
                    'guest_name' => $room->pendingReservations->first()->guest?->full_name ?? 'N/A',
                    'check_in_date' => $room->pendingReservations->first()->check_in_date?->format('Y-m-d'),
                    'check_out_date' => $room->pendingReservations->first()->check_out_date?->format('Y-m-d'),
                ] : null,
                'nights' => ($room->currentReservation ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first() : null)) ? (($room->currentReservation ?? $room->reservations->first())->check_in_date->diffInDays(($room->currentReservation ?? $room->reservations->first())->check_out_date)) : 0,
                'total_amount' => $room->currentReservation?->total_amount ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->total_amount ?? 0 : 0),
                'balance' => $room->currentReservation?->balance_amount ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->balance_amount ?? 0 : 0),
                'amenities' => $room->roomType?->amenities ?? [],
                'bed_type' => $room->roomType?->bed_type ?? 'N/A',
                'view_type' => $room->roomType?->view_type ?? 'N/A',
                'last_cleaned' => $room->last_cleaned_at?->diffForHumans(),
                'key_card' => ($room->currentReservation?->activeKeyCard ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->activeKeyCard : null)) ? [
                    'id' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->id,
                    'card_number' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->card_number,
                    'card_type' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->card_type,
                ] : null,
            ]),
            'roomsByFloor' => $roomsByFloor,
            'roomStatus' => $roomStatus,
        ]);
    }

    /**
     * Manual checkout: mark an occupied room as available when no reservation is linked.
     * Use when a room shows occupied but has no active reservation (data inconsistency).
     */
    public function manualCheckout(Request $request, Room $room)
    {
        if ($room->status !== 'occupied') {
            return back()->with('error', 'Room is not occupied. Manual checkout only applies to occupied rooms.');
        }

        $room->update([
            'status' => 'cleaning', // Room needs cleaning before becoming available
            'housekeeping_status' => 'dirty',
        ]);

        return back()->with('success', "Room {$room->room_number} has been manually checked out and marked for cleaning.");
    }

    /**
     * Mark room as clean (housekeeping completion)
     * Sets housekeeping_status to 'clean' and status to 'available' if room was in 'cleaning' status
     */
    public function markClean(Request $request, Room $room)
    {
        $room->update([
            'housekeeping_status' => 'clean',
            'status' => $room->status === 'cleaning' ? 'available' : $room->status, // Only change to available if was cleaning
            'last_cleaned_at' => now(),
            'last_cleaned_by' => auth()->id(),
        ]);

        return back()->with('success', "Room {$room->room_number} has been marked as clean and is now available.");
    }
}
