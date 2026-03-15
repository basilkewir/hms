<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\RoomType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChannelManagerController extends Controller
{
    public function index()
    {
        // Define OTA sources - support both 'source' and 'booking_source' columns
        $otaSources = ['booking_com', 'expedia', 'agoda', 'airbnb', 'tripadvisor', 'travel_agent', 'corporate'];

        $reservations = Reservation::with(['guest', 'room', 'roomType'])
            ->whereIn('booking_source', $otaSources)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function($reservation) {
                $source = $reservation->booking_source;
                return [
                    'id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'booking_reference' => $reservation->booking_reference ?? $reservation->ota_confirmation_number,
                    'booking_source' => $source,
                    'guest_name' => $reservation->guest ? ($reservation->guest->first_name . ' ' . $reservation->guest->last_name) : 'N/A',
                    'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                    'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                    'room_type' => $reservation->roomType?->name,
                    'room_number' => $reservation->room?->room_number,
                    'total_amount' => $reservation->total_amount,
                    'status' => $reservation->status,
                    'commission_rate' => $this->getCommissionRate($source),
                    'commission_amount' => $this->calculateCommission($reservation),
                ];
            });

        $stats = [
            'total' => Reservation::whereIn('booking_source', $otaSources)->count(),
            'booking_com' => Reservation::where('booking_source', 'booking_com')->count(),
            'expedia' => Reservation::where('booking_source', 'expedia')->count(),
            'agoda' => Reservation::where('booking_source', 'agoda')->count(),
            'airbnb' => Reservation::where('booking_source', 'airbnb')->count(),
            'tripadvisor' => Reservation::where('booking_source', 'tripadvisor')->count(),
            'travel_agent' => Reservation::where('booking_source', 'travel_agent')->count(),
            'corporate' => Reservation::where('booking_source', 'corporate')->count(),
        ];

        return Inertia::render('Admin/ChannelManager/Index', [
            'user' => auth()->user()->load('roles'),
            'reservations' => $reservations,
            'stats' => $stats,
            'commission_rates' => $this->getCommissionRates(),
        ]);
    }

    public function create()
    {
        $guests = Guest::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'phone']);
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'base_price']);

        return Inertia::render('Admin/ChannelManager/Create', [
            'user' => auth()->user()->load('roles'),
            'guests' => $guests,
            'roomTypes' => $roomTypes,
            'bookingSources' => [
                'booking_com' => 'Booking.com',
                'expedia' => 'Expedia',
                'agoda' => 'Agoda',
                'airbnb' => 'Airbnb',
                'tripadvisor' => 'TripAdvisor',
                'travel_agent' => 'Travel Agent',
                'corporate' => 'Corporate',
            ],
            'commission_rates' => $this->getCommissionRates(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'booking_source' => 'required|in:booking_com,expedia,agoda,airbnb,tripadvisor,travel_agent,corporate',
            'booking_reference' => 'required|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'special_requests' => 'nullable|string',
        ]);

        // Check for overbooking
        $overbookingLimit = Setting::get('overbooking_limit', 10);
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

        // Calculate pricing
        $roomRate = $validated['room_rate'];
        $totalRoomCharges = $roomRate * $nights;

        // Apply manual discount if provided
        $discountAmount = $validated['discount_amount'] ?? 0;
        $taxRate = Setting::get('room_tax_rate', Setting::get('tax_rate', 0)) / 100;
        $serviceChargeRate = Setting::get('service_charge_rate', 0) / 100;

        $taxes = ($totalRoomCharges - $discountAmount) * $taxRate;
        $serviceCharges = ($totalRoomCharges - $discountAmount) * $serviceChargeRate;
        $totalAmount = $totalRoomCharges - $discountAmount + $taxes + $serviceCharges;

        // Calculate commission
        $commissionRate = $validated['commission_rate'] ?? $this->getCommissionRate($validated['booking_source']);
        $commissionAmount = $validated['commission_amount'] ?? ($totalRoomCharges * ($commissionRate / 100));

        // Generate unique reservation number
        $reservationNumber = 'OTA-' . strtoupper(Str::random(8));
        while (Reservation::where('reservation_number', $reservationNumber)->exists()) {
            $reservationNumber = 'OTA-' . strtoupper(Str::random(8));
        }

        $validated['reservation_number'] = $reservationNumber;
        $validated['nights'] = $nights;
        $validated['service_charges'] = $serviceCharges;
        $validated['total_amount'] = $totalAmount;
        $validated['balance_amount'] = $totalAmount;
        $validated['status'] = 'confirmed';
        $validated['created_by'] = auth()->id();
        $validated['commission_rate'] = $commissionRate;
        $validated['commission_amount'] = $commissionAmount;

        // Map number_of_adults/children to adults/children (database column names)
        $validated['adults'] = $validated['number_of_adults'];
        $validated['children'] = $validated['number_of_children'] ?? 0;
        unset($validated['number_of_adults'], $validated['number_of_children']);

        $reservation = Reservation::create($validated);

        return redirect()->route('admin.channel-manager.show', $reservation->id)
            ->with('success', 'OTA reservation created successfully!');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'roomType', 'createdBy']);

        return Inertia::render('Admin/ChannelManager/Show', [
            'user' => auth()->user()->load('roles'),
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'booking_reference' => $reservation->booking_reference,
                'booking_source' => $reservation->booking_source,
                'guest' => $reservation->guest,
                'room' => $reservation->room,
                'room_type' => $reservation->roomType,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                'nights' => $reservation->nights,
                'number_of_adults' => $reservation->number_of_adults,
                'number_of_children' => $reservation->number_of_children,
                'room_rate' => $reservation->room_rate,
                'total_room_charges' => $reservation->total_room_charges,
                'commission_rate' => $reservation->commission_rate,
                'commission_amount' => $reservation->commission_amount,
                'taxes' => $reservation->taxes,
                'service_charges' => $reservation->service_charges,
                'discount_amount' => $reservation->discount_amount,
                'total_amount' => $reservation->total_amount,
                'paid_amount' => $reservation->paid_amount,
                'balance_amount' => $reservation->balance_amount,
                'special_requests' => $reservation->special_requests,
                'status' => $reservation->status,
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function edit(Reservation $reservation)
    {
        $guests = Guest::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'phone']);
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'base_price']);

        return Inertia::render('Admin/ChannelManager/Edit', [
            'user' => auth()->user()->load('roles'),
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'guest_id' => $reservation->guest_id,
                'room_type_id' => $reservation->room_type_id,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                'number_of_adults' => $reservation->number_of_adults,
                'number_of_children' => $reservation->number_of_children,
                'booking_source' => $reservation->booking_source,
                'booking_reference' => $reservation->booking_reference,
                'room_rate' => $reservation->room_rate,
                'commission_rate' => $reservation->commission_rate,
                'commission_amount' => $reservation->commission_amount,
                'discount_amount' => $reservation->discount_amount,
                'special_requests' => $reservation->special_requests,
                'status' => $reservation->status,
            ],
            'guests' => $guests,
            'roomTypes' => $roomTypes,
            'bookingSources' => [
                'booking_com' => 'Booking.com',
                'expedia' => 'Expedia',
                'agoda' => 'Agoda',
                'travel_agent' => 'Travel Agent',
                'corporate' => 'Corporate',
            ],
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'booking_source' => 'required|in:booking_com,expedia,agoda,travel_agent,corporate',
            'booking_reference' => 'required|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'special_requests' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled,no_show,modified',
        ]);

        // Recalculate if dates or rates changed
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        $roomRate = $validated['room_rate'];
        $totalRoomCharges = $roomRate * $nights;

        // Apply manual discount if provided
        $discountAmount = $validated['discount_amount'] ?? 0;
        $taxRate = Setting::get('room_tax_rate', Setting::get('tax_rate', 0)) / 100;
        $serviceChargeRate = Setting::get('service_charge_rate', 0) / 100;

        $taxes = ($totalRoomCharges - $discountAmount) * $taxRate;
        $serviceCharges = ($totalRoomCharges - $discountAmount) * $serviceChargeRate;
        $totalAmount = $totalRoomCharges - $discountAmount + $taxes + $serviceCharges;

        // Calculate commission
        $commissionRate = $validated['commission_rate'] ?? $this->getCommissionRate($validated['booking_source']);
        $commissionAmount = $validated['commission_amount'] ?? ($totalRoomCharges * ($commissionRate / 100));

        $validated['nights'] = $nights;
        $validated['total_room_charges'] = $totalRoomCharges;
        $validated['taxes'] = $taxes;
        $validated['service_charges'] = $serviceCharges;
        $validated['total_amount'] = $totalAmount;
        $validated['balance_amount'] = $totalAmount - ($reservation->paid_amount ?? 0);
        $validated['commission_rate'] = $commissionRate;
        $validated['commission_amount'] = $commissionAmount;
        $validated['updated_by'] = auth()->id();

        // Map number_of_adults/children to adults/children (database column names)
        $validated['adults'] = $validated['number_of_adults'];
        $validated['children'] = $validated['number_of_children'] ?? 0;
        unset($validated['number_of_adults'], $validated['number_of_children']);

        $reservation->update($validated);

        return redirect()->route('admin.channel-manager.show', $reservation->id)
            ->with('success', 'OTA reservation updated successfully!');
    }

    public function syncInventory()
    {
        // Get room availability for next 30 days
        $availability = [];
        $today = Carbon::today();

        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');

            $availability[$dateStr] = [];

            $roomTypes = RoomType::where('is_active', true)->get();
            foreach ($roomTypes as $roomType) {
                $totalRooms = $roomType->rooms->count();
                $bookedRooms = Reservation::where('room_type_id', $roomType->id)
                    ->whereIn('status', ['confirmed', 'checked_in', 'pending'])
                    ->whereDate('check_in_date', '<=', $date)
                    ->whereDate('check_out_date', '>', $date)
                    ->count();

                $availableRooms = max(0, $totalRooms - $bookedRooms);

                $availability[$dateStr][$roomType->id] = [
                    'room_type_id' => $roomType->id,
                    'room_type_name' => $roomType->name,
                    'total_rooms' => $totalRooms,
                    'booked_rooms' => $bookedRooms,
                    'available_rooms' => $availableRooms,
                    'base_price' => $roomType->base_price,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => now()->toDateString(),
                'availability' => $availability,
                'message' => 'Inventory sync completed successfully'
            ],
        ]);
    }

    private function getCommissionRates()
    {
        return [
            'booking_com' => 15.00,
            'expedia' => 15.00,
            'agoda' => 15.00,
            'travel_agent' => 10.00,
            'corporate' => 5.00,
        ];
    }

    private function getCommissionRate($bookingSource)
    {
        $rates = $this->getCommissionRates();
        return $rates[$bookingSource] ?? 10.00;
    }

    private function calculateCommission($reservation)
    {
        return $reservation->total_room_charges * ($reservation->commission_rate / 100);
    }

    private function checkOverbooking($checkIn, $checkOut, $roomTypeId)
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        $totalRooms = RoomType::find($roomTypeId)->rooms->count();

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
