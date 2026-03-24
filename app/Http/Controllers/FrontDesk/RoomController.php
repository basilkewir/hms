<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\KeyCard;
use App\Models\GuestFolio;
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

        $activeReservationIds = $rooms->map(function ($room) {
            $activeReservation = $room->currentReservation
                ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first() : null);

            return $activeReservation?->id;
        })->filter()->unique()->values();

        $foliosByReservationId = GuestFolio::whereIn('reservation_id', $activeReservationIds)
            ->with(['charges' => function ($query) {
                $query->where('is_voided', false)
                    ->orderByDesc('charge_date')
                    ->orderByDesc('charge_time');
            }])
            ->get()
            ->keyBy('reservation_id');

        $mapRoomData = function ($room) use ($foliosByReservationId) {
            $activeReservation = $room->currentReservation
                ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first() : null);
            $activeKeyCard = $activeReservation?->activeKeyCard;
            $activeFolio = $activeReservation ? $foliosByReservationId->get($activeReservation->id) : null;
            $folioCharges = $activeFolio?->charges ?? collect();
            $serviceCharges = (float) $folioCharges->where('charge_code', 'SERVICE')->sum('net_amount');
            $posCharges = (float) $folioCharges->where('charge_code', 'POS')->sum('net_amount');
            $additionalRoomCharges = $serviceCharges + $posCharges;
            $nights = $activeReservation
                ? $activeReservation->check_in_date->diffInDays($activeReservation->check_out_date)
                : 0;
            $roomRate = (float) ($activeReservation?->room_rate ?? $room->roomType->base_price ?? 0);
            $totalRoomCharges = (float) ($activeFolio?->room_charges
                ?? $activeReservation?->total_room_charges
                ?? ($roomRate * $nights));

            return [
                'id' => $room->id,
                'number' => $room->room_number,
                'floor' => $room->floor ? ($room->floor->name ?? "Floor {$room->floor->floor_number}") : ($room->floor ?? 'Unknown'),
                'floor_number' => $room->floor?->floor_number ?? $room->floor ?? 0,
                'type' => $room->roomType->name ?? 'N/A',
                'status' => $room->status,
                'housekeeping_status' => $room->housekeeping_status,
                'price' => $roomRate,
                'capacity' => $room->roomType->max_occupancy ?? 0,
                'guest' => $activeReservation?->guest?->full_name,
                'guest_phone' => $activeReservation?->guest?->phone,
                'guest_email' => $activeReservation?->guest?->email,
                'check_in' => $activeReservation?->check_in_date?->format('Y-m-d H:i'),
                'check_out' => $activeReservation?->check_out_date?->format('Y-m-d H:i'),
                'reservation_id' => $activeReservation?->id,
                'pending_reservation' => $room->pendingReservations->isNotEmpty() ? [
                    'id' => $room->pendingReservations->first()->id,
                    'reservation_number' => $room->pendingReservations->first()->reservation_number,
                    'guest_name' => $room->pendingReservations->first()->guest?->full_name ?? 'N/A',
                    'check_in_date' => $room->pendingReservations->first()->check_in_date?->format('Y-m-d'),
                    'check_out_date' => $room->pendingReservations->first()->check_out_date?->format('Y-m-d'),
                ] : null,
                'nights' => $nights,
                'room_rate' => $roomRate,
                'total_room_charges' => $totalRoomCharges,
                'service_charges' => $serviceCharges,
                'pos_charges' => $posCharges,
                'additional_room_charges' => $additionalRoomCharges,
                'room_posted_charges' => $folioCharges
                    ->whereIn('charge_code', ['SERVICE', 'POS'])
                    ->map(fn($charge) => [
                        'id' => $charge->id,
                        'type' => $charge->charge_code,
                        'description' => $charge->description,
                        'amount' => (float) $charge->net_amount,
                        'charge_date' => $charge->charge_date?->format('Y-m-d'),
                        'charge_time' => $charge->charge_time?->format('H:i'),
                        'department' => $charge->department,
                    ])->values(),
                'total_amount' => (float) ($activeFolio?->total_amount ?? $activeReservation?->total_amount ?? 0),
                'balance' => (float) ($activeFolio?->balance_amount ?? $activeReservation?->balance_amount ?? 0),
                'amenities' => $room->roomType?->amenities ?? [],
                'bed_type' => $room->roomType?->bed_type ?? 'N/A',
                'view_type' => $room->roomType?->view_type ?? 'N/A',
                'last_cleaned' => $room->last_cleaned_at?->diffForHumans(),
                'key_card' => $activeKeyCard ? [
                    'id' => $activeKeyCard->id,
                    'card_number' => $activeKeyCard->card_number,
                    'card_type' => $activeKeyCard->card_type,
                ] : null,
            ];
        };

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
                'rooms' => $floorRooms->map($mapRoomData)->sortBy('number')->values(),
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
            'rooms' => $rooms->map($mapRoomData),
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
