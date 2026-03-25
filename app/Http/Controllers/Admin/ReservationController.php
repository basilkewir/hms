<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\GroupBooking;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user()->load('roles');
        $isManager = $user->hasRole('manager');
        $viewPath = $isManager ? 'Manager/Reservations/Index' : 'Admin/Reservations/Index';

        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 15;
        }

        $reservations = Reservation::with(['guest', 'room', 'roomType'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn($r) => [
                'id'                  => $r->id,
                'confirmation_number' => $r->reservation_number,
                'guest_name'          => $r->guest ? trim(($r->guest->first_name ?? '') . ' ' . ($r->guest->last_name ?? '')) ?: ($r->guest->full_name ?? 'N/A') : 'N/A',
                'guest_email'         => $r->guest?->email ?? '',
                'check_in_date'       => $r->check_in_date instanceof \Carbon\Carbon ? $r->check_in_date->format('Y-m-d') : $r->check_in_date,
                'check_out_date'      => $r->check_out_date instanceof \Carbon\Carbon ? $r->check_out_date->format('Y-m-d') : $r->check_out_date,
                'room_number'         => $r->room?->room_number ?? null,
                'room_type'           => $r->roomType?->name ?? null,
                'adults'              => $r->number_of_adults ?? $r->adults ?? 0,
                'children'            => $r->number_of_children ?? $r->children ?? 0,
                'status'              => $r->status,
                'total_amount'        => $r->total_amount ?? 0,
            ]);

        $reservationStats = [
            'arrivals'        => Reservation::whereDate('check_in_date', today())->whereNotIn('status', ['cancelled', 'no_show'])->count(),
            'departures'      => Reservation::whereDate('check_out_date', today())->whereNotIn('status', ['cancelled', 'no_show'])->count(),
            'pendingCheckins' => Reservation::whereDate('check_in_date', today())->whereIn('status', ['confirmed', 'pending'])->count(),
            'occupiedRooms'   => Reservation::where('status', 'checked_in')->count(),
        ];

        return Inertia::render($viewPath, [
            'user'             => $user,
            'reservations'     => $reservations,
            'reservationStats' => $reservationStats,
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'pdf');
        $limit = $request->input('limit', 500);

        $reservations = Reservation::with(['guest', 'room', 'roomType'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $reportData = [
            'hotel_name' => Setting::get('hotel_name', 'Hotel Management System'),
            'title' => 'Reservations Report',
            'filename' => 'reservations_report',
            'currency' => [
                'code' => Setting::get('currency_code', 'USD'),
                'symbol' => Setting::get('currency_symbol', '$'),
            ]
        ];

        $view = 'exports.reservations';
        $fileName = 'reservations_report_' . now()->format('Y-m-d_H-i-s');

        $exportService = new \App\Services\ExportService();
        return $exportService->export($view, [
            'reservations' => $reservations,
            'reportData' => $reportData,
            'reportType' => 'reservations'
        ], $fileName, $format);
    }

    public function create()
    {
        $guests = Guest::with('guestType')->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'phone', 'guest_type_id', 'is_vip'])
            ->map(function($guest) {
                return [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'guest_type_id' => $guest->guest_type_id,
                    'is_vip' => $guest->is_vip,
                    'guest_type' => $guest->guestType ? [
                        'id' => $guest->guestType->id,
                        'name' => $guest->guestType->name,
                        'code' => $guest->guestType->code,
                        'discount_percentage' => $guest->guestType->discount_percentage,
                        'color' => $guest->guestType->color,
                    ] : null,
                ];
            });
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'base_price', 'max_occupancy'])
            ->map(function($roomType) {
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'code' => $roomType->code,
                    'price' => $roomType->base_price, // Map base_price to price for frontend
                    'base_price' => $roomType->base_price,
                    'max_occupancy' => $roomType->max_occupancy,
                    'capacity' => $roomType->max_occupancy, // Map max_occupancy to capacity for frontend
                ];
            });
        $groupBookings = GroupBooking::where('status', '!=', 'cancelled')
            ->orderBy('check_in_date', 'desc')
            ->get(['id', 'group_number', 'group_name', 'check_in_date', 'check_out_date']);

        // Get available rooms for today
        $availableRooms = Room::with('roomType')
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean') // Only show rooms that are clean and ready
            ->get()
            ->map(fn($room) => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type_id' => $room->room_type_id,
                'room_type' => $room->roomType?->name,
                'status' => $room->status,
            ]);

        // Get active hotel services for additional services
        $hotelServices = \App\Models\HotelService::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'description', 'price', 'pricing_type', 'is_free'])
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'description' => $service->description,
                    'price' => (float) $service->price,
                    'pricing_type' => $service->pricing_type,
                    'is_free' => $service->is_free,
                ];
            });

        // Determine which view to render based on route name (prioritize route over role)
        $routeName = request()->route()->getName() ?? '';
        $user = auth()->user();

        $viewPath = 'Admin/Reservations/Create';

        // Prioritize route name - if route starts with manager., use manager view
        if (str_starts_with($routeName, 'manager.')) {
            $viewPath = 'Manager/Reservations/Create';
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            $viewPath = 'FrontDesk/Reservations/Create';
        } elseif ($user->hasRole('manager') && !$user->hasRole('admin')) {
            // Fallback: if user is manager (but not admin), use manager view
            $viewPath = 'Manager/Reservations/Create';
        } elseif ($user->hasRole('front_desk') && !$user->hasRole('admin') && !$user->hasRole('manager')) {
            // Fallback: if user is front_desk only, use front-desk view
            $viewPath = 'FrontDesk/Reservations/Create';
        }

        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'guests' => $guests,
            'roomTypes' => $roomTypes,
            'groupBookings' => $groupBookings,
            'availableRooms' => $availableRooms,
            'hotelServices' => $hotelServices,
            'taxRate' => (float) Setting::get('room_tax_rate', Setting::get('tax_rate', 0)),
            'serviceChargeRate' => (float) Setting::get('service_charge_rate', 0),
            'bookingSources' => [
                'walk_in' => 'Walk-in',
                'phone' => 'Phone',
                'email' => 'Email',
                'website' => 'Website',
                'booking_com' => 'Booking.com',
                'expedia' => 'Expedia',
                'agoda' => 'Agoda',
                'travel_agent' => 'Travel Agent',
                'corporate' => 'Corporate',
            ],
        ]);
    }

    public function store(Request $request)
    {
        // Handle guest creation if guest information is provided instead of guest_id
        $guestId = $request->input('guest_id');

        if (!$guestId && ($request->has('guest_first_name') || $request->has('guest_email'))) {
            // Validate guest creation fields
            $guestData = $request->validate([
                'guest_first_name' => 'required|string|max:255',
                'guest_last_name' => 'required|string|max:255',
                'guest_email' => 'required|email|max:255',
                'guest_phone' => 'nullable|string|max:20',
                'guest_address' => 'nullable|string',
            ]);

            // Create or find guest (use updateOrCreate to handle existing guests)
            $guest = Guest::updateOrCreate(
                ['email' => $guestData['guest_email']],
                [
                    'first_name' => $guestData['guest_first_name'],
                    'last_name' => $guestData['guest_last_name'],
                    'phone' => $guestData['guest_phone'] ?? null,
                    'address' => $guestData['guest_address'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]
            );
            $guestId = $guest->id;
        }

        $validated = $request->validate([
            'guest_id' => $guestId ? 'required|exists:guests,id' : 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_id' => 'nullable|exists:rooms,id',
            'number_of_rooms' => 'required|integer|min:1|max:10',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'booking_source' => 'required|in:walk_in,phone,email,website,booking_com,expedia,agoda,travel_agent,corporate',
            'booking_reference' => 'nullable|string|max:255',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string',
            'room_preferences' => 'nullable|array',
            'early_check_in_requested' => 'nullable|boolean',
            'late_check_out_requested' => 'nullable|boolean',
            'preferred_check_in_time' => 'nullable|date_format:H:i',
            'preferred_check_out_time' => 'nullable|date_format:H:i',
            'breakfast_included' => 'nullable|boolean',
            'wifi_included' => 'nullable|boolean',
            'parking_required' => 'nullable|boolean',
            'airport_pickup' => 'nullable|boolean',
            'airport_drop' => 'nullable|boolean',
            'selected_services' => 'nullable|array',
            'selected_services.*' => 'exists:hotel_services,id',
            'group_booking_id' => 'nullable|exists:group_bookings,id',
            'is_group_booking' => 'nullable|boolean',
            'billing_type' => 'nullable|in:individual,group_consolidated,group_split',
            'send_confirmation_email' => 'nullable|boolean',
            'status' => 'nullable|in:pending,confirmed,checked_in,checked_out,cancelled,no_show,modified',
            // New fields for multiple rooms
            'selected_rooms' => 'nullable|array',
            'selected_rooms.*.room_type_id' => 'nullable|exists:room_types,id',
            'selected_rooms.*.room_id' => 'nullable|exists:rooms,id',
        ]);

        // Check for overbooking
        $overbookingLimit = Setting::get('overbooking_limit', 10); // Default 10% overbooking allowed
        $thisOverbooking = $this->checkOverbooking($validated['check_in_date'], $validated['check_out_date'], $validated['room_type_id']);

        if ($thisOverbooking['is_overbooked'] && !$request->has('allow_overbooking')) {
            return back()->withErrors([
                'overbooking' => "Overbooking detected. {$thisOverbooking['message']}. Check 'Allow Overbooking' to proceed."
            ])->withInput();
        }

        // Calculate nights
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        // Get guest with guest type for discount calculation
        $guest = Guest::with('guestType')->find($validated['guest_id']);

        // Calculate pricing
        $roomRate = $validated['room_rate'];
        $totalRoomCharges = $roomRate * $nights;

        // Calculate guest type discount
        $guestTypeDiscountAmount = 0;
        $guestTypeDiscountReason = '';
        $autoApplyGuestTypeDiscount = Setting::get('auto_apply_guest_type_discount', true);

        if ($autoApplyGuestTypeDiscount && $guest && $guest->guestType && $guest->guestType->is_active) {
            $discountPercentage = $guest->guestType->discount_percentage;
            if ($discountPercentage > 0) {
                $guestTypeDiscountAmount = ($totalRoomCharges * $discountPercentage) / 100;
                $guestTypeDiscountReason = $guest->guestType->name . ' discount (' . $discountPercentage . '%)';
            }
        }

        // Calculate VIP discount if applicable
        $vipDiscountAmount = 0;
        $autoApplyVipDiscount = Setting::get('auto_apply_vip_discount', true);
        $vipDiscountPercentage = Setting::get('vip_discount_percentage', 0);

        if ($autoApplyVipDiscount && $guest && $guest->is_vip && $vipDiscountPercentage > 0) {
            $vipDiscountAmount = ($totalRoomCharges * $vipDiscountPercentage) / 100;
            if ($guestTypeDiscountReason) {
                $guestTypeDiscountReason .= ' + VIP discount (' . $vipDiscountPercentage . '%)';
            } else {
                $guestTypeDiscountReason = 'VIP discount (' . $vipDiscountPercentage . '%)';
            }
        }

        // Manual discount (if provided, it overrides or adds to automatic discounts based on settings)
        $manualDiscountAmount = $validated['discount_amount'] ?? 0;
        $discountCombinationMode = Setting::get('discount_combination_mode', 'add'); // 'add' or 'override'

        if ($discountCombinationMode === 'override' && $manualDiscountAmount > 0) {
            // Manual discount overrides automatic discounts
            $totalDiscountAmount = $manualDiscountAmount;
            $discountReason = $validated['discount_reason'] ?? 'Manual discount';
        } else {
            // Add all discounts together
            $totalDiscountAmount = $guestTypeDiscountAmount + $vipDiscountAmount + $manualDiscountAmount;
            $discountReason = $validated['discount_reason'] ?? $guestTypeDiscountReason;
        }

        // Calculate selected services total
        $selectedServicesTotal = 0;
        if ($request->has('selected_services') && is_array($request->selected_services)) {
            $selectedServiceIds = $request->selected_services;
            $numberOfGuests = $validated['number_of_adults'] + ($validated['number_of_children'] ?? 0);

            foreach ($selectedServiceIds as $serviceId) {
                $service = \App\Models\HotelService::find($serviceId);
                if ($service && $service->is_active && !$service->is_free) {
                    if ($service->pricing_type === 'per_night') {
                        $selectedServicesTotal += $service->price * $nights;
                    } elseif ($service->pricing_type === 'per_person') {
                        $selectedServicesTotal += $service->price * $numberOfGuests * $nights;
                    } else {
                        // per_service
                        $selectedServicesTotal += $service->price;
                    }
                }
            }
        }

        $taxes = ($totalRoomCharges - $totalDiscountAmount + $selectedServicesTotal) * (Setting::get('room_tax_rate', Setting::get('tax_rate', 0)) / 100);
        $serviceCharges = ($totalRoomCharges - $totalDiscountAmount) * (Setting::get('service_charge_rate', 0) / 100);
        $totalAmount = $totalRoomCharges - $totalDiscountAmount + $selectedServicesTotal + $taxes + $serviceCharges;

        // Update validated data with calculated discount
        $validated['discount_amount'] = $totalDiscountAmount;
        if (empty($validated['discount_reason'] ?? null) && $discountReason) {
            $validated['discount_reason'] = $discountReason;
        }

        // Generate unique reservation number
        $reservationNumber = 'RES' . strtoupper(Str::random(8));
        while (Reservation::where('reservation_number', $reservationNumber)->exists()) {
            $reservationNumber = 'RES' . strtoupper(Str::random(8));
        }

        $validated['reservation_number'] = $reservationNumber;
        $validated['nights'] = $nights;
        $validated['total_room_charges'] = $totalRoomCharges;
        $validated['taxes'] = $taxes;
        $validated['service_charges'] = $serviceCharges;
        $validated['total_amount'] = $totalAmount;
        $validated['balance_amount'] = $totalAmount;
        $validated['status'] = $request->input('status', 'pending');
        $validated['created_by'] = auth()->id();


        // Set check-in/check-out times and staff when status is checked_in or checked_out
        $status = $validated['status'];
        if ($status === 'checked_in') {
            $validated['actual_check_in'] = now();
            $validated['checked_in_by'] = auth()->id();
            $validated['police_report_status'] = 'new';
        } elseif ($status === 'checked_out') {
            $validated['actual_check_out'] = now();
            $validated['checked_out_by'] = auth()->id();
            // Also set check-in time if not already set
            if (empty($validated['actual_check_in'] ?? null)) {
                $validated['actual_check_in'] = Carbon::parse($validated['check_in_date'])->startOfDay();
            }
        }

        // Map number_of_adults/number_of_children to the DB columns adults/children
        $validated['adults'] = $validated['number_of_adults'] ?? 1;
        $validated['children'] = $validated['number_of_children'] ?? 0;

        $reservation = Reservation::create($validated);

        // Handle multiple rooms - attach rooms to reservation
        $selectedRooms = $request->input('selected_rooms', []);
        $numberOfRooms = $request->input('number_of_rooms', 1);

        if ($numberOfRooms > 1 && !empty($selectedRooms)) {
            // Multiple rooms selected - create reservation_room records
            foreach ($selectedRooms as $index => $roomData) {
                $roomId = $roomData['room_id'] ?? null;
                $roomTypeId = $roomData['room_type_id'] ?? $validated['room_type_id'];

                // Calculate room rate for this specific room
                $roomRate = $validated['room_rate'];
                if ($roomId) {
                    $room = Room::find($roomId);
                    if ($room && $room->roomType) {
                        $roomRate = $room->roomType->base_price;
                    }
                }

                $roomTotalCharges = $roomRate * $nights;

                // Create reservation_room pivot record
                \App\Models\ReservationRoom::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $roomId,
                    'is_primary' => ($index === 0), // First room is primary
                    'check_in_date' => $validated['check_in_date'],
                    'check_out_date' => $validated['check_out_date'],
                    'adults' => $validated['number_of_adults'],
                    'children' => $validated['number_of_children'] ?? 0,
                    'room_rate' => $roomRate,
                    'total_room_charges' => $roomTotalCharges,
                ]);

            }
        }

        $assignedRoomIds = collect([
            $validated['room_id'] ?? null,
            ...collect($selectedRooms)->pluck('room_id')->all(),
        ])->filter()->unique();

        foreach ($assignedRoomIds as $roomId) {
            $this->refreshRoomOccupancyStatus((int) $roomId);
        }

        // Attach selected hotel services to the reservation
        if ($request->has('selected_services') && is_array($request->selected_services)) {
            $selectedServiceIds = $request->selected_services;
            $numberOfGuests = $validated['number_of_adults'] + ($validated['number_of_children'] ?? 0);

            foreach ($selectedServiceIds as $serviceId) {
                $service = \App\Models\HotelService::find($serviceId);
                if ($service && $service->is_active) {
                    $unitPrice = (float) $service->price;
                    $quantity = 1;
                    $totalPrice = 0;

                    // Calculate price based on pricing type
                    if ($service->pricing_type === 'per_night') {
                        $quantity = $nights;
                        $totalPrice = $unitPrice * $nights;
                    } elseif ($service->pricing_type === 'per_person') {
                        $quantity = $numberOfGuests * $nights;
                        $totalPrice = $unitPrice * $numberOfGuests * $nights;
                    } else {
                        // per_service
                        $totalPrice = $unitPrice;
                    }

                    // Only attach if not free or if free but we want to track it
                    if (!$service->is_free || $totalPrice > 0) {
                        $reservation->hotelServices()->attach($serviceId, [
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'total_price' => $totalPrice,
                            'service_date' => $validated['check_in_date'],
                            'status' => 'pending',
                        ]);
                    }
                }
            }
        }

        // Reload reservation with relationships for redirect
        $reservation->load(['guest', 'room', 'roomType']);

        // Send confirmation email if requested
        $emailWarning = null;
        if ($request->input('send_confirmation_email') && $reservation->guest && $reservation->guest->email) {
            if (!$this->sendConfirmationEmail($reservation)) {
                $emailWarning = ' Reservation saved, but confirmation email could not be sent.';
            }
        }

        // Redirect based on route name (prioritize route over role)
        $routeName = request()->route()->getName() ?? '';

        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.reservations.show', $reservation->id)
                ->with('success', 'Reservation created successfully!' . ($emailWarning ?? ''));
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.reservations.show', $reservation->id)
                ->with('success', 'Reservation created successfully!' . ($emailWarning ?? ''));
        }

        return redirect()->route('admin.reservations.show', $reservation->id)
            ->with('success', 'Reservation created successfully!' . ($emailWarning ?? ''));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'roomType', 'groupBooking', 'createdBy', 'checkedInBy', 'checkedOutBy']);

        $folio = \App\Models\GuestFolio::with(['charges' => function ($query) {
            $query->whereIn('charge_code', ['SERVICE', 'POS'])
                ->where('is_voided', false)
                ->orderBy('charge_date', 'asc')
                ->orderBy('created_at', 'asc');
        }])->where('reservation_id', $reservation->id)->first();

        $chargeItems = $folio
            ? $folio->charges->map(fn ($charge) => [
                'id' => $charge->id,
                'charge_code' => $charge->charge_code,
                'charge_type' => $charge->charge_code === 'POS' ? 'POS / Restaurant' : 'Service',
                'description' => $charge->description,
                'quantity' => (int) $charge->quantity,
                'unit_price' => (float) $charge->unit_price,
                'total_amount' => (float) $charge->total_amount,
                'tax_amount' => (float) $charge->tax_amount,
                'net_amount' => (float) $charge->net_amount,
                'charge_date' => $charge->charge_date?->format('Y-m-d'),
                'department' => $charge->department,
            ])->values()->toArray()
            : [];

        $dynamicServiceCharges = $folio
            ? (float) $folio->charges->where('charge_code', 'SERVICE')->sum('net_amount')
            : (float) ($reservation->service_charges ?? 0);
        $dynamicPosCharges = $folio
            ? (float) $folio->charges->where('charge_code', 'POS')->sum('net_amount')
            : 0.0;
        $dynamicAdditionalRoomCharges = $dynamicServiceCharges + $dynamicPosCharges;
        $dynamicRoomCharges = $folio ? (float) ($folio->room_charges ?? 0) : (float) ($reservation->total_room_charges ?? 0);
        $dynamicTaxes = $folio ? (float) ($folio->tax_amount ?? 0) : (float) ($reservation->taxes ?? 0);
        $dynamicTotalAmount = $folio ? (float) ($folio->total_amount ?? 0) : (float) ($reservation->total_amount ?? 0);
        $dynamicPaidAmount = $folio ? (float) ($folio->paid_amount ?? 0) : (float) ($reservation->paid_amount ?? 0);
        $dynamicBalanceAmount = $folio ? (float) ($folio->balance_amount ?? 0) : (float) ($reservation->balance_amount ?? 0);

        // Determine which view to render based on route name (prioritize route over role)
        $routeName = request()->route()->getName() ?? '';
        $user = auth()->user();

        $viewPath = 'Admin/Reservations/Show';

        // Prioritize route name - if route starts with manager., use manager view
        if (str_starts_with($routeName, 'manager.')) {
            $viewPath = 'Manager/Reservations/Show';
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            $viewPath = 'FrontDesk/Reservations/Show';
        } elseif ($user->hasRole('manager') && !$user->hasRole('admin')) {
            // Fallback: if user is manager (but not admin), use manager view
            $viewPath = 'Manager/Reservations/Show';
        } elseif ($user->hasRole('front_desk') && !$user->hasRole('admin') && !$user->hasRole('manager')) {
            // Fallback: if user is front_desk only, use front-desk view
            $viewPath = 'FrontDesk/Reservations/Show';
        }

        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'guest' => $reservation->guest,
                'room' => $reservation->room,
                'room_type' => $reservation->roomType,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                'nights' => $reservation->nights,
                'number_of_adults' => $reservation->number_of_adults,
                'number_of_children' => $reservation->number_of_children,
                'infants' => $reservation->infants,
                'status' => $reservation->status,
                'booking_source' => $reservation->booking_source,
                'booking_reference' => $reservation->booking_reference,
                'room_rate' => $reservation->room_rate,
                'total_room_charges' => $dynamicRoomCharges,
                'taxes' => $dynamicTaxes,
                'service_charges' => $dynamicServiceCharges,
                'pos_charges' => $dynamicPosCharges,
                'additional_room_charges' => $dynamicAdditionalRoomCharges,
                'service_charge_items' => $chargeItems,
                'discount_amount' => $reservation->discount_amount,
                'discount_reason' => $reservation->discount_reason,
                'total_amount' => $dynamicTotalAmount,
                'paid_amount' => $dynamicPaidAmount,
                'balance_amount' => $dynamicBalanceAmount,
                'special_requests' => $reservation->special_requests,
                'room_preferences' => $reservation->room_preferences,
                'actual_check_in' => $reservation->actual_check_in?->format('Y-m-d H:i:s'),
                'actual_check_out' => $reservation->actual_check_out?->format('Y-m-d H:i:s'),
                'checked_in_by' => $reservation->checkedInBy ? [
                    'id' => $reservation->checkedInBy->id,
                    'name' => trim(($reservation->checkedInBy->first_name ?? '') . ' ' . ($reservation->checkedInBy->last_name ?? '')),
                    'first_name' => $reservation->checkedInBy->first_name,
                    'last_name' => $reservation->checkedInBy->last_name,
                    'email' => $reservation->checkedInBy->email,
                ] : null,
                'checked_out_by' => $reservation->checkedOutBy ? [
                    'id' => $reservation->checkedOutBy->id,
                    'name' => trim(($reservation->checkedOutBy->first_name ?? '') . ' ' . ($reservation->checkedOutBy->last_name ?? '')),
                    'first_name' => $reservation->checkedOutBy->first_name,
                    'last_name' => $reservation->checkedOutBy->last_name,
                    'email' => $reservation->checkedOutBy->email,
                ] : null,
                'group_booking' => $reservation->groupBooking,
                'is_group_booking' => $reservation->is_group_booking,
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function edit(Reservation $reservation)
    {
        $guests = Guest::with('guestType')->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'phone', 'guest_type_id', 'is_vip'])
            ->map(function($guest) {
                return [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'guest_type_id' => $guest->guest_type_id,
                    'is_vip' => $guest->is_vip,
                    'guest_type' => $guest->guestType ? [
                        'id' => $guest->guestType->id,
                        'name' => $guest->guestType->name,
                        'code' => $guest->guestType->code,
                        'discount_percentage' => $guest->guestType->discount_percentage,
                        'color' => $guest->guestType->color,
                    ] : null,
                ];
            });
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'base_price']);
        $rooms = Room::with('roomType')->get(['id', 'room_number', 'room_type_id', 'status']);
        $groupBookings = GroupBooking::where('status', '!=', 'cancelled')->get(['id', 'group_number', 'group_name']);

        // Determine which view to render based on route name (prioritize route over role)
        $routeName = request()->route()->getName() ?? '';
        $user = auth()->user();

        $viewPath = 'Admin/Reservations/Edit';

        // Prioritize route name - if route starts with manager., use manager view
        if (str_starts_with($routeName, 'manager.')) {
            $viewPath = 'Manager/Reservations/Edit';
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            $viewPath = 'FrontDesk/Reservations/Edit';
        } elseif ($user->hasRole('manager') && !$user->hasRole('admin')) {
            // Fallback: if user is manager (but not admin), use manager view
            $viewPath = 'Manager/Reservations/Edit';
        } elseif ($user->hasRole('front_desk') && !$user->hasRole('admin') && !$user->hasRole('manager')) {
            // Fallback: if user is front_desk only, use front-desk view
            $viewPath = 'FrontDesk/Reservations/Edit';
        }

        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id,
                'room_type_id' => $reservation->room_type_id,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                'number_of_adults' => $reservation->number_of_adults,
                'number_of_children' => $reservation->number_of_children,
                'infants' => $reservation->infants,
                'booking_source' => $reservation->booking_source,
                'booking_reference' => $reservation->booking_reference,
                'room_rate' => $reservation->room_rate,
                'discount_amount' => $reservation->discount_amount,
                'discount_reason' => $reservation->discount_reason,
                'special_requests' => $reservation->special_requests,
                'room_preferences' => $reservation->room_preferences,
                'status' => $reservation->status,
                'group_booking_id' => $reservation->group_booking_id,
                'is_group_booking' => $reservation->is_group_booking,
                'paid_amount' => $reservation->paid_amount,
                'total_amount' => $reservation->total_amount,
                'balance_amount' => $reservation->balance_amount,
            ],
            'guests' => $guests,
            'roomTypes' => $roomTypes,
            'rooms' => $rooms,
            'groupBookings' => $groupBookings,
            'bookingSources' => [
                'walk_in' => 'Walk-in',
                'phone' => 'Phone',
                'email' => 'Email',
                'website' => 'Website',
                'booking_com' => 'Booking.com',
                'expedia' => 'Expedia',
                'agoda' => 'Agoda',
                'travel_agent' => 'Travel Agent',
                'corporate' => 'Corporate',
            ],
            'pricingSettings' => [
                'auto_apply_guest_type_discount' => (bool) Setting::get('auto_apply_guest_type_discount', true),
                'auto_apply_vip_discount' => (bool) Setting::get('auto_apply_vip_discount', true),
                'vip_discount_percentage' => (float) Setting::get('vip_discount_percentage', 0),
                'discount_combination_mode' => Setting::get('discount_combination_mode', 'add'),
                'tax_rate' => (float) Setting::get('room_tax_rate', Setting::get('tax_rate', 0)),
                'service_charge_rate' => (float) Setting::get('service_charge_rate', 0),
            ],
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $originalRoomId = $reservation->room_id;
        $openFolio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_id' => 'nullable|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'extend_days' => 'nullable|integer|min:0|max:365',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'booking_source' => 'required|in:walk_in,phone,email,website,booking_com,expedia,agoda,travel_agent,corporate',
            'booking_reference' => 'nullable|string|max:255',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string',
            'room_preferences' => 'nullable|array',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled,no_show,modified',
            'group_booking_id' => 'nullable|exists:group_bookings,id',
            'is_group_booking' => 'nullable|boolean',
        ]);

        $extendDays = (int) ($validated['extend_days'] ?? 0);
        if ($extendDays > 0) {
            $validated['check_out_date'] = Carbon::parse($validated['check_out_date'])
                ->addDays($extendDays)
                ->toDateString();
        }

        unset($validated['extend_days']);

        // Recalculate if dates or rates changed
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        // Get guest with guest type for discount calculation
        $guest = Guest::with('guestType')->find($validated['guest_id']);

        $roomRate = $validated['room_rate'];
        $totalRoomCharges = $roomRate * $nights;

        // Calculate guest type discount
        $guestTypeDiscountAmount = 0;
        $guestTypeDiscountReason = '';
        $autoApplyGuestTypeDiscount = Setting::get('auto_apply_guest_type_discount', true);

        if ($autoApplyGuestTypeDiscount && $guest && $guest->guestType && $guest->guestType->is_active) {
            $discountPercentage = $guest->guestType->discount_percentage;
            if ($discountPercentage > 0) {
                $guestTypeDiscountAmount = ($totalRoomCharges * $discountPercentage) / 100;
                $guestTypeDiscountReason = $guest->guestType->name . ' discount (' . $discountPercentage . '%)';
            }
        }

        // Calculate VIP discount if applicable
        $vipDiscountAmount = 0;
        $autoApplyVipDiscount = Setting::get('auto_apply_vip_discount', true);
        $vipDiscountPercentage = Setting::get('vip_discount_percentage', 0);

        if ($autoApplyVipDiscount && $guest && $guest->is_vip && $vipDiscountPercentage > 0) {
            $vipDiscountAmount = ($totalRoomCharges * $vipDiscountPercentage) / 100;
            if ($guestTypeDiscountReason) {
                $guestTypeDiscountReason .= ' + VIP discount (' . $vipDiscountPercentage . '%)';
            } else {
                $guestTypeDiscountReason = 'VIP discount (' . $vipDiscountPercentage . '%)';
            }
        }

        // Manual discount (if provided, it overrides or adds to automatic discounts based on settings)
        $manualDiscountAmount = $validated['discount_amount'] ?? 0;
        $discountCombinationMode = Setting::get('discount_combination_mode', 'add'); // 'add' or 'override'

        if ($discountCombinationMode === 'override' && $manualDiscountAmount > 0) {
            // Manual discount overrides automatic discounts
            $totalDiscountAmount = $manualDiscountAmount;
            $discountReason = $validated['discount_reason'] ?? 'Manual discount';
        } else {
            // Add all discounts together
            $totalDiscountAmount = $guestTypeDiscountAmount + $vipDiscountAmount + $manualDiscountAmount;
            $discountReason = $validated['discount_reason'] ?? $guestTypeDiscountReason;
        }

        $taxes = ($totalRoomCharges - $totalDiscountAmount) * (Setting::get('room_tax_rate', Setting::get('tax_rate', 0)) / 100);
        $serviceCharges = ($totalRoomCharges - $totalDiscountAmount) * (Setting::get('service_charge_rate', 0) / 100);
        $totalAmount = $totalRoomCharges - $totalDiscountAmount + $taxes + $serviceCharges;

        // Update validated data with calculated discount
        $validated['discount_amount'] = $totalDiscountAmount;
        if (!$validated['discount_reason'] && $discountReason) {
            $validated['discount_reason'] = $discountReason;
        }

        $currentPaidAmount = (float) ($openFolio?->paid_amount ?? $reservation->paid_amount ?? 0);

        $validated['nights'] = $nights;
        $validated['total_room_charges'] = $totalRoomCharges;
        $validated['taxes'] = $taxes;
        $validated['service_charges'] = $serviceCharges;
        $validated['total_amount'] = $totalAmount;
        $validated['paid_amount'] = $currentPaidAmount;
        $validated['balance_amount'] = max(0, $totalAmount - $currentPaidAmount);
        $validated['updated_by'] = auth()->id();

        if (($validated['status'] ?? null) === 'cancelled') {
            $validated['total_room_charges'] = 0;
            $validated['taxes'] = 0;
            $validated['service_charges'] = 0;
            $validated['discount_amount'] = 0;
            $validated['total_amount'] = 0;
            $validated['paid_amount'] = 0;
            $validated['balance_amount'] = 0;
            $validated['cancellation_charges'] = 0;
            $validated['cancelled_at'] = now();
            $validated['cancelled_by'] = auth()->id();
        }

        // Sync adults/children DB columns with number_of_adults/number_of_children
        $validated['adults'] = $validated['number_of_adults'] ?? 1;
        $validated['children'] = $validated['number_of_children'] ?? 0;

        // Police report status lifecycle:
        // - When a reservation transitions to checked_in for the first time, set 'new'
        // - When a checked-in reservation that was already 'sent' to police has its
        //   key stay-details changed, flip back to 'modified' so staff know to re-report
        if (($validated['status'] ?? null) === 'checked_in' && $reservation->status !== 'checked_in') {
            $validated['police_report_status'] = 'new';
        } elseif (
            $reservation->status === 'checked_in' &&
            $reservation->police_report_status === 'sent'
        ) {
            $stayFieldsChanged =
                ($validated['room_id'] ?? null) != $reservation->room_id ||
                ($validated['check_in_date'] ?? null) != $reservation->check_in_date?->toDateString() ||
                ($validated['check_out_date'] ?? null) != $reservation->check_out_date?->toDateString();

            if ($stayFieldsChanged) {
                $validated['police_report_status'] = 'modified';
            }
        }

        $reservation->update($validated);

        if (($validated['status'] ?? null) !== 'cancelled') {
            $this->syncOpenFolioFromReservation($reservation, $openFolio);
        }

        $this->refreshRoomOccupancyStatus($originalRoomId);
        $this->refreshRoomOccupancyStatus($reservation->room_id);

        // Determine redirect route based on the route name used
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.reservations.show', $reservation->id)
                ->with('success', 'Reservation updated successfully!');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.reservations.show', $reservation->id)
                ->with('success', 'Reservation updated successfully!');
        }

        return redirect()->route('admin.reservations.show', $reservation->id)
            ->with('success', 'Reservation updated successfully!');
    }

    private function syncOpenFolioFromReservation(Reservation $reservation, ?GuestFolio $folio = null): void
    {
        $folio = $folio ?: GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        if (!$folio) {
            return;
        }

        $serviceChargeTotal = (float) FolioCharge::where('guest_folio_id', $folio->id)
            ->where('charge_code', 'SERVICE')
            ->where('is_voided', false)
            ->sum('net_amount');

        $posChargeTotal = (float) FolioCharge::where('guest_folio_id', $folio->id)
            ->where('charge_code', 'POS')
            ->where('is_voided', false)
            ->sum('net_amount');

        $baseReservationTotal = (float) ($reservation->total_amount ?? 0);
        $folioPaidAmount = (float) ($folio->paid_amount ?? $reservation->paid_amount ?? 0);
        $folioTotalAmount = $baseReservationTotal + $serviceChargeTotal + $posChargeTotal;
        $folioBalanceAmount = max(0, $folioTotalAmount - $folioPaidAmount);

        $folio->update([
            'guest_id' => $reservation->guest_id,
            'room_id' => $reservation->room_id,
            'room_charges' => (float) ($reservation->total_room_charges ?? 0),
            'service_charges' => (float) ($reservation->service_charges ?? 0),
            'tax_amount' => (float) ($reservation->taxes ?? 0),
            'discount_amount' => (float) ($reservation->discount_amount ?? 0),
            'total_amount' => $folioTotalAmount,
            'paid_amount' => $folioPaidAmount,
            'balance_amount' => $folioBalanceAmount,
            'payment_status' => $folioBalanceAmount <= 0 ? 'paid' : ($folioPaidAmount > 0 ? 'partial' : 'pending'),
        ]);

        $roomCharge = FolioCharge::where('guest_folio_id', $folio->id)
            ->where('charge_code', 'ROOM')
            ->orderBy('id')
            ->first();

        if ($roomCharge) {
            $roomCharge->update([
                'description' => 'Room charges - ' . max(1, (int) ($reservation->nights ?? 1)) . ' night(s)',
                'quantity' => max(1, (int) ($reservation->nights ?? 1)),
                'unit_price' => (float) ($reservation->room_rate ?? 0),
                'total_amount' => (float) ($reservation->total_room_charges ?? 0),
                'tax_amount' => (float) ($reservation->taxes ?? 0),
                'discount_amount' => (float) ($reservation->discount_amount ?? 0),
                'net_amount' => $baseReservationTotal,
                'reference_id' => $reservation->id,
            ]);
        }
    }

    public function confirm(Reservation $reservation)
    {
        $reservation->update([
            'status' => 'confirmed',
            'updated_by' => auth()->id(),
        ]);

        $emailSent = true;
        // Send confirmation email
        if ($reservation->guest && $reservation->guest->email) {
            $emailSent = $this->sendConfirmationEmail($reservation);
        }

        // Redirect based on route name (prioritize route over role)
        $routeName = request()->route()->getName() ?? '';

        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.reservations.show', $reservation->id)
                ->with($emailSent ? 'success' : 'error', $emailSent
                    ? 'Reservation confirmed and confirmation email sent!'
                    : 'Reservation confirmed, but confirmation email could not be sent.');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.reservations.show', $reservation->id)
                ->with($emailSent ? 'success' : 'error', $emailSent
                    ? 'Reservation confirmed and confirmation email sent!'
                    : 'Reservation confirmed, but confirmation email could not be sent.');
        }

        return redirect()->route('admin.reservations.show', $reservation->id)
            ->with($emailSent ? 'success' : 'error', $emailSent
                ? 'Reservation confirmed and confirmation email sent!'
                : 'Reservation confirmed, but confirmation email could not be sent.');
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancellation_charges' => 0,
            'total_room_charges' => 0,
            'taxes' => 0,
            'service_charges' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 0,
            'updated_by' => auth()->id(),
        ]);

        $this->refreshRoomOccupancyStatus($reservation->room_id);

        return redirect()->back()->with('success', 'Reservation cancelled successfully!');
    }

    public function sendConfirmation(Reservation $reservation)
    {
        if (!$reservation->guest || !$reservation->guest->email) {
            // Redirect based on route name (prioritize route over role)
            $routeName = request()->route()->getName() ?? '';

            if (str_starts_with($routeName, 'manager.')) {
                return redirect()->route('manager.reservations.show', $reservation->id)
                    ->with('error', 'Guest email not available.');
            } elseif (str_starts_with($routeName, 'front-desk.')) {
                return redirect()->route('front-desk.reservations.show', $reservation->id)
                    ->with('error', 'Guest email not available.');
            }

            return redirect()->route('admin.reservations.show', $reservation->id)
                ->with('error', 'Guest email not available.');
        }

        if (!$this->sendConfirmationEmail($reservation)) {
            $routeName = request()->route()->getName() ?? '';

            if (str_starts_with($routeName, 'manager.')) {
                return redirect()->route('manager.reservations.show', $reservation->id)
                    ->with('error', 'Failed to send confirmation email. Please verify SMTP settings and logs.');
            } elseif (str_starts_with($routeName, 'front-desk.')) {
                return redirect()->route('front-desk.reservations.show', $reservation->id)
                    ->with('error', 'Failed to send confirmation email. Please verify SMTP settings and logs.');
            }

            return redirect()->route('admin.reservations.show', $reservation->id)
                ->with('error', 'Failed to send confirmation email. Please verify SMTP settings and logs.');
        }

        // Redirect based on route name (prioritize route over role)
        $routeName = request()->route()->getName() ?? '';

        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.reservations.show', $reservation->id)
                ->with('success', 'Confirmation email sent successfully!');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.reservations.show', $reservation->id)
                ->with('success', 'Confirmation email sent successfully!');
        }

        return redirect()->route('admin.reservations.show', $reservation->id)
            ->with('success', 'Confirmation email sent successfully!');
    }

    private function sendConfirmationEmail(Reservation $reservation): bool
    {
        try {
            $reservation->load(['guest', 'room', 'roomType']);

            Mail::send('emails.reservation-confirmation', [
                'reservation' => $reservation,
                'hotel' => [
                    'name' => Setting::get('hotel_name', 'Hotel'),
                    'address' => Setting::get('hotel_address', ''),
                    'phone' => Setting::get('hotel_phone', ''),
                    'email' => Setting::get('hotel_email', ''),
                ],
            ], function ($message) use ($reservation) {
                $message->to($reservation->guest->email, $reservation->guest->full_name)
                    ->subject('Reservation Confirmation - ' . $reservation->reservation_number);
            });

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send confirmation email: ' . $e->getMessage());
            return false;
        }
    }

    private function refreshRoomOccupancyStatus(?int $roomId): void
    {
        if (!$roomId) {
            return;
        }

        $room = Room::find($roomId);
        if (!$room) {
            return;
        }

        $activeCheckedInExists = Reservation::where('room_id', $roomId)
            ->where('status', 'checked_in')
            ->whereNull('actual_check_out')
            ->exists();

        if ($activeCheckedInExists) {
            $room->update(['status' => 'occupied']);
            return;
        }

        $today = Carbon::today()->toDateString();

        $activeReservedExists = Reservation::where('room_id', $roomId)
            ->whereIn('status', ['confirmed', 'pending', 'modified'])
            ->whereDate('check_in_date', $today)
            ->whereNull('actual_check_in')
            ->exists();

        $room->update([
            'status' => $activeReservedExists ? 'reserved' : 'available',
        ]);
    }

    private function checkOverbooking($checkIn, $checkOut, $roomTypeId)
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // Get total rooms of this type
        $totalRooms = Room::where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->count();

        if ($totalRooms == 0) {
            return ['is_overbooked' => true, 'message' => 'No rooms of this type available'];
        }

        // Count existing reservations for this period
        $existingReservations = Reservation::where('room_type_id', $roomTypeId)
            ->whereIn('status', ['confirmed', 'checked_in', 'pending'])
            ->where(function($query) use ($checkInDate, $checkOutDate) {
                $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                    ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                    ->orWhere(function($q) use ($checkInDate, $checkOutDate) {
                        $q->where('check_in_date', '<=', $checkInDate)
                          ->where('check_out_date', '>=', $checkOutDate);
                    });
            })
            ->count();

        $overbookingLimit = Setting::get('overbooking_limit', 10);
        $maxAllowed = $totalRooms + ($totalRooms * $overbookingLimit / 100);

        if ($existingReservations >= $maxAllowed) {
            return [
                'is_overbooked' => true,
                'message' => "Maximum bookings ({$maxAllowed}) reached for this period. {$existingReservations} existing reservations."
            ];
        }

        return ['is_overbooked' => false, 'message' => ''];
    }
}
