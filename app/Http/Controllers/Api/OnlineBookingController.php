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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Mail\BookingConfirmation;

class OnlineBookingController extends Controller
{
    private const ONLINE_AVAILABILITY_REFRESH_SECONDS = 30;

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
            'meta' => [
                'refresh_after_seconds' => self::ONLINE_AVAILABILITY_REFRESH_SECONDS,
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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
            'guest.date_of_birth' => 'nullable|date',
            'guest.nationality' => 'required|string|max:100',
            'guest.id_type' => 'required|string',
            'guest.id_number' => 'required|string',
            // Extended optional guest fields
            'guest.title' => 'nullable|string|max:20',
            'guest.gender' => 'nullable|string|max:20',
            'guest.occupation' => 'nullable|string|max:100',
            'guest.address' => 'nullable|string|max:500',
            'guest.city' => 'nullable|string|max:100',
            'guest.state' => 'nullable|string|max:100',
            'guest.country' => 'nullable|string|max:100',
            'guest.postal_code' => 'nullable|string|max:20',
            'guest.alternate_phone' => 'nullable|string|max:20',
            'guest.emergency_contact_name' => 'nullable|string|max:255',
            'guest.emergency_contact_phone' => 'nullable|string|max:20',
            'guest.emergency_contact_relationship' => 'nullable|string|max:100',
            'guest.passport_number' => 'nullable|string|max:50',
            'guest.passport_issuing_country' => 'nullable|string|max:100',
            'guest.passport_issue_date' => 'nullable|date',
            'guest.passport_expiry_date' => 'nullable|date',
            'guest.visa_number' => 'nullable|string|max:50',
            'guest.visa_type' => 'nullable|string|max:50',
            'guest.visa_issue_date' => 'nullable|date',
            'guest.visa_expiry_date' => 'nullable|date',
            'guest.arrival_from' => 'nullable|string|max:255',
            'guest.departure_to' => 'nullable|string|max:255',
            'guest.purpose_of_visit' => 'nullable|string|max:255',
            'guest.special_requests' => 'nullable|string|max:1000',
            
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
            $systemUserId = $this->resolveSystemUserId();

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

            // Create or update guest — merge strategy:
            // - Core identity fields (name, phone, id) are always updated from the form.
            // - police_verification_status is ONLY set to 'pending' for brand-new guests;
            //   existing guests keep their current verification status so verified records
            //   are not silently downgraded on every re-booking.
            // - Extended optional fields fill in blanks only; we never overwrite existing data.
            // - guest_type_id defaults to 1 (Regular) for new online guests.
            $existingGuest = Guest::where('email', $request->guest['email'])->first();
            $guestInput = $request->guest;

            $coreFields = [
                'first_name'  => $guestInput['first_name'],
                'last_name'   => $guestInput['last_name'],
                'phone'       => $guestInput['phone'],
                'nationality' => $guestInput['nationality'],
                'id_type'     => $guestInput['id_type'],
                'id_number'   => $guestInput['id_number'],
                'gender'      => $guestInput['gender'] ?? 'other',
                'address'     => $guestInput['address'] ?? 'N/A',
                'city'        => $guestInput['city'] ?? 'N/A',
                'state'       => $guestInput['state'] ?? 'N/A',
                'country'     => $guestInput['country'] ?? 'N/A',
                'emergency_contact_name' => $guestInput['emergency_contact_name']
                    ?? trim(($guestInput['first_name'] ?? '') . ' ' . ($guestInput['last_name'] ?? '')),
                'emergency_contact_phone' => $guestInput['emergency_contact_phone']
                    ?? ($guestInput['phone'] ?? 'N/A'),
                'emergency_contact_relationship' => $guestInput['emergency_contact_relationship'] ?? 'self',
                'id_issuing_authority' => $guestInput['id_issuing_authority'] ?? 'Website',
                'id_issue_date' => $guestInput['id_issue_date'] ?? now()->format('Y-m-d'),
                'id_expiry_date' => $guestInput['id_expiry_date'] ?? now()->addYears(10)->format('Y-m-d'),
            ];

            // Only fill date_of_birth if provided (it is now optional on the website form)
            $coreFields['date_of_birth'] = !empty($guestInput['date_of_birth'])
                ? $guestInput['date_of_birth']
                : now()->subYears(30)->format('Y-m-d');

            // New guest defaults — never applied to returning guests
            if (!$existingGuest) {
                $coreFields['police_verification_status'] = 'pending';
                $coreFields['guest_type_id'] = 1; // Regular
                $coreFields['created_by'] = $systemUserId;
            }

            $coreFields['updated_by'] = $systemUserId;

            // Extended fields — only update if the current record is null/empty
            $extendedFieldKeys = [
                'title', 'gender', 'occupation',
                'address', 'city', 'state', 'country', 'postal_code', 'alternate_phone',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                'passport_number', 'passport_issuing_country', 'passport_issue_date', 'passport_expiry_date',
                'visa_number', 'visa_type', 'visa_issue_date', 'visa_expiry_date',
                'arrival_from', 'departure_to', 'purpose_of_visit', 'special_requests',
            ];

            $extendedFields = [];
            foreach ($extendedFieldKeys as $key) {
                if (isset($guestInput[$key]) && $guestInput[$key] !== '') {
                    if (!$existingGuest || empty($existingGuest->$key)) {
                        $extendedFields[$key] = $guestInput[$key];
                    }
                }
            }

            $guest = Guest::updateOrCreate(
                ['email' => $guestInput['email']],
                array_merge($coreFields, $extendedFields)
            );

            // Get room type and calculate pricing
            $roomType = RoomType::find($roomTypeId);
            $nights = $checkIn->diffInDays($checkOut);
            $roomRate = $roomType->base_price;
            $totalRoomCharges = $roomRate * $nights;
            
            $taxRate = \App\Models\Setting::get('room_tax_rate', \App\Models\Setting::get('tax_rate', 0)) / 100;
            $taxes = $totalRoomCharges * $taxRate;
            $serviceCharges = 0;
            $totalAmount = $totalRoomCharges + $taxes;
            
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
                'adults'               => $request->reservation['number_of_adults'],
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
                'created_by'           => $systemUserId,
                'updated_by'           => $systemUserId,
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

            // Send booking confirmation email (queued — does not block the API response)
            try {
                $reservation->load(['guest', 'room', 'roomType']);
                Mail::to(
                    $guest->email,
                    $guest->full_name ?? trim($guest->first_name . ' ' . $guest->last_name)
                )->queue(new BookingConfirmation($reservation, $confirmationToken));
            } catch (\Exception $mailEx) {
                Log::warning('Booking confirmation email could not be queued', [
                    'reservation' => $reservationNumber,
                    'error'       => $mailEx->getMessage(),
                ]);
            }

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

    private function resolveSystemUserId(): int
    {
        $userId = DB::table('users')->min('id');

        if (!$userId) {
            throw new \RuntimeException('No system user available for booking audit fields.');
        }

        return (int) $userId;
    }

    /**
     * Look up an existing guest by email or phone to pre-fill the online booking form.
     * Requires the X-Booking-Token header. Returns a safe subset of guest data.
     */
    public function guestLookup(Request $request)
    {
        if (!$this->verifyBookingToken($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (empty($request->email) && empty($request->phone)) {
            return response()->json(['success' => false, 'message' => 'Provide email or phone'], 422);
        }

        $query = Guest::query();

        if (!empty($request->email)) {
            $query->where('email', $request->email);
        } elseif (!empty($request->phone)) {
            $query->where('phone', $request->phone);
        }

        $guest = $query->first();

        if (!$guest) {
            return response()->json(['success' => false, 'message' => 'Guest not found', 'found' => false], 404);
        }

        // Return safe profile fields (no internal flags, no blacklist reason, no notes)
        return response()->json([
            'success' => true,
            'found'   => true,
            'guest'   => [
                'first_name'                     => $guest->first_name,
                'last_name'                      => $guest->last_name,
                'email'                          => $guest->email,
                'phone'                          => $guest->phone,
                'alternate_phone'                => $guest->alternate_phone,
                'date_of_birth'                  => $guest->date_of_birth?->format('Y-m-d'),
                'gender'                         => $guest->gender,
                'title'                          => $guest->title,
                'occupation'                     => $guest->occupation,
                'nationality'                    => $guest->nationality,
                'address'                        => $guest->address,
                'city'                           => $guest->city,
                'state'                          => $guest->state,
                'country'                        => $guest->country,
                'postal_code'                    => $guest->postal_code,
                'id_type'                        => $guest->id_type,
                'id_number'                      => $guest->id_number,
                'passport_number'                => $guest->passport_number,
                'passport_issuing_country'       => $guest->passport_issuing_country,
                'passport_expiry_date'           => $guest->passport_expiry_date?->format('Y-m-d'),
                'visa_number'                    => $guest->visa_number,
                'visa_type'                      => $guest->visa_type,
                'visa_expiry_date'               => $guest->visa_expiry_date?->format('Y-m-d'),
                'arrival_from'                   => $guest->arrival_from,
                'purpose_of_visit'               => $guest->purpose_of_visit,
                'emergency_contact_name'         => $guest->emergency_contact_name,
                'emergency_contact_phone'        => $guest->emergency_contact_phone,
                'emergency_contact_relationship' => $guest->emergency_contact_relationship,
            ],
        ]);
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
                        $reservation->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                            'total_room_charges' => 0,
                            'taxes' => 0,
                            'service_charges' => 0,
                            'discount_amount' => 0,
                            'total_amount' => 0,
                            'paid_amount' => 0,
                            'balance_amount' => 0,
                            'cancellation_charges' => 0,
                        ]);
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
