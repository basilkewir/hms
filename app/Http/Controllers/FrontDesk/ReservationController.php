<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Guest;
use App\Models\HotelService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['guest', 'room', 'roomType'])
            ->orderBy('check_in_date', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'confirmation_number' => $reservation->reservation_number,
                    'guest_name' => $reservation->guest->full_name ?? 'N/A',
                    'guest_email' => $reservation->guest->email ?? 'N/A',
                    'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                    'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                    'room_number' => $reservation->room->room_number ?? null,
                    'room_type' => $reservation->roomType->name ?? 'N/A',
                    'adults' => $reservation->adults,
                    'children' => $reservation->children,
                    'status' => $reservation->status,
                ];
            });

        $stats = [
            'arrivals' => Reservation::whereDate('check_in_date', today())
                ->whereIn('status', ['confirmed', 'pending'])->count(),
            'departures' => Reservation::whereDate('check_out_date', today())
                ->where('status', 'checked_in')->count(),
            'pendingCheckins' => Reservation::whereDate('check_in_date', '<=', today())
                ->where('status', 'confirmed')->count(),
            'occupiedRooms' => Reservation::where('status', 'checked_in')->count(),
        ];

        return Inertia::render('FrontDesk/Reservations/Index', [
            'user' => auth()->user()->load('roles'),
            'reservations' => $reservations,
            'reservationStats' => $stats,
        ]);
    }

    public function arrivals()
    {
        $arrivals = Reservation::with(['guest', 'roomType', 'room'])
            ->whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'pending'])
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'confirmation_number' => $reservation->reservation_number,
                    'guest_name' => $reservation->guest->full_name ?? 'N/A',
                    'room_type' => $reservation->roomType->name ?? 'N/A',
                    'room_number' => $reservation->room->room_number ?? null,
                    'nights' => $reservation->nights,
                    'adults' => $reservation->adults ?? $reservation->number_of_adults ?? 1,
                    'children' => $reservation->children ?? $reservation->number_of_children ?? 0,
                    'status' => $reservation->status,
                    'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                    'check_in_time' => $reservation->preferred_check_in_time ?? null,
                ];
            });

        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');

        $viewPath = 'FrontDesk/Reservations/Arrivals';
        if ($isManager) {
            $viewPath = 'Manager/Reservations/Arrivals';
        }

        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'arrivals' => $arrivals,
        ]);
    }

    public function departures()
    {
        $departures = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_out_date', today())
            ->where('status', 'checked_in')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'room_number' => $reservation->room->room_number ?? 'N/A',
                    'guest_name' => $reservation->guest->full_name ?? 'N/A',
                    'room_type' => $reservation->roomType->name ?? 'N/A',
                    'expected_departure' => $reservation->check_out_date->format('Y-m-d') . 'T12:00:00',
                ];
            });

        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');

        $viewPath = 'FrontDesk/Reservations/Departures';
        if ($isManager) {
            $viewPath = 'Manager/Reservations/Departures';
        }

        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'departures' => $departures,
        ]);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'roomType', 'createdBy', 'checkedInBy', 'checkedOutBy']);

        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');

        $viewPath = 'FrontDesk/Reservations/Show';
        if ($isManager) {
            $viewPath = 'Manager/Reservations/Show';
        }

        // Load folio and its individual SERVICE charges dynamically
        $folio = \App\Models\GuestFolio::with(['charges' => function ($q) {
            $q->where('charge_code', 'SERVICE')
              ->where('is_voided', false)
              ->orderBy('charge_date', 'asc')
              ->orderBy('created_at', 'asc');
        }])->where('reservation_id', $reservation->id)->first();

        $serviceChargeItems = $folio
            ? $folio->charges->map(fn($c) => [
                'id'          => $c->id,
                'description' => $c->description,
                'quantity'    => (int) $c->quantity,
                'unit_price'  => (float) $c->unit_price,
                'total_amount'=> (float) $c->total_amount,
                'tax_amount'  => (float) $c->tax_amount,
                'net_amount'  => (float) $c->net_amount,
                'charge_date' => $c->charge_date?->format('Y-m-d'),
                'department'  => $c->department,
              ])->values()->toArray()
            : [];

        // Use folio's service_charges total if available; fallback to reservation field
        $dynamicServiceCharges = $folio
            ? (float) ($folio->service_charges ?? 0)
            : (float) ($reservation->service_charges ?? 0);

        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'status' => $reservation->status,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                'nights' => $reservation->nights,
                'number_of_adults' => $reservation->number_of_adults ?? $reservation->adults,
                'number_of_children' => $reservation->number_of_children ?? $reservation->children,
                'adults' => $reservation->adults,
                'children' => $reservation->children,
                'room_rate' => $reservation->room_rate,
                'total_room_charges' => $reservation->total_room_charges,
                'taxes' => $reservation->taxes,
                'service_charges' => $dynamicServiceCharges,
                'service_charge_items' => $serviceChargeItems,
                'discount_amount' => $reservation->discount_amount,
                'discount_reason' => $reservation->discount_reason,
                'booking_source' => $reservation->booking_source,
                'booking_reference' => $reservation->booking_reference,
                'total_amount' => $reservation->total_amount,
                'paid_amount' => $reservation->paid_amount,
                'balance_amount' => $reservation->balance_amount,
                'special_requests' => $reservation->special_requests,
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
                'created_at' => $reservation->created_at->format('Y-m-d H:i'),
                'guest' => [
                    'full_name' => $reservation->guest->full_name,
                    'email' => $reservation->guest->email,
                    'phone' => $reservation->guest->phone,
                    'address' => $reservation->guest->address,
                    'city' => $reservation->guest->city,
                    'country' => $reservation->guest->country,
                    'nationality' => $reservation->guest->nationality,
                    'id_type' => $reservation->guest->id_type,
                    'id_number' => $reservation->guest->id_number,
                    'purpose_of_visit' => $reservation->guest->purpose_of_visit,
                ],
                'room_type' => [
                    'name' => $reservation->roomType->name,
                    'code' => $reservation->roomType->code,
                ],
                'room' => $reservation->room ? [
                    'room_number' => $reservation->room->room_number,
                    'floor' => $reservation->room->floor,
                ] : null,
                'created_by' => $reservation->createdBy->full_name ?? 'System',
            ],
        ]);
    }

    public function create()
    {
        // Get guests with their types
        $guests = Guest::with('guestType')
            ->orderBy('first_name')
            ->get()
            ->map(function ($guest) {
                return [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'guest_type' => $guest->guestType ? [
                        'id' => $guest->guestType->id,
                        'name' => $guest->guestType->name,
                        'color' => $guest->guestType->color,
                        'discount_percentage' => $guest->guestType->discount_percentage,
                    ] : null,
                    'is_vip' => $guest->is_vip,
                ];
            });

        // Get room types
        $roomTypes = RoomType::where('is_active', true)
            ->get()
            ->map(function ($roomType) {
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'code' => $roomType->code,
                    'price' => $roomType->base_price,
                    'capacity' => $roomType->capacity,
                ];
            });

        // Get available rooms with full details
        $availableRooms = Room::with('roomType')
            ->whereIn('status', ['available', 'reserved'])
            ->where('housekeeping_status', 'clean')
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type_id' => $room->room_type_id,
                    'room_type' => $room->roomType ? $room->roomType->name : null,
                    'status' => $room->status,
                ];
            });

        // Get group bookings (empty for now, but structure ready)
        $groupBookings = collect([]);

        // Define booking sources
        $bookingSources = [
            'walk_in' => 'Walk-in',
            'phone' => 'Phone',
            'email' => 'Email',
            'website' => 'Website',
            'travel_agent' => 'Travel Agent',
            'online_agency' => 'Online Agency',
            'corporate' => 'Corporate',
            'group' => 'Group',
            'other' => 'Other',
        ];

        return Inertia::render('FrontDesk/Reservations/Create', [
            'user' => auth()->user()->load('roles'),
            'guests' => $guests,
            'roomTypes' => $roomTypes,
            'availableRooms' => $availableRooms,
            'groupBookings' => $groupBookings,
            'bookingSources' => $bookingSources,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_id' => 'nullable|exists:rooms,id',
            'number_of_rooms' => 'required|integer|min:1|max:10',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'booking_source' => 'required|string',
            'booking_reference' => 'nullable|string',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'room_preferences' => 'nullable|array',
            'early_check_in_requested' => 'nullable|boolean',
            'late_check_out_requested' => 'nullable|boolean',
            'preferred_check_in_time' => 'nullable|string',
            'preferred_check_out_time' => 'nullable|string',
            'breakfast_included' => 'nullable|boolean',
            'wifi_included' => 'nullable|boolean',
            'parking_required' => 'nullable|boolean',
            'airport_pickup' => 'nullable|boolean',
            'airport_drop' => 'nullable|boolean',
            'group_booking_id' => 'nullable|integer',
            'is_group_booking' => 'nullable|boolean',
            'billing_type' => 'nullable|string',
            'status' => 'required|string|in:pending,confirmed',
            'send_confirmation_email' => 'nullable|boolean',
            // New fields for multiple rooms
            'selected_rooms' => 'nullable|array',
            'selected_rooms.*.room_type_id' => 'nullable|exists:room_types,id',
            'selected_rooms.*.room_id' => 'nullable|exists:rooms,id',
        ]);

        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        // Get the guest
        $guest = Guest::find($validated['guest_id']);
        if (!$guest) {
            return back()->with('error', 'Guest not found.');
        }

        // Get room type
        $roomType = RoomType::find($validated['room_type_id']);
        if (!$roomType) {
            return back()->with('error', 'Room type not found.');
        }

        // Calculate pricing
        $roomCharges = $validated['room_rate'] * $nights;
        $totalDiscount = $validated['discount_amount'] ?? 0;
        $totalAmount = $roomCharges - $totalDiscount;

        // Create reservation
        $reservation = Reservation::create([
            'reservation_number' => 'RES' . strtoupper(uniqid()),
            'guest_id' => $guest->id,
            'room_type_id' => $validated['room_type_id'],
            'room_id' => $validated['room_id'] ?? null,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'nights' => $nights,
            'adults' => $validated['number_of_adults'],
            'children' => $validated['number_of_children'] ?? 0,
            'infants' => $validated['infants'] ?? 0,
            'status' => $validated['status'],
            'booking_source' => $validated['booking_source'],
            'booking_reference' => $validated['booking_reference'],
            'room_rate' => $validated['room_rate'],
            'discount_amount' => $totalDiscount,
            'discount_reason' => $validated['discount_reason'],
            'total_room_charges' => $roomCharges,
            'total_amount' => $totalAmount,
            'balance_amount' => $totalAmount,
            'special_requests' => $validated['special_requests'],
            'room_preferences' => $validated['room_preferences'] ?? [],
            'early_check_in_requested' => $validated['early_check_in_requested'] ?? false,
            'late_check_out_requested' => $validated['late_check_out_requested'] ?? false,
            'preferred_check_in_time' => $validated['preferred_check_in_time'],
            'preferred_check_out_time' => $validated['preferred_check_out_time'],
            'breakfast_included' => $validated['breakfast_included'] ?? false,
            'wifi_included' => $validated['wifi_included'] ?? false,
            'parking_required' => $validated['parking_required'] ?? false,
            'airport_pickup' => $validated['airport_pickup'] ?? false,
            'airport_drop' => $validated['airport_drop'] ?? false,
            'group_booking_id' => $validated['group_booking_id'],
            'is_group_booking' => $validated['is_group_booking'] ?? false,
            'billing_type' => $validated['billing_type'] ?? 'individual',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

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
                    $room = \App\Models\Room::find($roomId);
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

                // Mark the room as reserved if selected
                if ($roomId) {
                    \App\Models\Room::where('id', $roomId)->update(['status' => 'reserved']);
                }
            }
        }

        // Send confirmation email if requested
        $emailWarning = null;
        if ($validated['send_confirmation_email'] ?? false) {
            if (!$this->sendConfirmationEmail($reservation)) {
                $emailWarning = ' Reservation saved, but confirmation email could not be sent.';
            }
        }

        return redirect()->route('front-desk.reservations.index')
            ->with('success', 'Reservation created successfully!' . ($emailWarning ?? ''));
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
            Log::error('Failed to send confirmation email from front desk flow', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
