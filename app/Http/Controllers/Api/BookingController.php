<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Services\RoomTypeInventoryService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    private const WEBSITE_ROOM_REFRESH_SECONDS = 30;

    public function __construct(private RoomTypeInventoryService $inventoryService)
    {
    }

    public function roomTypes()
    {
        $types = RoomType::where('is_active', true)
            ->orderBy('base_price')
            ->get()
            ->map(function ($type) {
                $amenities = is_array($type->amenities) ? $type->amenities : [];

                return [
                    'id'              => $type->id,
                    'name'            => $type->name,
                    'code'            => $type->code,
                    'description'     => $type->description,
                    'base_price'      => (float) $type->base_price,
                    'max_occupancy'   => $type->max_occupancy,
                    'max_adults'      => $type->max_adults,
                    'max_children'    => $type->max_children,
                    'bed_type'        => $type->bed_type,
                    'room_size_sqft'  => $type->room_size_sqft,
                    'view_type'       => $type->view_type,
                    'has_balcony'     => (bool) $type->has_balcony,
                    'has_living_room' => (bool) $type->has_living_room,
                    'amenities'       => $amenities,
                    'available_rooms' => Room::where('room_type_id', $type->id)
                        ->where('status', 'available')
                        ->where('housekeeping_status', 'clean')
                        ->where('is_active', true)
                        ->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $types,
            'meta' => [
                'refresh_after_seconds' => self::WEBSITE_ROOM_REFRESH_SECONDS,
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Public rooms endpoint — returns individual rooms with room type details.
     * Used by hotel website to display actual room listings (room numbers, floors, amenities).
     */
    public function rooms()
    {
        $rooms = Room::with(['roomType.amenities', 'floorRelation'])
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean')
            ->where('is_active', true)
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) {
                $type = $room->roomType;

                // Floor label: use Floor relationship if available, else numeric floor
                $floorModel = $room->floorRelation ?? null;
                $floorLabel = null;
                if ($floorModel) {
                    $floorLabel = $floorModel->name ?? ('Floor ' . $floorModel->floor_number);
                } elseif (!is_null($room->floor)) {
                    $floorLabel = $room->floor == 0 ? 'Ground Floor' : 'Floor ' . $room->floor;
                }

                // Amenities: prefer relationship (room_type_amenity pivot), fall back to JSON column
                $amenities = [];
                if ($type && $type->amenities instanceof \Illuminate\Database\Eloquent\Collection) {
                    $amenities = $type->amenities->where('is_active', true)
                        ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])
                        ->values()
                        ->toArray();
                }
                // Fall back to JSON column if relationship returns nothing
                if (empty($amenities) && is_array($type?->getAttributes()['amenities'] ?? null)) {
                    $jsonAmenities = json_decode($type->getAttributes()['amenities'], true) ?? [];
                    $amenities = array_map(function ($a) {
                        if (is_array($a) && isset($a['name'])) return $a;
                        return ['id' => $a, 'name' => (string) $a];
                    }, $jsonAmenities);
                }

                return [
                    // Room-level fields
                    'id'                  => $room->id,
                    'room_number'         => $room->room_number,
                    'floor'               => $floorModel?->floor_number ?? $room->floor ?? null,
                    'floor_label'         => $floorLabel,
                    'building'            => $room->building,
                    'wing'                => $room->wing,
                    'status'              => $room->status,
                    'housekeeping_status' => $room->housekeeping_status,
                    'is_smoking'          => (bool) ($room->is_smoking ?? false),
                    'is_accessible'       => (bool) ($room->is_accessible ?? false),
                    'notes'               => $room->notes,

                    // Room type fields (inherited)
                    'room_type_id'        => $type?->id,
                    'type_name'           => $type?->name,
                    'code'                => $type?->code,
                    'description'         => $type?->description,
                    'base_price'          => $room->custom_price ?? (float) ($type?->base_price ?? 0),
                    'max_occupancy'       => $type?->max_occupancy,
                    'max_adults'          => $type?->max_adults,
                    'max_children'        => $type?->max_children,
                    'bed_type'            => $type?->bed_type,
                    'bed_count'           => $type?->bed_count ?? 1,
                    'room_size_sqft'      => $type?->room_size_sqft,
                    'view_type'           => $type?->view_type,
                    'has_balcony'         => (bool) ($type?->has_balcony ?? false),
                    'has_living_room'     => (bool) ($type?->has_living_room ?? false),
                    'amenities'           => $amenities,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $rooms,
            'meta'    => [
                'refresh_after_seconds' => self::WEBSITE_ROOM_REFRESH_SECONDS,
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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

        $inventory = $this->inventoryService->availabilitySummary(
            $roomType->id,
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        $availableRoomsQuery = $this->availableRoomsQuery($roomType->id, $validated['check_in_date'], $validated['check_out_date']);
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
                'available_rooms' => (int) $inventory['available'],
                'rooms' => $rooms,
                'inventory_by_date' => $inventory['by_date'],
            ],
            'meta' => [
                'refresh_after_seconds' => self::WEBSITE_ROOM_REFRESH_SECONDS,
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function createHold(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_type_code' => 'nullable|string|max:50',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'quantity' => 'nullable|integer|min:1|max:5',
            'hold_minutes' => 'nullable|integer|min:5|max:30',
        ]);

        $roomType = $this->resolveRoomType($validated['room_type_id'] ?? null, $validated['room_type_code'] ?? null);
        if (!$roomType) {
            return response()->json(['success' => false, 'message' => 'Room type not found'], 404);
        }

        try {
            $hold = $this->inventoryService->createHold(
                roomTypeId: $roomType->id,
                checkInDate: $validated['check_in_date'],
                checkOutDate: $validated['check_out_date'],
                quantity: (int) ($validated['quantity'] ?? 1),
                minutesToExpire: (int) ($validated['hold_minutes'] ?? 15),
                ipAddress: $request->ip(),
                metadata: ['source' => 'public_booking_api']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'hold_token' => $hold->hold_token,
                    'expires_at' => $hold->expires_at?->toIso8601String(),
                    'room_type_id' => $hold->room_type_id,
                    'check_in_date' => $hold->check_in_date?->toDateString(),
                    'check_out_date' => $hold->check_out_date?->toDateString(),
                    'quantity' => $hold->quantity,
                ],
            ], 201);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 409);
        }
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
            'hold_token' => 'nullable|string|max:100',
        ]);

        $roomType = $this->resolveRoomType($validated['room_type_id'] ?? null, $validated['room_type_code'] ?? null);
        if (!$roomType) {
            return response()->json(['success' => false, 'message' => 'Room type not found'], 404);
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = max(1, $checkIn->diffInDays($checkOut));
        $systemUserId = $this->resolveSystemUserId();
        $holdToken = $validated['hold_token'] ?? null;

        DB::beginTransaction();
        try {
            if ($holdToken) {
                $this->inventoryService->consumeHold($holdToken, $roomType->id, $checkIn, $checkOut, 1);
            } else {
                $this->inventoryService->reserveNow($roomType->id, $checkIn, $checkOut, 1);
            }

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
                    'date_of_birth' => now()->subYears(30)->format('Y-m-d'),
                    'gender' => 'other',
                    'nationality' => 'Unknown',
                    'address' => 'N/A',
                    'city' => 'N/A',
                    'state' => 'N/A',
                    'country' => 'N/A',
                    'emergency_contact_name' => trim(($validated['guest']['first_name'] ?? '') . ' ' . ($validated['guest']['last_name'] ?? '')),
                    'emergency_contact_phone' => $validated['guest']['phone'] ?? 'N/A',
                    'emergency_contact_relationship' => 'self',
                    'id_type' => 'other',
                    'id_number' => 'WEB-' . strtoupper(Str::random(8)),
                    'id_issuing_authority' => 'Website',
                    'id_issue_date' => now()->format('Y-m-d'),
                    'id_expiry_date' => now()->addYears(10)->format('Y-m-d'),
                    'created_by' => $systemUserId,
                    'updated_by' => $systemUserId,
                ]
            );

            $reservation = Reservation::create([
                'reservation_number' => 'RES-' . strtoupper(Str::random(8)),
                'guest_id' => $guest->id,
                'room_id' => null,
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
                'created_by' => $systemUserId,
                'updated_by' => $systemUserId,
            ]);

            $this->inventoryService->refreshRange($roomType->id, $checkIn, $checkOut);

            DB::commit();
        } catch (\RuntimeException $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 409);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to complete your booking. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reservation_id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'status' => $reservation->status,
                'room_assigned' => false,
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

    private function resolveSystemUserId(): int
    {
        $userId = DB::table('users')->min('id');

        if (!$userId) {
            throw new \RuntimeException('No system user available for booking audit fields.');
        }

        return (int) $userId;
    }
}
