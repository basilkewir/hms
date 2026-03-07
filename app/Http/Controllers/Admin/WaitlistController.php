<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Waitlist;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaitlistController extends Controller
{
    public function index()
    {
        $waitlists = Waitlist::with(['guest', 'roomType'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(20)
            ->through(function($waitlist) {
                return [
                    'id' => $waitlist->id,
                    'guest_name' => $waitlist->guest->full_name ?? 'N/A',
                    'guest_email' => $waitlist->guest->email ?? 'N/A',
                    'guest_phone' => $waitlist->guest->phone ?? 'N/A',
                    'room_type' => $waitlist->roomType?->name,
                    'requested_dates' => $waitlist->requested_check_in->format('Y-m-d') . ' to ' . $waitlist->requested_check_out->format('Y-m-d'),
                    'requested_nights' => $waitlist->requested_nights,
                    'number_of_adults' => $waitlist->number_of_adults,
                    'number_of_children' => $waitlist->number_of_children,
                    'priority' => $waitlist->priority,
                    'status' => $waitlist->status,
                    'contact_email' => $waitlist->contact_email,
                    'contact_phone' => $waitlist->contact_phone,
                    'special_requests' => $waitlist->special_requests,
                    'notified_at' => $waitlist->notified_at?->format('Y-m-d H:i:s'),
                    'converted_at' => $waitlist->converted_at?->format('Y-m-d H:i:s'),
                    'expires_at' => $waitlist->expires_at?->format('Y-m-d H:i:s'),
                    'is_expired' => $waitlist->expires_at && $waitlist->expires_at->isPast(),
                    'can_convert' => $this->canConvertToReservation($waitlist),
                ];
            });

        $stats = [
            'total' => Waitlist::count(),
            'active' => Waitlist::where('status', 'active')->count(),
            'notified' => Waitlist::whereNotNull('notified_at')->count(),
            'converted' => Waitlist::whereNotNull('converted_at')->count(),
            'expired' => Waitlist::where('expires_at', '<', now())->count(),
        ];

        return Inertia::render('Admin/Waitlist/Index', [
            'user' => auth()->user()->load('roles'),
            'waitlists' => $waitlists,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $guests = Guest::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'phone']);
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Waitlist/Create', [
            'user' => auth()->user()->load('roles'),
            'guests' => $guests,
            'roomTypes' => $roomTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'requested_check_in' => 'required|date|after_or_equal:today',
            'requested_check_out' => 'required|date|after:requested_check_in',
            'requested_nights' => 'required|integer|min:1',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'priority' => 'required|integer|min:1|max:10',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'expires_at' => 'nullable|date|after:requested_check_in',
        ]);

        // Calculate nights if not provided
        if (!$validated['requested_nights']) {
            $checkIn = Carbon::parse($validated['requested_check_in']);
            $checkOut = Carbon::parse($validated['requested_check_out']);
            $validated['requested_nights'] = $checkIn->diffInDays($checkOut);
        }

        // Set expiration date if not provided (default: 30 days from request)
        if (!$validated['expires_at']) {
            $validated['expires_at'] = Carbon::parse($validated['requested_check_in'])->addDays(30);
        }

        $validated['status'] = 'active';
        $validated['created_by'] = auth()->id();

        $waitlist = Waitlist::create($validated);

        return redirect()->route('admin.waitlist.show', $waitlist->id)
            ->with('success', 'Waitlist entry created successfully!');
    }

    public function show(Waitlist $waitlist)
    {
        $waitlist->load(['guest', 'roomType', 'convertedToReservation']);

        return Inertia::render('Admin/Waitlist/Show', [
            'user' => auth()->user()->load('roles'),
            'waitlist' => [
                'id' => $waitlist->id,
                'guest' => $waitlist->guest,
                'room_type' => $waitlist->roomType,
                'requested_check_in' => $waitlist->requested_check_in->format('Y-m-d'),
                'requested_check_out' => $waitlist->requested_check_out->format('Y-m-d'),
                'requested_nights' => $waitlist->requested_nights,
                'number_of_adults' => $waitlist->number_of_adults,
                'number_of_children' => $waitlist->number_of_children,
                'priority' => $waitlist->priority,
                'status' => $waitlist->status,
                'contact_email' => $waitlist->contact_email,
                'contact_phone' => $waitlist->contact_phone,
                'special_requests' => $waitlist->special_requests,
                'notified_at' => $waitlist->notified_at?->format('Y-m-d H:i:s'),
                'converted_at' => $waitlist->converted_at?->format('Y-m-d H:i:s'),
                'converted_to_reservation_id' => $waitlist->converted_to_reservation_id,
                'converted_reservation' => $waitlist->convertedToReservation,
                'expires_at' => $waitlist->expires_at?->format('Y-m-d H:i:s'),
                'is_expired' => $waitlist->expires_at && $waitlist->expires_at->isPast(),
                'can_convert' => $this->canConvertToReservation($waitlist),
            ],
        ]);
    }

    public function edit(Waitlist $waitlist)
    {
        $guests = Guest::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'phone']);
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Waitlist/Edit', [
            'user' => auth()->user()->load('roles'),
            'waitlist' => [
                'id' => $waitlist->id,
                'guest_id' => $waitlist->guest_id,
                'room_type_id' => $waitlist->room_type_id,
                'requested_check_in' => $waitlist->requested_check_in->format('Y-m-d'),
                'requested_check_out' => $waitlist->requested_check_out->format('Y-m-d'),
                'requested_nights' => $waitlist->requested_nights,
                'number_of_adults' => $waitlist->number_of_adults,
                'number_of_children' => $waitlist->number_of_children,
                'priority' => $waitlist->priority,
                'status' => $waitlist->status,
                'contact_email' => $waitlist->contact_email,
                'contact_phone' => $waitlist->contact_phone,
                'special_requests' => $waitlist->special_requests,
                'expires_at' => $waitlist->expires_at?->format('Y-m-d'),
            ],
            'guests' => $guests,
            'roomTypes' => $roomTypes,
        ]);
    }

    public function update(Request $request, Waitlist $waitlist)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'requested_check_in' => 'required|date',
            'requested_check_out' => 'required|date|after:requested_check_in',
            'requested_nights' => 'required|integer|min:1',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'priority' => 'required|integer|min:1|max:10',
            'status' => 'required|in:active,notified,converted,expired,cancelled',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'expires_at' => 'nullable|date|after:requested_check_in',
        ]);

        $waitlist->update($validated);

        return redirect()->route('admin.waitlist.show', $waitlist->id)
            ->with('success', 'Waitlist entry updated successfully!');
    }

    public function destroy(Waitlist $waitlist)
    {
        $waitlist->delete();

        return redirect()->route('admin.waitlist.index')
            ->with('success', 'Waitlist entry deleted successfully!');
    }

    public function convertToReservation(Waitlist $waitlist)
    {
        if (!$this->canConvertToReservation($waitlist)) {
            return back()->withErrors(['error' => 'Cannot convert this waitlist entry to reservation. No available rooms or entry is expired.']);
        }

        // Check for overbooking
        $overbookingLimit = Setting::get('overbooking_limit', 10);
        $thisOverbooking = $this->checkOverbooking($waitlist->requested_check_in, $waitlist->requested_check_out, $waitlist->room_type_id);

        if ($thisOverbooking['is_overbooked']) {
            return back()->withErrors(['error' => "Cannot convert: {$thisOverbooking['message']}"]);
        }

        // Find available room
        $availableRoom = $this->findAvailableRoom($waitlist->room_type_id, $waitlist->requested_check_in, $waitlist->requested_check_out);

        if (!$availableRoom) {
            return back()->withErrors(['error' => 'No available rooms found for the requested dates.']);
        }

        // Calculate pricing
        $roomRate = $waitlist->roomType->base_price;
        $nights = $waitlist->requested_nights;
        $totalRoomCharges = $roomRate * $nights;

        $taxRate = Setting::get('tax_rate', 0) / 100;
        $serviceChargeRate = Setting::get('service_charge_rate', 0) / 100;

        $taxes = $totalRoomCharges * $taxRate;
        $serviceCharges = $totalRoomCharges * $serviceChargeRate;
        $totalAmount = $totalRoomCharges + $taxes + $serviceCharges;

        // Generate unique reservation number
        $reservationNumber = 'WL-' . strtoupper(Str::random(8));
        while (Reservation::where('reservation_number', $reservationNumber)->exists()) {
            $reservationNumber = 'WL-' . strtoupper(Str::random(8));
        }

        // Create reservation
        $reservation = Reservation::create([
            'reservation_number' => $reservationNumber,
            'guest_id' => $waitlist->guest_id,
            'room_id' => $availableRoom->id,
            'room_type_id' => $waitlist->room_type_id,
            'check_in_date' => $waitlist->requested_check_in,
            'check_out_date' => $waitlist->requested_check_out,
            'nights' => $nights,
            'number_of_adults' => $waitlist->number_of_adults,
            'number_of_children' => $waitlist->number_of_children,
            'booking_source' => 'waitlist',
            'room_rate' => $roomRate,
            'total_room_charges' => $totalRoomCharges,
            'taxes' => $taxes,
            'service_charges' => $serviceCharges,
            'total_amount' => $totalAmount,
            'balance_amount' => $totalAmount,
            'special_requests' => $waitlist->special_requests,
            'status' => 'confirmed',
            'created_by' => auth()->id(),
        ]);

        // Update waitlist status
        $waitlist->update([
            'status' => 'converted',
            'converted_at' => now(),
            'converted_to_reservation_id' => $reservation->id,
            'updated_by' => auth()->id(),
        ]);

        // Mark room as reserved
        $availableRoom->update(['status' => 'reserved']);

        // Send confirmation email
        if ($waitlist->guest && $waitlist->guest->email) {
            $this->sendWaitlistConversionEmail($waitlist, $reservation);
        }

        return redirect()->route('admin.reservations.show', $reservation->id)
            ->with('success', 'Waitlist entry successfully converted to reservation!');
    }

    public function notify(Waitlist $waitlist)
    {
        if ($waitlist->status === 'converted' || $waitlist->status === 'expired') {
            return back()->withErrors(['error' => 'Cannot notify: Waitlist entry is already converted or expired.']);
        }

        // Send notification email
        $this->sendWaitlistNotification($waitlist);

        $waitlist->update([
            'status' => 'notified',
            'notified_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Waitlist notification sent successfully!');
    }

    public function checkAvailability()
    {
        $availableRooms = $this->checkAllAvailability();

        return Inertia::render('Admin/Waitlist/Availability', [
            'user' => auth()->user()->load('roles'),
            'availability' => $availableRooms,
        ]);
    }

    public function autoNotify()
    {
        $notifications = [];

        // Check each waitlist entry for availability
        $waitlists = Waitlist::where('status', 'active')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($waitlists as $waitlist) {
            if ($this->canConvertToReservation($waitlist)) {
                // Send notification
                $this->sendWaitlistNotification($waitlist);

                $waitlist->update([
                    'status' => 'notified',
                    'notified_at' => now(),
                ]);

                $notifications[] = [
                    'waitlist_id' => $waitlist->id,
                    'guest_name' => $waitlist->guest->full_name,
                    'guest_email' => $waitlist->guest->email,
                    'room_type' => $waitlist->roomType->name,
                    'dates' => $waitlist->requested_check_in->format('Y-m-d') . ' to ' . $waitlist->requested_check_out->format('Y-m-d'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto-notification completed',
            'notifications_sent' => count($notifications),
            'details' => $notifications,
        ]);
    }

    private function canConvertToReservation($waitlist)
    {
        if ($waitlist->status === 'converted' || $waitlist->status === 'expired') {
            return false;
        }

        if ($waitlist->expires_at && $waitlist->expires_at->isPast()) {
            return false;
        }

        return $this->findAvailableRoom($waitlist->room_type_id, $waitlist->requested_check_in, $waitlist->requested_check_out) !== null;
    }

    private function findAvailableRoom($roomTypeId, $checkIn, $checkOut)
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        return Room::where('room_type_id', $roomTypeId)
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean')
            ->whereNotExists(function ($query) use ($checkInDate, $checkOutDate) {
                $query->select(DB::raw(1))
                    ->from('reservations')
                    ->whereColumn('reservations.room_id', 'rooms.id')
                    ->whereIn('reservations.status', ['confirmed', 'checked_in', 'pending'])
                    ->where(function($q) use ($checkInDate, $checkOutDate) {
                        $q->whereBetween('reservations.check_in_date', [$checkInDate, $checkOutDate])
                          ->orWhereBetween('reservations.check_out_date', [$checkInDate, $checkOutDate])
                          ->orWhere(function($qq) use ($checkInDate, $checkOutDate) {
                              $qq->where('reservations.check_in_date', '<=', $checkInDate)
                                 ->where('reservations.check_out_date', '>=', $checkOutDate);
                          });
                    });
            })
            ->first();
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

    private function checkAllAvailability()
    {
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

        return $availability;
    }

    private function sendWaitlistNotification($waitlist)
    {
        try {
            $waitlist->load(['guest', 'roomType']);

            Mail::send('emails.waitlist-availability', [
                'waitlist' => $waitlist,
                'hotel' => [
                    'name' => Setting::get('hotel_name', 'Hotel'),
                    'address' => Setting::get('hotel_address', ''),
                    'phone' => Setting::get('hotel_phone', ''),
                    'email' => Setting::get('hotel_email', ''),
                ],
            ], function ($message) use ($waitlist) {
                $message->to($waitlist->contact_email, $waitlist->guest->full_name)
                    ->subject('Room Availability - Your Waitlist Request');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send waitlist notification: ' . $e->getMessage());
        }
    }

    private function sendWaitlistConversionEmail($waitlist, $reservation)
    {
        try {
            $waitlist->load(['guest', 'roomType']);

            Mail::send('emails.waitlist-conversion', [
                'waitlist' => $waitlist,
                'reservation' => $reservation,
                'hotel' => [
                    'name' => Setting::get('hotel_name', 'Hotel'),
                    'address' => Setting::get('hotel_address', ''),
                    'phone' => Setting::get('hotel_phone', ''),
                    'email' => Setting::get('hotel_email', ''),
                ],
            ], function ($message) use ($waitlist) {
                $message->to($waitlist->contact_email, $waitlist->guest->full_name)
                    ->subject('Reservation Confirmed - Your Waitlist Request');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send waitlist conversion email: ' . $e->getMessage());
        }
    }
}
