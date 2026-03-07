<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\GuestType;
use App\Models\HotelService;
use App\Models\BreakfastMenu;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OnlineBookingController extends Controller
{
    /**
     * Check room availability for given dates
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $nights = $checkIn->diffInDays($checkOut);
        $adults = $request->adults;
        $children = $request->children ?? 0;

        // Get available room types
        $roomTypes = RoomType::where('is_active', true)
            ->where('max_adults', '>=', $adults)
            ->where('max_children', '>=', $children)
            ->with(['rooms' => function($query) use ($checkIn, $checkOut) {
                $query->where('status', 'available')
                    ->where('housekeeping_status', 'clean') // Only show rooms that are clean and ready
                    ->where('is_active', true)
                    ->whereDoesntHave('reservations', function($q) use ($checkIn, $checkOut) {
                        $q->where(function($query) use ($checkIn, $checkOut) {
                            $query->whereBetween('check_in_date', [$checkIn, $checkOut->copy()->subDay()])
                                ->orWhereBetween('check_out_date', [$checkIn->copy()->addDay(), $checkOut])
                                ->orWhere(function($q) use ($checkIn, $checkOut) {
                                    $q->where('check_in_date', '<=', $checkIn)
                                      ->where('check_out_date', '>=', $checkOut);
                                });
                        })->whereIn('status', ['confirmed', 'checked_in']);
                    });
            }])
            ->get()
            ->map(function($roomType) use ($nights) {
                $availableRooms = $roomType->rooms->count();
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'code' => $roomType->code,
                    'description' => $roomType->description,
                    'max_occupancy' => $roomType->max_occupancy,
                    'max_adults' => $roomType->max_adults,
                    'max_children' => $roomType->max_children,
                    'base_price' => $roomType->base_price,
                    'total_price' => $roomType->base_price * $nights,
                    'nights' => $nights,
                    'available_rooms' => $availableRooms,
                    'is_available' => $availableRooms > 0,
                    'bed_type' => $roomType->bedType ? $roomType->bedType->name : null,
                    'amenities' => $roomType->amenities ?? [],
                ];
            })
            ->filter(function($roomType) {
                return $roomType['is_available'];
            })
            ->values();

        return response()->json([
            'success' => true,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'nights' => $nights,
            'adults' => $adults,
            'children' => $children,
            'available_room_types' => $roomTypes,
        ]);
    }

    /**
     * Get available services and breakfast menus for online booking
     */
    public function getServices()
    {
        $services = HotelService::where('is_active', true)
            ->where('available_online', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'category', 'description', 'price', 'pricing_type', 'icon']);

        $breakfastMenus = BreakfastMenu::where('is_active', true)
            ->where('available_online', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'type', 'description', 'price', 'items', 'serving_time_start', 'serving_time_end']);

        return response()->json([
            'success' => true,
            'services' => $services,
            'breakfast_menus' => $breakfastMenus,
        ]);
    }

    /**
     * Verify the X-Booking-Token header — timing-safe
     */
    private function verifyBookingToken(Request $request): bool
    {
        $expected = Setting::get('integration.booking_api_token');
        $provided = $request->header('X-Booking-Token');
        if (!$expected || !$provided) {
            return false;
        }
        return hash_equals((string) $expected, (string) $provided);
    }

    /**
     * Create a reservation from online booking
     */
    public function createBooking(Request $request)
    {
        if (!$this->verifyBookingToken($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'guest' => 'required|array',
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'required|string|max:255',
            'guest.email' => 'required|email|max:255',
            'guest.phone' => 'required|string|max:20',
            'guest.date_of_birth' => 'required|date',
            'guest.nationality' => 'required|string|max:100',
            'guest.id_type' => 'required|string',
            'guest.id_number' => 'required|string',
            
            'reservation' => 'required|array',
            'reservation.room_type_id' => 'required|exists:room_types,id',
            'reservation.check_in_date' => 'required|date|after_or_equal:today',
            'reservation.check_out_date' => 'required|date|after:reservation.check_in_date',
            'reservation.number_of_adults' => 'required|integer|min:1',
            'reservation.number_of_children' => 'nullable|integer|min:0',
            
            'services' => 'nullable|array',
            'services.*.service_id' => 'nullable|exists:hotel_services,id',
            'services.*.breakfast_menu_id' => 'nullable|exists:breakfast_menus,id',
            'services.*.quantity' => 'nullable|integer|min:1',
            'services.*.service_date' => 'nullable|date',
            
            'payment' => 'nullable|array',
            'payment.deposit_amount' => 'nullable|numeric|min:0',
            'payment.payment_method' => 'nullable|string',
            'payment.transaction_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Check availability again
            $checkIn = Carbon::parse($request->reservation['check_in_date']);
            $checkOut = Carbon::parse($request->reservation['check_out_date']);
            $roomTypeId = $request->reservation['room_type_id'];
            
            // Lock the room row to prevent double-booking race conditions
            $availableRoom = Room::where('room_type_id', $roomTypeId)
                ->where('status', 'available')
                ->where('housekeeping_status', 'clean')
                ->where('is_active', true)
                ->whereDoesntHave('reservations', function ($q) use ($checkIn, $checkOut) {
                    $q->where(function ($query) use ($checkIn, $checkOut) {
                        $query->whereBetween('check_in_date', [$checkIn, $checkOut->copy()->subDay()])
                            ->orWhereBetween('check_out_date', [$checkIn->copy()->addDay(), $checkOut])
                            ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                $q->where('check_in_date', '<=', $checkIn)
                                  ->where('check_out_date', '>=', $checkOut);
                            });
                    })->whereIn('status', ['confirmed', 'checked_in']);
                })
                ->lockForUpdate()
                ->first();

            if (!$availableRoom) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No rooms available for the selected dates',
                ], 409);
            }

            // Create or find guest
            $guest = Guest::updateOrCreate(
                ['email' => $request->guest['email']],
                [
                    'first_name' => $request->guest['first_name'],
                    'last_name' => $request->guest['last_name'],
                    'phone' => $request->guest['phone'],
                    'date_of_birth' => $request->guest['date_of_birth'],
                    'nationality' => $request->guest['nationality'],
                    'id_type' => $request->guest['id_type'],
                    'id_number' => $request->guest['id_number'],
                    'police_verification_status' => 'pending',
                ]
            );

            // Get room type and calculate pricing
            $roomType = RoomType::find($roomTypeId);
            $nights = $checkIn->diffInDays($checkOut);
            $roomRate = $roomType->base_price;
            $totalRoomCharges = $roomRate * $nights;
            
            // Calculate taxes (get from settings)
            $taxRate = \App\Models\Setting::get('tax_rate', 0) / 100;
            $taxes = $totalRoomCharges * $taxRate;
            $serviceCharges = 0; // Can be configured
            $totalAmount = $totalRoomCharges + $taxes + $serviceCharges;
            
            // Handle deposit
            $depositAmount = $request->payment['deposit_amount'] ?? 0;
            $balanceAmount = $totalAmount - $depositAmount;

            // Generate reservation number — cryptographically random suffix
            $reservationNumber = 'WEB' . date('Ymd') . strtoupper(bin2hex(random_bytes(5)));

            // Confirmation token: opaque secret used to retrieve booking details without IDOR
            $confirmationToken = bin2hex(random_bytes(16));

            // Create reservation
            $reservation = Reservation::create([
                'reservation_number'   => $reservationNumber,
                'confirmation_token'   => hash('sha256', $confirmationToken), // store hashed
                'guest_id'             => $guest->id,
                'room_id'              => $availableRoom->id,
                'room_type_id'         => $roomTypeId,
                'check_in_date'        => $checkIn,
                'check_out_date'       => $checkOut,
                'nights'               => $nights,
                'number_of_adults'     => $request->reservation['number_of_adults'],
                'number_of_children'   => $request->reservation['number_of_children'] ?? 0,
                'status'               => 'confirmed',
                'room_rate'            => $roomRate,
                'total_room_charges'   => $totalRoomCharges,
                'taxes'                => $taxes,
                'service_charges'      => $serviceCharges,
                'total_price'          => $totalAmount,
                'total_amount'         => $totalAmount,
                'paid_amount'          => $depositAmount,
                'balance_amount'       => $balanceAmount,
                'booking_source'       => 'website',
                'booking_reference'    => $request->payment['transaction_id'] ?? null,
                'special_requests'     => $request->reservation['special_requests'] ?? null,
            ]);

            // Attach services if provided
            if ($request->has('services')) {
                foreach ($request->services as $service) {
                    if (isset($service['service_id'])) {
                        $hotelService = HotelService::find($service['service_id']);
                        if ($hotelService) {
                            $quantity = $service['quantity'] ?? 1;
                            $reservation->hotelServices()->attach($service['service_id'], [
                                'quantity' => $quantity,
                                'unit_price' => $hotelService->price,
                                'total_price' => $hotelService->price * $quantity,
                                'service_date' => $service['service_date'] ?? $checkIn->format('Y-m-d'),
                                'status' => 'pending',
                            ]);
                        }
                    }
                    
                    if (isset($service['breakfast_menu_id'])) {
                        $breakfastMenu = BreakfastMenu::find($service['breakfast_menu_id']);
                        if ($breakfastMenu) {
                            $quantity = $service['quantity'] ?? $request->reservation['number_of_adults'];
                            $reservation->breakfastMenus()->attach($service['breakfast_menu_id'], [
                                'quantity' => $quantity,
                                'unit_price' => $breakfastMenu->price,
                                'total_price' => $breakfastMenu->price * $quantity,
                                'service_date' => $service['service_date'] ?? $checkIn->format('Y-m-d'),
                                'status' => 'pending',
                            ]);
                        }
                    }
                }
            }

            // Update room status
            $availableRoom->update(['status' => 'reserved']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation created successfully',
                'reservation' => [
                    'reservation_number' => $reservation->reservation_number,
                    'confirmation_token' => $confirmationToken, // raw token returned once only
                    'check_in'           => $reservation->check_in_date->format('Y-m-d'),
                    'check_out'          => $reservation->check_out_date->format('Y-m-d'),
                    'room_number'        => $availableRoom->room_number,
                    'room_type'          => $roomType->name,
                    'total_amount'       => $totalAmount,
                    'paid_amount'        => $depositAmount,
                    'balance_amount'     => $balanceAmount,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Online booking failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to complete your booking. Please try again or contact the hotel.',
            ], 500);
        }
    }

    /**
     * Get booking confirmation details.
     * Requires the opaque confirmation_token returned at booking time —
     * prevents IDOR (guessing another guest's reservation_number).
     */
    public function getBookingConfirmation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_number' => 'required|string',
            'confirmation_token' => 'required|string|min:32',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $reservation = Reservation::where('reservation_number', $request->reservation_number)
            ->with(['guest', 'room', 'roomType', 'hotelServices', 'breakfastMenus'])
            ->first();

        // Verify confirmation token (compare hash of provided token against stored hash)
        if (!$reservation || !$reservation->confirmation_token ||
            !hash_equals($reservation->confirmation_token, hash('sha256', $request->confirmation_token))) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'reservation' => [
                'reservation_number' => $reservation->reservation_number,
                'guest' => [
                    'name' => $reservation->guest->full_name,
                    'email' => $reservation->guest->email,
                    'phone' => $reservation->guest->phone,
                ],
                'room' => [
                    'number' => $reservation->room->room_number ?? null,
                    'type' => $reservation->roomType->name,
                ],
                'check_in' => $reservation->check_in_date->format('Y-m-d'),
                'check_out' => $reservation->check_out_date->format('Y-m-d'),
                'nights' => $reservation->nights,
                'status' => $reservation->status,
                'total_amount' => $reservation->total_amount,
                'paid_amount' => $reservation->paid_amount,
                'balance_amount' => $reservation->balance_amount,
                'services' => $reservation->hotelServices->map(function($service) {
                    return [
                        'name' => $service->name,
                        'quantity' => $service->pivot->quantity,
                        'total_price' => $service->pivot->total_price,
                    ];
                }),
                'breakfast_menus' => $reservation->breakfastMenus->map(function ($menu) {
                    return [
                        'name'        => $menu->name,
                        'quantity'    => $menu->pivot->quantity,
                        'total_price' => $menu->pivot->total_price,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Webhook: hotel website notifies HMS of external events
     * (e.g. payment received, booking cancelled via website payment gateway)
     */
    public function handleWebhook(Request $request)
    {
        if (!$this->verifyBookingToken($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'event'              => 'required|in:payment_received,booking_cancelled,booking_updated',
            'reservation_number' => 'required|string',
            'confirmation_token' => 'required|string|min:32',
            'data'               => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $reservation = Reservation::where('reservation_number', $request->reservation_number)->first();

        if (!$reservation || !$reservation->confirmation_token ||
            !hash_equals($reservation->confirmation_token, hash('sha256', $request->confirmation_token))) {
            return response()->json(['success' => false, 'message' => 'Reservation not found'], 404);
        }

        DB::beginTransaction();
        try {
            switch ($request->event) {
                case 'payment_received':
                    $amount = (float) ($request->data['amount'] ?? 0);
                    $reservation->increment('paid_amount', $amount);
                    $reservation->decrement('balance_amount', $amount);
                    if ($reservation->fresh()->balance_amount <= 0) {
                        $reservation->update(['payment_status' => 'paid']);
                    }
                    break;

                case 'booking_cancelled':
                    if (in_array($reservation->status, ['pending', 'confirmed'])) {
                        $reservation->update(['status' => 'cancelled']);
                        if ($reservation->room_id) {
                            Room::where('id', $reservation->room_id)
                                ->where('status', 'reserved')
                                ->update(['status' => 'available']);
                        }
                    }
                    break;

                case 'booking_updated':
                    $allowed = ['special_requests', 'adults', 'children'];
                    $updates = array_intersect_key($request->data ?? [], array_flip($allowed));
                    if (!empty($updates)) {
                        $reservation->update($updates);
                    }
                    break;
            }

            DB::commit();
            Log::info('Booking webhook processed', [
                'event'              => $request->event,
                'reservation_number' => $request->reservation_number,
            ]);

            return response()->json(['success' => true, 'message' => 'Webhook processed']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Webhook processing failed'], 500);
        }
    }
}
