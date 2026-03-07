<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function roomTypes()
    {
        $types = RoomType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'base_price', 'max_occupancy'])
            ->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'base_price' => (float) $type->base_price,
                'max_occupancy' => $type->max_occupancy,
            ]);

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function availability(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_type_code' => 'nullable|string',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $roomType = $this->resolveRoomType($validated['room_type_id'] ?? null, $validated['room_type_code'] ?? null);
        if (!$roomType) {
            return response()->json(['success' => false, 'message' => 'Room type not found'], 404);
        }

        $availableRoomsQuery = $this->availableRoomsQuery($roomType->id, $validated['check_in_date'], $validated['check_out_date']);
        $availableRooms = $availableRoomsQuery->count();
        $rooms = $availableRoomsQuery
            ->limit(20)
            ->get(['id', 'room_number'])
            ->map(fn ($room) => [
                'id' => $room->id,
                'room_number' => $room->room_number,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'room_type_id' => $roomType->id,
                'available_rooms' => $availableRooms,
                'rooms' => $rooms,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $token = Setting::get('integration.booking_api_token');
        $providedToken = $request->header('X-Booking-Token');

        // hash_equals() prevents timing attacks on secret token comparison
        if (!$token || !$providedToken || !hash_equals((string) $token, (string) $providedToken)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'required|string|max:255',
            'guest.email' => 'required|email|max:255',
            'guest.phone' => 'nullable|string|max:50',
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_type_code' => 'nullable|string|max:50',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'booking_reference' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $roomType = $this->resolveRoomType($validated['room_type_id'] ?? null, $validated['room_type_code'] ?? null);
        if (!$roomType) {
            return response()->json(['success' => false, 'message' => 'Room type not found'], 404);
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $room = $this->availableRoomsQuery($roomType->id, $checkIn, $checkOut)->first();

        $roomCharges = (float) $roomType->base_price * $nights;
        $taxRate = (float) Setting::get('tax_rate', 0);
        $serviceChargeRate = (float) Setting::get('service_charge_rate', 0);
        $taxAmount = $roomCharges * ($taxRate / 100);
        $serviceCharges = $roomCharges * ($serviceChargeRate / 100);
        $totalAmount = $roomCharges + $taxAmount + $serviceCharges;

        $guest = Guest::updateOrCreate(
            ['email' => $validated['guest']['email']],
            [
                'first_name' => $validated['guest']['first_name'],
                'last_name' => $validated['guest']['last_name'],
                'phone' => $validated['guest']['phone'] ?? null,
                'created_by' => null,
                'updated_by' => null,
            ]
        );

        $reservation = Reservation::create([
            'reservation_number' => 'RES-' . strtoupper(Str::random(8)),
            'guest_id' => $guest->id,
            'room_id' => $room?->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'nights' => $nights,
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'infants' => $validated['infants'] ?? 0,
            'status' => 'pending',
            'room_rate' => (float) $roomType->base_price,
            'total_room_charges' => $roomCharges,
            'taxes' => $taxAmount,
            'service_charges' => $serviceCharges,
            'total_amount' => $totalAmount,
            'balance_amount' => $totalAmount,
            'booking_source' => 'website',
            'booking_reference' => $validated['booking_reference'] ?? null,
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'reservation_id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'status' => $reservation->status,
                'room_assigned' => (bool) $room,
            ],
        ], 201);
    }

    private function resolveRoomType(?int $roomTypeId, ?string $roomTypeCode): ?RoomType
    {
        if ($roomTypeId) {
            return RoomType::where('is_active', true)->find($roomTypeId);
        }
        if ($roomTypeCode) {
            return RoomType::where('is_active', true)->where('code', $roomTypeCode)->first();
        }
        return null;
    }

    private function availableRoomsQuery(int $roomTypeId, $checkIn, $checkOut)
    {
        return Room::where('room_type_id', $roomTypeId)
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean')
            ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->where(function ($sub) use ($checkIn, $checkOut) {
                        $sub->whereDate('check_in_date', '<', $checkOut)
                            ->whereDate('check_out_date', '>', $checkIn);
                    });
            });
    }
}
