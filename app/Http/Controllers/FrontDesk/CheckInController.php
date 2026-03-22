<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\KeyCard;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $selectedReservationId = $request->query('reservation_id');

        // Get today's arrivals
        $arrivals = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_in_date', $today)
            ->whereIn('status', ['confirmed', 'pending'])
            ->get();

        // Get all check-in eligible reservations (for manual check-in)
        $allReservations = Reservation::with(['guest', 'room', 'roomType'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '<=', now())
            ->orderBy('check_in_date', 'desc')
            ->limit(100)
            ->get();

        // If a reservation ID is provided, pre-select it
        $selectedReservation = null;
        if ($selectedReservationId) {
            $selectedReservation = Reservation::with(['guest', 'room', 'roomType'])
                ->where('id', $selectedReservationId)
                ->whereIn('status', ['confirmed', 'pending'])
                ->first();
        }

        $availableRooms = Room::with('roomType')
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean') // Only show rooms that are clean and ready
            ->get();

        // Also include reserved rooms that are clean (for reservations that already have a room assigned)
        $reservedRooms = Room::with('roomType')
            ->where('status', 'reserved')
            ->where('housekeeping_status', 'clean')
            ->get();

        $availableRooms = $availableRooms->merge($reservedRooms)->unique('id');

        $availableKeyCards = KeyCard::available()->get();

        $user = auth()->user();
        $layout = 'FrontDesk';

        // Determine layout based on role
        if ($user->hasRole('admin')) {
            $layout = 'Admin';
        } elseif ($user->hasRole('manager')) {
            $layout = 'Manager';
        }

        return Inertia::render($layout . '/CheckIn', [
            'user' => $user->load('roles'),
            'selectedReservationId' => $selectedReservationId,
            'availableKeyCards' => $availableKeyCards->map(fn($card) => [
                'id' => $card->id,
                'card_number' => $card->card_number,
                'card_type' => $card->card_type,
            ]),
            'todaysArrivals' => $arrivals->map(function($r) {
                $reservedRoom = $r->room;
                // Allow check-in if room is available OR if it's reserved for this specific reservation
                $isRoomAvailable = $reservedRoom &&
                    ($reservedRoom->status === 'available' ||
                     ($reservedRoom->status === 'reserved' && $reservedRoom->housekeeping_status === 'clean') ||
                     ($reservedRoom->status === 'reserved' && $r->room_id === $reservedRoom->id));

                return [
                    'id' => $r->id,
                    'reservation_number' => $r->reservation_number,
                    'guestName' => $r->guest->full_name ?? 'N/A',
                    'guest_id' => $r->guest_id,
                    'roomNumber' => $reservedRoom->room_number ?? 'TBA',
                    'room_id' => $r->room_id,
                    'room_type' => $r->roomType->name ?? 'N/A',
                    'nights' => $r->check_in_date && $r->check_out_date
                        ? now()->parse($r->check_in_date)->diffInDays($r->check_out_date)
                        : 0,
                    'guestCount' => ($r->adults ?? $r->number_of_adults ?? 0) + ($r->children ?? $r->number_of_children ?? 0),
                    'arrivalTime' => $r->check_in_date,
                    'check_in_date' => $r->check_in_date->format('Y-m-d'),
                    'check_out_date' => $r->check_out_date->format('Y-m-d'),
                    'room_rate' => (float) ($r->room_rate ?? 0),
                    'paid_amount' => (float) ($r->paid_amount ?? 0),
                    'total_amount' => (float) ($r->total_amount ?? 0),
                    'balance_amount' => (float) ($r->balance_amount ?? $r->total_amount ?? 0),
                    'status' => $r->status === 'checked_in' ? 'checked_in' : 'pending',
                    'reservedRoomAvailable' => $isRoomAvailable,
                    'reservedRoomStatus' => $reservedRoom ? $reservedRoom->status : null,
                    'reservedRoomHousekeepingStatus' => $reservedRoom ? $reservedRoom->housekeeping_status : null,
                ];
            }),
            'allReservations' => $allReservations->map(function($r) {
                $reservedRoom = $r->room;
                // Allow check-in if room is available OR if it's reserved for this specific reservation
                $isRoomAvailable = $reservedRoom &&
                    ($reservedRoom->status === 'available' ||
                     ($reservedRoom->status === 'reserved' && $reservedRoom->housekeeping_status === 'clean') ||
                     ($reservedRoom->status === 'reserved' && $r->room_id === $reservedRoom->id));

                return [
                    'id' => $r->id,
                    'reservation_number' => $r->reservation_number,
                    'guestName' => $r->guest->full_name ?? 'N/A',
                    'guest_id' => $r->guest_id,
                    'roomNumber' => $reservedRoom->room_number ?? 'TBA',
                    'room_id' => $r->room_id,
                    'room_type' => $r->roomType->name ?? 'N/A',
                    'nights' => $r->check_in_date && $r->check_out_date
                        ? now()->parse($r->check_in_date)->diffInDays($r->check_out_date)
                        : 0,
                    'guestCount' => ($r->adults ?? $r->number_of_adults ?? 0) + ($r->children ?? $r->number_of_children ?? 0),
                    'arrivalTime' => $r->check_in_date,
                    'check_in_date' => $r->check_in_date->format('Y-m-d'),
                    'check_out_date' => $r->check_out_date->format('Y-m-d'),
                    'room_rate' => (float) ($r->room_rate ?? 0),
                    'paid_amount' => (float) ($r->paid_amount ?? 0),
                    'total_amount' => (float) ($r->total_amount ?? 0),
                    'balance_amount' => (float) ($r->balance_amount ?? $r->total_amount ?? 0),
                    'status' => $r->status === 'checked_in' ? 'checked_in' : 'pending',
                    'reservedRoomAvailable' => $isRoomAvailable,
                    'reservedRoomStatus' => $reservedRoom ? $reservedRoom->status : null,
                    'reservedRoomHousekeepingStatus' => $reservedRoom ? $reservedRoom->housekeeping_status : null,
                ];
            }),
            'availableRooms' => $availableRooms->map(fn($room) => [
                'id' => $room->id,
                'number' => $room->room_number,
                'type' => $room->roomType->name ?? 'N/A',
                'room_type_id' => $room->room_type_id,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id'  => 'required|exists:reservations,id',
            'room_number'     => 'required|exists:rooms,room_number',
            'key_card_id'     => 'nullable|exists:key_cards,id',
            'payment_amount'  => 'nullable|numeric|min:0',
            'payment_method'  => 'nullable|in:cash,card,bank_transfer',
        ]);

        $reservation = Reservation::with(['guest', 'room'])->findOrFail($validated['reservation_id']);
        $room = Room::where('room_number', $validated['room_number'])->firstOrFail();

        // Verify the room is available, clean, or reserved for this reservation
        $canCheckIn = $room->status === 'available' ||
                     ($room->housekeeping_status === 'clean') ||
                     ($room->status === 'reserved' && $reservation->room_id === $room->id);

        if (!$canCheckIn) {
            return back()->withErrors([
                'room_number' => "Room {$validated['room_number']} is not available or not clean. Please select a different room."
            ]);
        }

        // Late arrival adjustment: if the original check_in_date is earlier than today
        // AND the current time is at or after 12:00 noon, start billing from today
        // and remove the skipped day(s) from the reservation so the guest is not
        // charged for nights they were not present.
        $originalCheckIn = Carbon::parse($reservation->check_in_date)->startOfDay();
        $todayStart      = now()->startOfDay();
        $lateArrivalDays = 0;

        if ($originalCheckIn->lt($todayStart) && now()->hour >= 12) {
            $lateArrivalDays = (int) $originalCheckIn->diffInDays($todayStart);
        }

        $updateFields = [
            'room_id'         => $room->id,
            'status'          => 'checked_in',
            'actual_check_in' => now(),
            'checked_in_by'   => auth()->id(),
        ];

        if ($lateArrivalDays > 0) {
            $newNights       = max(1, (int) ($reservation->nights ?? 1) - $lateArrivalDays);
            $roomRate        = (float) ($reservation->room_rate ?? 0);
            $discountAmount  = (float) ($reservation->discount_amount ?? 0);
            $newRoomCharges  = $roomRate * $newNights;
            $newTotal        = max(0, $newRoomCharges - $discountAmount);
            $newBalance      = max(0, $newTotal - (float) ($reservation->paid_amount ?? 0));

            $updateFields['check_in_date']       = $todayStart->toDateString();
            $updateFields['nights']              = $newNights;
            $updateFields['total_room_charges']  = $newRoomCharges;
            $updateFields['total_amount']        = $newTotal;
            $updateFields['balance_amount']      = $newBalance;
        }

        $reservation->update($updateFields);

        // Mark room as occupied. Keep housekeeping_status 'clean' for the rest
        // of today — the nightly GenerateDailyCleaningTasks command will flip it
        // to 'dirty' the next morning so housekeeping receives the task then.
        $room->update([
            'status' => 'occupied',
            'housekeeping_status' => 'clean',
        ]);

        // Create GuestFolio and record room charges at check-in
        $folio = $this->createGuestFolio($reservation, $room);

        // Record any payment made at check-in
        $paymentAmount = isset($validated['payment_amount']) ? (float) $validated['payment_amount'] : 0;
        if ($paymentAmount > 0 && $folio) {
            $paymentMethod = $validated['payment_method'] ?? 'cash';
            $newPaid       = $folio->paid_amount + $paymentAmount;
            $newBalance    = max(0, $folio->total_amount - $newPaid);
            $folio->update([
                'paid_amount'    => $newPaid,
                'balance_amount' => $newBalance,
                'payment_status' => $newBalance <= 0 ? 'paid' : 'partial',
            ]);

            // Record a folio charge/payment entry for audit trail
            \App\Models\FolioCharge::create([
                'guest_folio_id'  => $folio->id,
                'charge_code'     => 'PAYMENT',
                'description'     => 'Payment at check-in (' . strtoupper($paymentMethod) . ')',
                'charge_date'     => now()->toDateString(),
                'charge_time'     => now()->format('H:i:s'),
                'quantity'        => 1,
                'unit_price'      => -$paymentAmount,
                'total_amount'    => -$paymentAmount,
                'tax_rate'        => 0,
                'tax_amount'      => 0,
                'discount_rate'   => 0,
                'discount_amount' => 0,
                'net_amount'      => -$paymentAmount,
                'reference_type'  => 'reservation',
                'reference_id'    => $reservation->id,
                'department'      => 'Front Desk',
                'posted_by'       => auth()->check() ? auth()->id() : null,
                'posted_at'       => now(),
            ]);
        }

        // Assign key card if provided
        if (!empty($validated['key_card_id'])) {
            $keyCard = KeyCard::findOrFail($validated['key_card_id']);
            if ($keyCard->status === 'available') {
                $keyCard->assignToReservation(
                    $reservation->id,
                    $room->id,
                    $reservation->guest_id,
                    auth()->id(),
                    $reservation->check_out_date
                );
            }
        }

        // Build success message
        $keyCardMsg      = !empty($validated['key_card_id']) ? ' and key card assigned' : '';
        $paymentMsg      = $paymentAmount > 0 ? ' — payment of $' . number_format($paymentAmount, 2) . ' recorded' : '';
        $lateArrivalMsg  = $lateArrivalDays > 0
            ? " — {$lateArrivalDays} day(s) removed from billing (late arrival after 12:00 noon)"
            : '';
        $successMsg  = 'Guest checked in successfully' . $keyCardMsg . $paymentMsg . $lateArrivalMsg;

        // If a payment was made at check-in, redirect to the check-in receipt
        if ($paymentAmount > 0) {
            $user = auth()->user();
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.checkin.receipt', ['reservation_id' => $reservation->id])
                    ->with('success', $successMsg);
            } elseif ($user->hasRole('manager')) {
                return redirect()->route('manager.checkin.receipt', ['reservation_id' => $reservation->id])
                    ->with('success', $successMsg);
            }
            return redirect()->route('front-desk.checkin.receipt', ['reservation_id' => $reservation->id])
                ->with('success', $successMsg);
        }

        // Redirect based on user role (no payment)
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('success', $successMsg);
        } elseif ($user->hasRole('manager')) {
            return redirect()->route('manager.dashboard')->with('success', $successMsg);
        }

        return redirect()->route('front-desk.checkin')->with('success', $successMsg);
    }

    /**
     * Print check-in payment receipt.
     */
    public function printReceipt(Request $request)
    {
        $reservationId = $request->query('reservation_id');
        if (!$reservationId) {
            return redirect()->back()->withErrors(['reservation_id' => 'Reservation ID required.']);
        }

        $reservation = Reservation::with(['guest', 'room', 'roomType'])->find($reservationId);
        if (!$reservation) {
            return redirect()->back()->withErrors(['reservation' => 'Reservation not found.']);
        }

        $folio = GuestFolio::where('reservation_id', $reservation->id)->first();

        // Get the most recent check-in payment (stored as a FolioCharge with charge_code 'PAYMENT')
        $lastPaymentCharge = FolioCharge::where('reference_id', $reservation->id)
            ->where('reference_type', 'reservation')
            ->where('charge_code', 'PAYMENT')
            ->latest('posted_at')
            ->first();

        $user = auth()->user();
        $role = 'front_desk';
        if ($user->hasRole('admin')) $role = 'admin';
        elseif ($user->hasRole('manager')) $role = 'manager';

        return Inertia::render('CheckIn/Receipt', [
            'user' => $user->load('roles'),
            'role' => $role,
            'receipt' => [
                'reservation_number' => $reservation->reservation_number,
                'guest_name' => $reservation->guest->full_name ?? 'N/A',
                'room_number' => $reservation->room?->room_number ?? 'N/A',
                'check_in_date' => $reservation->check_in_date?->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date?->format('Y-m-d'),
                'actual_check_in' => $reservation->actual_check_in?->format('Y-m-d H:i'),
                'nights' => $reservation->check_in_date && $reservation->check_out_date
                    ? $reservation->check_in_date->diffInDays($reservation->check_out_date)
                    : 0,
                'room_rate' => $reservation->room_rate ?? ($reservation->roomType?->base_price ?? 0),
                'total_amount' => $folio ? $folio->total_amount : ($reservation->total_amount ?? 0),
                'paid_amount' => $folio ? $folio->paid_amount : ($reservation->paid_amount ?? 0),
                'balance_amount' => $folio ? $folio->balance_amount : ($reservation->balance_amount ?? 0),
                'payment_amount' => $lastPaymentCharge ? abs($lastPaymentCharge->total_amount) : 0,
                'payment_method' => $lastPaymentCharge
                    ? (preg_match('/\((\w+)\)/', $lastPaymentCharge->description, $m) ? strtolower($m[1]) : 'cash')
                    : 'cash',
                'folio_number' => $folio->folio_number ?? null,
            ],
            'hotelName' => Setting::get('hotel_name', 'Hotel'),
            'hotelAddress' => Setting::get('hotel_address', ''),
            'hotelPhone' => Setting::get('hotel_phone', ''),
            'hotelEmail' => Setting::get('hotel_email', ''),
            'hotelLogo' => Setting::get('hotel_logo', ''),
            'hotelWebsite' => Setting::get('hotel_website', ''),
        ]);
    }

    /**
     * Create GuestFolio and record room charges at check-in
     * This ensures room payments are tracked in transactions from check-in onwards
     */
    private function createGuestFolio(Reservation $reservation, Room $room)
    {
        // Check if folio already exists
        $existingFolio = GuestFolio::where('reservation_id', $reservation->id)->first();
        if ($existingFolio) {
            return $existingFolio;
        }

        // Get room rate from reservation or room type
        $roomRate = $reservation->room_rate ?? 0;
        if (!$roomRate && $room->roomType) {
            $roomRate = $room->roomType->base_price ?? 0;
        }

        // Calculate nights
        $checkInDate = $reservation->check_in_date instanceof Carbon
            ? $reservation->check_in_date
            : Carbon::parse($reservation->check_in_date);
        $checkOutDate = $reservation->check_out_date instanceof Carbon
            ? $reservation->check_out_date
            : Carbon::parse($reservation->check_out_date);
        $nights = $checkInDate->diffInDays($checkOutDate);
        if ($nights < 1) {
            $nights = 1;
        }

        // Calculate room charges
        $roomCharges = $roomRate * $nights;

        // Get tax and service charge rates — always use room_tax_rate for room charges
        $taxRate = Setting::get('room_tax_rate', Setting::get('tax_rate', 0));
        $serviceChargeRate = Setting::get('service_charge_rate', 0);

        // Calculate taxes and service charges
        $taxAmount = ($roomCharges * $taxRate) / 100;
        $serviceChargeAmount = ($roomCharges * $serviceChargeRate) / 100;

        // Calculate total
        $totalAmount = $roomCharges + $taxAmount + $serviceChargeAmount;

        // Create guest folio
        $folio = GuestFolio::create([
            'folio_number' => 'FOL-' . strtoupper(Str::random(8)),
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $room->id,
            'status' => 'open',
            'folio_date' => now()->toDateString(),
            'room_charges' => $roomCharges,
            'service_charges' => $serviceChargeAmount,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'balance_amount' => $totalAmount,
            'is_charged_to_room' => false,
            'payment_status' => 'pending',
        ]);

        // Create folio charge for room
        FolioCharge::create([
            'guest_folio_id' => $folio->id,
            'charge_code' => 'ROOM',
            'description' => 'Room charges - ' . $nights . ' night(s)',
            'charge_date' => now()->toDateString(),
            'charge_time' => now()->format('H:i:s'),
            'quantity' => $nights,
            'unit_price' => $roomRate,
            'total_amount' => $roomCharges,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'net_amount' => $roomCharges + $taxAmount + $serviceChargeAmount,
            'reference_type' => 'reservation',
            'reference_id' => $reservation->id,
            'department' => 'Front Desk',
            'posted_by' => auth()->check() ? auth()->id() : null,
            'posted_at' => now(),
        ]);

        return $folio;
    }
}
