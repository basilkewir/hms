<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\KeyCard;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\HousekeepingTask;
use App\Models\HousekeepingSchedule;
use App\Models\HousekeepingNotification;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CheckOutController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $selectedReservationId = $request->query('reservation_id');

        // Get today's departures
        $departures = Reservation::with(['guest', 'room', 'roomType'])
            ->whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
            ->get();

        // Get all checked-in reservations (for manual check-out)
        $allCheckedIn = Reservation::with(['guest', 'room', 'roomType'])
            ->where('status', 'checked_in')
            ->orderBy('check_out_date', 'asc')
            ->limit(100)
            ->get();

        $user = auth()->user();
        $layout = 'FrontDesk';

        // Determine layout based on role
        if ($user && $user->hasRole('admin')) {
            $layout = 'Admin';
        } elseif ($user && $user->hasRole('manager')) {
            $layout = 'Manager';
        }

        // Get key cards for checked-in reservations
        $reservationIds = $allCheckedIn->pluck('id')->merge($departures->pluck('id'))->unique()->values();
        $assignedKeyCards = KeyCard::whereIn('reservation_id', $reservationIds)
            ->where('status', 'assigned')
            ->with('reservation')
            ->get()
            ->keyBy('reservation_id');

        $folios = GuestFolio::whereIn('reservation_id', $reservationIds)
            ->with(['charges' => function($query) {
                $query->where('is_voided', false)->orderBy('charge_date')->orderBy('charge_time');
            }])
            ->get()
            ->keyBy('reservation_id');

        // Get all POS sales charged to these reservations
        $posSales = Sale::whereIn('reservation_id', $reservationIds)
            ->where('is_charged_to_room', true)
            ->with(['items.product'])
            ->get()
            ->groupBy('reservation_id');

        return Inertia::render($layout . '/CheckOut', [
            'user' => $user->load('roles'),
            'selectedReservationId' => $selectedReservationId,
            'todaysDepartures' => $departures->map(function($r) use ($assignedKeyCards, $folios, $posSales) {
                $keyCard = $assignedKeyCards->get($r->id);
                $folio = $folios->get($r->id);

                // Calculate actual nights stayed (for early checkout) using 12:00 PM → 12:00 PM rule
                $actualCheckInRaw = $r->actual_check_in ?? $r->check_in_date;
                $now = now();

                if ($actualCheckInRaw) {
                    $actualCheckIn = $actualCheckInRaw instanceof Carbon
                        ? $actualCheckInRaw
                        : Carbon::parse($actualCheckInRaw);

                    // Base nights: difference in calendar days between check-in and check-out dates
                    $checkInDay = $actualCheckIn->copy()->startOfDay();
                    $checkOutDay = $now->copy()->startOfDay();
                    $baseNights = $checkInDay->diffInDays($checkOutDay);

                    // Always at least 1 night once checked in
                    $actualNights = max(1, $baseNights);

                    // If stay spans at least one full day and checkout time is after 12:00 PM,
                    // charge one extra night (day is considered 12:00 PM → 12:00 PM next day)
                    $checkoutNoon = $now->copy()->setTime(12, 0, 0);
                    if ($baseNights >= 1 && $now->gt($checkoutNoon)) {
                        $actualNights++;
                    }
                } else {
                    $actualNights = 0;
                }
                $scheduledNights = $r->check_in_date && $r->check_out_date
                    ? Carbon::parse($r->check_in_date)->diffInDays($r->check_out_date)
                    : 0;

                // Get room rate
                $roomRate = $r->room_rate ?? 0;
                if (!$roomRate && $r->roomType) {
                    $roomRate = $r->roomType->base_price ?? 0;
                }
                if (!$roomRate && $r->room && $r->room->roomType) {
                    $roomRate = $r->room->roomType->base_price ?? 0;
                }

                // Calculate room charges based on actual nights (for early checkout)
                $actualRoomCharges = $roomRate * $actualNights;
                $scheduledRoomCharges = $roomRate * $scheduledNights;

                // Get all charges from folio
                $allCharges = $folio ? $folio->charges : collect();
                $serviceCharges = $allCharges->where('charge_code', 'SERVICE')->sum('net_amount');
                $posCharges = $allCharges->where('charge_code', 'POS')->sum('net_amount');

                // For early checkout, always use recalculated actual room charges.
                // If folio has stale full-stay charges from before, ignore them for the bill preview.
                $roomCharges = $actualNights < $scheduledNights
                    ? $actualRoomCharges
                    : ($folio && $folio->room_charges > 0
                        ? $folio->room_charges
                        : ($r->total_room_charges ?? $scheduledRoomCharges));

                // Recalculate taxes and service charges based on actual room charges
                $taxRate = Setting::get('room_tax_rate', Setting::get('tax_rate', 0)) / 100; // Use room_tax_rate if set, fallback to tax_rate
                $serviceChargeRate = Setting::get('service_charge_rate', 0) / 100;

                // Get discount amount
                $discountAmount = $folio ? ($folio->discount_amount ?? 0) : ($r->discount_amount ?? 0);

                // Calculate taxes and service charges on room charges (excluding service/POS charges which have their own tax)
                $roomTaxAmount = ($roomCharges - $discountAmount) * $taxRate;
                $roomServiceChargeAmount = ($roomCharges - $discountAmount) * $serviceChargeRate;

                // Always recalculate tax from current settings rate (never use stale stored folio->tax_amount)
                $taxFromCharges = $taxRate > 0 ? $allCharges->sum('tax_amount') : 0;
                $taxAmount = $roomTaxAmount + $taxFromCharges;

                // Get POS sales for itemized display
                $posSalesForReservation = $posSales->get($r->id, collect());

                // Check for unpaid POS bills (restaurant/bar bills with pending payment status)
                $unpaidPosBills = $posSalesForReservation->filter(function($sale) {
                    return $sale->payment_status === 'pending';
                });
                $hasUnpaidBills = $unpaidPosBills->count() > 0;
                $unpaidBillsTotal = $unpaidPosBills->sum('total_amount');

                // Calculate unified total
                $unifiedTotal = $roomCharges + $serviceCharges + $posCharges + $taxAmount - $discountAmount;
                $unifiedBalance = $unifiedTotal - ($folio ? ($folio->paid_amount ?? 0) : ($r->paid_amount ?? 0));

                return [
                    'id' => $r->id,
                    'reservation_number' => $r->reservation_number,
                    'guestName' => $r->guest->full_name ?? 'N/A',
                    'guest_id' => $r->guest_id,
                    'roomNumber' => $r->room->room_number ?? 'N/A',
                    'room_id' => $r->room_id,
                    'nights' => $actualNights,
                    'scheduled_nights' => $scheduledNights,
                    'actual_nights' => $actualNights,
                    'room_rate' => number_format($roomRate, 2),
                    'is_early_checkout' => $actualNights < $scheduledNights,
                    'totalAmount' => number_format($r->total_price ?? $r->total_amount ?? 0, 2),
                    'roomCharges' => number_format($roomCharges, 2),
                    'serviceCharges' => number_format($serviceCharges, 2),
                    'posCharges' => number_format($posCharges, 2),
                    'taxAmount' => number_format($taxAmount, 2),
                    'discountAmount' => number_format($discountAmount, 2),
                    'unifiedTotal' => number_format($unifiedTotal, 2),
                    'balanceAmount' => number_format($r->balance_amount ?? 0, 2),
                    'unifiedBalance' => number_format($unifiedBalance, 2),
                    'paidAmount' => number_format($folio ? ($folio->paid_amount ?? 0) : ($r->paid_amount ?? 0), 2),
                    'departureTime' => $r->check_out_date,
                    'check_in_date' => $r->check_in_date->format('Y-m-d'),
                    'check_out_date' => $r->check_out_date->format('Y-m-d'),
                    'actual_check_in' => $r->actual_check_in?->format('Y-m-d H:i'),
                    'status' => $r->status,
                    'folio' => $folio ? [
                        'id' => $folio->id,
                        'folio_number' => $folio->folio_number,
                        'charges' => $allCharges->map(function($charge) {
                            return [
                                'id' => $charge->id,
                                'charge_code' => $charge->charge_code,
                                'description' => $charge->description,
                                'charge_date' => $charge->charge_date->format('Y-m-d'),
                                'charge_time' => $charge->charge_time?->format('H:i'),
                                'quantity' => $charge->quantity,
                                'unit_price' => $charge->unit_price,
                                'net_amount' => $charge->net_amount,
                                'department' => $charge->department,
                            ];
                        })->values(),
                    ] : null,
                    'posSales' => $posSalesForReservation->map(function($sale) {
                        return [
                            'id' => $sale->id,
                            'sale_number' => $sale->sale_number,
                            'sale_date' => $sale->sale_date->format('Y-m-d H:i'),
                            'total_amount' => $sale->total_amount,
                            'payment_status' => $sale->payment_status,
                            'is_paid' => $sale->payment_status === 'completed',
                            'items' => $sale->items->map(function($item) {
                                return [
                                    'product_name' => $item->product->name ?? 'N/A',
                                    'quantity' => $item->quantity,
                                    'unit_price' => $item->unit_price,
                                    'total_price' => $item->total_price,
                                ];
                            }),
                        ];
                    })->values(),
                    'hasUnpaidBills' => $hasUnpaidBills,
                    'unpaidBillsTotal' => number_format($unpaidBillsTotal, 2),
                    'unpaidBillsCount' => $unpaidPosBills->count(),
                    'key_card' => $keyCard ? [
                        'id' => $keyCard->id,
                        'card_number' => $keyCard->card_number,
                        'card_type' => $keyCard->card_type,
                    ] : null,
                ];
            }),
            'allCheckedIn' => $allCheckedIn->map(function($r) use ($assignedKeyCards, $folios, $posSales) {
                $keyCard = $assignedKeyCards->get($r->id);
                $folio = $folios->get($r->id);

                // Calculate actual nights stayed (for early checkout) using 12:00 PM → 12:00 PM rule
                $actualCheckInRaw = $r->actual_check_in ?? $r->check_in_date;
                $now = now();

                if ($actualCheckInRaw) {
                    $actualCheckIn = $actualCheckInRaw instanceof Carbon
                        ? $actualCheckInRaw
                        : Carbon::parse($actualCheckInRaw);

                    // Base nights: difference in calendar days between check-in and check-out dates
                    $checkInDay = $actualCheckIn->copy()->startOfDay();
                    $checkOutDay = $now->copy()->startOfDay();
                    $baseNights = $checkInDay->diffInDays($checkOutDay);

                    // Always at least 1 night once checked in
                    $actualNights = max(1, $baseNights);

                    // If stay spans at least one full day and checkout time is after 12:00 PM,
                    // charge one extra night (day is considered 12:00 PM → 12:00 PM next day)
                    $checkoutNoon = $now->copy()->setTime(12, 0, 0);
                    if ($baseNights >= 1 && $now->gt($checkoutNoon)) {
                        $actualNights++;
                    }
                } else {
                    $actualNights = 0;
                }
                $scheduledNights = $r->check_in_date && $r->check_out_date
                    ? Carbon::parse($r->check_in_date)->diffInDays($r->check_out_date)
                    : 0;

                // Get room rate
                $roomRate = $r->room_rate ?? 0;
                if (!$roomRate && $r->roomType) {
                    $roomRate = $r->roomType->base_price ?? 0;
                }
                if (!$roomRate && $r->room && $r->room->roomType) {
                    $roomRate = $r->room->roomType->base_price ?? 0;
                }

                // Calculate room charges based on actual nights (for early checkout)
                $actualRoomCharges = $roomRate * $actualNights;
                $scheduledRoomCharges = $roomRate * $scheduledNights;

                // Get all charges from folio
                $allCharges = $folio ? $folio->charges : collect();
                $serviceCharges = $allCharges->where('charge_code', 'SERVICE')->sum('net_amount');
                $posCharges = $allCharges->where('charge_code', 'POS')->sum('net_amount');

                // For early checkout, always use recalculated actual room charges.
                // If folio has stale full-stay charges from before, ignore them for the bill preview.
                $roomCharges = $actualNights < $scheduledNights
                    ? $actualRoomCharges
                    : ($folio && $folio->room_charges > 0
                        ? $folio->room_charges
                        : ($r->total_room_charges ?? $scheduledRoomCharges));

                // Recalculate taxes and service charges based on actual room charges
                $taxRate = Setting::get('room_tax_rate', Setting::get('tax_rate', 0)) / 100; // Use room_tax_rate if set, fallback to tax_rate
                $serviceChargeRate = Setting::get('service_charge_rate', 0) / 100;

                // Get discount amount
                $discountAmount = $folio ? ($folio->discount_amount ?? 0) : ($r->discount_amount ?? 0);

                // Calculate taxes and service charges on room charges (excluding service/POS charges which have their own tax)
                $roomTaxAmount = ($roomCharges - $discountAmount) * $taxRate;
                $roomServiceChargeAmount = ($roomCharges - $discountAmount) * $serviceChargeRate;

                // Always recalculate tax from current settings rate (never use stale stored folio->tax_amount)
                $taxFromCharges = $taxRate > 0 ? $allCharges->sum('tax_amount') : 0;
                $taxAmount = $roomTaxAmount + $taxFromCharges;

                // Get POS sales for itemized display
                $posSalesForReservation = $posSales->get($r->id, collect());

                // Check for unpaid POS bills (restaurant/bar bills with pending payment status)
                $unpaidPosBills = $posSalesForReservation->filter(function($sale) {
                    return $sale->payment_status === 'pending';
                });
                $hasUnpaidBills = $unpaidPosBills->count() > 0;
                $unpaidBillsTotal = $unpaidPosBills->sum('total_amount');

                // Calculate unified total
                $unifiedTotal = $roomCharges + $serviceCharges + $posCharges + $taxAmount - $discountAmount;
                $unifiedBalance = $unifiedTotal - ($folio ? ($folio->paid_amount ?? 0) : ($r->paid_amount ?? 0));

                return [
                    'id' => $r->id,
                    'reservation_number' => $r->reservation_number,
                    'guestName' => $r->guest->full_name ?? 'N/A',
                    'guest_id' => $r->guest_id,
                    'roomNumber' => $r->room->room_number ?? 'N/A',
                    'room_id' => $r->room_id,
                    'nights' => $actualNights,
                    'scheduled_nights' => $scheduledNights,
                    'actual_nights' => $actualNights,
                    'room_rate' => number_format($roomRate, 2),
                    'is_early_checkout' => $actualNights < $scheduledNights,
                    'totalAmount' => number_format($r->total_price ?? $r->total_amount ?? 0, 2),
                    'roomCharges' => number_format($roomCharges, 2),
                    'serviceCharges' => number_format($serviceCharges, 2),
                    'posCharges' => number_format($posCharges, 2),
                    'taxAmount' => number_format($taxAmount, 2),
                    'discountAmount' => number_format($discountAmount, 2),
                    'unifiedTotal' => number_format($unifiedTotal, 2),
                    'balanceAmount' => number_format($r->balance_amount ?? 0, 2),
                    'unifiedBalance' => number_format($unifiedBalance, 2),
                    'paidAmount' => number_format($folio ? ($folio->paid_amount ?? 0) : ($r->paid_amount ?? 0), 2),
                    'departureTime' => $r->check_out_date,
                    'check_in_date' => $r->check_in_date->format('Y-m-d'),
                    'check_out_date' => $r->check_out_date->format('Y-m-d'),
                    'actual_check_in' => $r->actual_check_in?->format('Y-m-d H:i'),
                    'status' => $r->status,
                    'folio' => $folio ? [
                        'id' => $folio->id,
                        'folio_number' => $folio->folio_number,
                        'charges' => $allCharges->map(function($charge) {
                            return [
                                'id' => $charge->id,
                                'charge_code' => $charge->charge_code,
                                'description' => $charge->description,
                                'charge_date' => $charge->charge_date->format('Y-m-d'),
                                'charge_time' => $charge->charge_time?->format('H:i'),
                                'quantity' => $charge->quantity,
                                'unit_price' => $charge->unit_price,
                                'net_amount' => $charge->net_amount,
                                'department' => $charge->department,
                            ];
                        })->values(),
                    ] : null,
                    'posSales' => $posSalesForReservation->map(function($sale) {
                        return [
                            'id' => $sale->id,
                            'sale_number' => $sale->sale_number,
                            'sale_date' => $sale->sale_date->format('Y-m-d H:i'),
                            'total_amount' => $sale->total_amount,
                            'payment_status' => $sale->payment_status,
                            'is_paid' => $sale->payment_status === 'completed',
                            'items' => $sale->items->map(function($item) {
                                return [
                                    'product_name' => $item->product->name ?? 'N/A',
                                    'quantity' => $item->quantity,
                                    'unit_price' => $item->unit_price,
                                    'total_price' => $item->total_price,
                                ];
                            }),
                        ];
                    })->values(),
                    'hasUnpaidBills' => $hasUnpaidBills,
                    'unpaidBillsTotal' => number_format($unpaidBillsTotal, 2),
                    'unpaidBillsCount' => $unpaidPosBills->count(),
                    'key_card' => $keyCard ? [
                        'id' => $keyCard->id,
                        'card_number' => $keyCard->card_number,
                        'card_type' => $keyCard->card_type,
                    ] : null,
                ];
            }),
        ]);
    }

    public function addServiceCharge(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'quantity' => 'nullable|integer|min:1|max:100',
            'department' => 'nullable|string|max:100',
            'charge_date' => 'nullable|date',
        ]);

        $reservation = Reservation::with(['guest', 'room'])->findOrFail($validated['reservation_id']);

        if ($reservation->status !== 'checked_in') {
            return back()->withErrors([
                'description' => 'Service charges can only be added while the guest is checked in.'
            ]);
        }

        $folio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        if (!$folio) {
            $folio = GuestFolio::create([
                'folio_number' => 'FOL-' . str_pad($reservation->id, 6, '0', STR_PAD_LEFT),
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id,
                'status' => 'open',
                'folio_date' => now(),
                'room_charges' => 0,
                'service_charges' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => 0,
            ]);
        }

        $quantity = (int) ($validated['quantity'] ?? 1);
        $unitPrice = (float) $validated['amount'];
        $baseAmount = $unitPrice * $quantity;

        $taxRatePercent = (float) Setting::get('room_tax_rate', Setting::get('tax_rate', 0));
        $taxRate = $taxRatePercent / 100;
        $taxAmount = $baseAmount * $taxRate;
        $netAmount = $baseAmount + $taxAmount;

        FolioCharge::create([
            'guest_folio_id' => $folio->id,
            'charge_code' => 'SERVICE',
            'description' => $validated['description'],
            'charge_date' => !empty($validated['charge_date']) ? Carbon::parse($validated['charge_date'])->toDateString() : now()->toDateString(),
            'charge_time' => now()->format('H:i:s'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $baseAmount,
            'tax_rate' => $taxRatePercent,
            'tax_amount' => $taxAmount,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'net_amount' => $netAmount,
            'reference_type' => 'service',
            'reference_id' => $reservation->id,
            'department' => $validated['department'] ?? 'Front Desk',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        $charges = FolioCharge::where('guest_folio_id', $folio->id)
            ->where('is_voided', false)
            ->get();

        $folio->service_charges = $charges->whereIn('charge_code', ['SERVICE', 'POS'])->sum('net_amount');
        $folio->tax_amount = $charges->sum('tax_amount');
        $folio->total_amount = ($folio->room_charges ?? 0) + $folio->service_charges + $folio->tax_amount - ($folio->discount_amount ?? 0);
        $folio->balance_amount = $folio->total_amount - ($folio->paid_amount ?? 0);
        $folio->save();

        return back()->with('success', 'Service charge added successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,check,mobile_payment,room_charge',
            'outstanding_amount' => 'nullable|numeric|min:0',
            'key_card_returned' => 'nullable|boolean',
            'key_card_id' => 'nullable|exists:key_cards,id',
            'key_card_status' => 'nullable|in:returned,lost,damaged',
            'damages' => 'sometimes|array',
            'damages.*.description' => 'required_with:damages|string|max:255',
            'damages.*.amount' => 'required_with:damages|numeric|min:0',
        ]);

        $reservation = Reservation::with(['guest', 'room', 'roomType'])->findOrFail($validated['reservation_id']);

        // Get or create guest folio to check payment history
        $folio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        if (!$folio) {
            $folio = GuestFolio::create([
                'folio_number' => 'FOL-' . str_pad($reservation->id, 6, '0', STR_PAD_LEFT),
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest ? $reservation->guest_id : null,
                'room_id' => $reservation->room_id,
                'status' => 'open',
                'folio_date' => now(),
                'room_charges' => 0,
                'service_charges' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => 0,
            ]);
        }

        // Check for unpaid POS bills (restaurant/bar bills)
        $unpaidPosBills = Sale::where('reservation_id', $reservation->id)
            ->where('is_charged_to_room', true)
            ->where('payment_status', 'pending')
            ->get();

        $hasUnpaidBills = $unpaidPosBills->count() > 0;

        // If guest has not paid anything yet (paid_amount = 0), ALL bills must be paid at checkout
        if ($folio->paid_amount == 0 && $validated['payment_status'] !== 'paid') {
            return redirect()->back()
                ->withErrors([
                    'payment_status' => 'You have not made any payments yet. All bills (room charges and restaurant/bar charges) must be paid in full at checkout. Please ensure full payment is made.'
                ])
                ->withInput();
        }

        // If there are unpaid POS bills and guest has made payments before, they can still pay at checkout
        // (This allows paying POS bills at checkout even if they've paid other bills separately)
        // But if they haven't paid anything, the above check will catch it

        // Calculate actual nights stayed based on actual check-in and current time,
        // using the rule that a day is 12:00 PM → 12:00 PM next day.
        $actualCheckInRaw = $reservation->actual_check_in ?? $reservation->check_in_date;
        $actualCheckOut = now();

        // Ensure $actualCheckIn is a Carbon instance
        $actualCheckIn = $actualCheckInRaw instanceof Carbon
            ? $actualCheckInRaw
            : Carbon::parse($actualCheckInRaw);

        // Base nights: difference in calendar days between check-in and check-out dates
        $checkInDay = $actualCheckIn->copy()->startOfDay();
        $checkOutDay = $actualCheckOut->copy()->startOfDay();
        $baseNights = $checkInDay->diffInDays($checkOutDay);

        // Always at least 1 night once checked in
        $actualNights = max(1, $baseNights);

        // If stay spans at least one full day and checkout time is after 12:00 PM,
        // charge one extra night (checkout should not cross 12:00 PM)
        $checkoutNoon = $actualCheckOut->copy()->setTime(12, 0, 0);
        if ($baseNights >= 1 && $actualCheckOut->gt($checkoutNoon)) {
            $actualNights++;
        }

        // Get room rate from reservation (use room_rate if available, otherwise calculate from room type)
        $roomRate = $reservation->room_rate ?? 0;
        if (!$roomRate && $reservation->roomType) {
            $roomRate = $reservation->roomType->base_price ?? 0;
        }
        if (!$roomRate && $reservation->room) {
            $roomRate = $reservation->room->roomType->base_price ?? 0;
        }

        // Recalculate room charges based on actual nights
        $actualRoomCharges = $roomRate * $actualNights;

        // Scheduled nights — used in early checkout refund note
        $scheduledNights = Carbon::parse($reservation->check_in_date)->diffInDays(
            Carbon::parse($reservation->check_out_date)
        );

        // Get guest for discount calculation
        $guest = $reservation->guest;

        // Recalculate discounts based on actual room charges
        $totalDiscountAmount = 0;
        $discountReason = '';

        // Guest type discount
        $autoApplyGuestTypeDiscount = \App\Models\Setting::get('auto_apply_guest_type_discount', true);
        if ($autoApplyGuestTypeDiscount && $guest && $guest->guestType && $guest->guestType->is_active) {
            $discountPercentage = $guest->guestType->discount_percentage ?? 0;
            if ($discountPercentage > 0) {
                $guestTypeDiscountAmount = ($actualRoomCharges * $discountPercentage) / 100;
                $totalDiscountAmount += $guestTypeDiscountAmount;
                $discountReason = $guest->guestType->name . ' discount (' . $discountPercentage . '%)';
            }
        }

        // VIP discount
        $autoApplyVipDiscount = \App\Models\Setting::get('auto_apply_vip_discount', true);
        $vipDiscountPercentage = \App\Models\Setting::get('vip_discount_percentage', 0);
        if ($autoApplyVipDiscount && $guest && $guest->is_vip && $vipDiscountPercentage > 0) {
            $vipDiscountAmount = ($actualRoomCharges * $vipDiscountPercentage) / 100;
            $totalDiscountAmount += $vipDiscountAmount;
            if ($discountReason) {
                $discountReason .= ' + VIP discount (' . $vipDiscountPercentage . '%)';
            } else {
                $discountReason = 'VIP discount (' . $vipDiscountPercentage . '%)';
            }
        }

        // Add existing manual discount from reservation
        $manualDiscount = $reservation->discount_amount ?? 0;
        $discountCombinationMode = \App\Models\Setting::get('discount_combination_mode', 'add');
        if ($discountCombinationMode === 'override' && $manualDiscount > 0) {
            $totalDiscountAmount = $manualDiscount;
            $discountReason = $reservation->discount_reason ?? 'Manual discount';
        } else {
            $totalDiscountAmount += $manualDiscount;
        }

        // Calculate taxes and service charges based on actual room charges (after discounts)
        $taxRate = \App\Models\Setting::get('room_tax_rate', \App\Models\Setting::get('tax_rate', 0)) / 100; // Use room_tax_rate if set, fallback to tax_rate
        $serviceChargeRate = \App\Models\Setting::get('service_charge_rate', 0) / 100;

        // Calculate taxable amount (room charges after discounts)
        $taxableRoomCharges = $actualRoomCharges;

        $taxAmount = ($actualRoomCharges - $totalDiscountAmount) * $taxRate;
        $serviceChargeAmount = ($actualRoomCharges - $totalDiscountAmount) * $serviceChargeRate;

        // Ensure folio exists and is updated with all charges
        $folio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        if (!$folio) {
            // Create folio if it doesn't exist
            $folio = GuestFolio::create([
                'folio_number' => 'FOL-' . str_pad($reservation->id, 6, '0', STR_PAD_LEFT),
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id,
                'status' => 'open',
                'folio_date' => now(),
                'room_charges' => 0,
                'service_charges' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => $reservation->paid_amount ?? 0,
                'balance_amount' => 0,
            ]);
        }

        // Add any damage charges submitted at checkout (treated as service charges)
        $damages = $request->input('damages', []);
        if (!empty($damages) && $folio) {
            $damageTaxRatePercent = \App\Models\Setting::get('room_tax_rate', \App\Models\Setting::get('tax_rate', 0));
            $damageTaxRate = $damageTaxRatePercent / 100;

            foreach ($damages as $damage) {
                $amount = isset($damage['amount']) ? (float) $damage['amount'] : 0;
                $description = isset($damage['description']) ? trim($damage['description']) : '';

                if ($amount <= 0 || $description === '') {
                    continue;
                }

                $baseDescription = mb_substr($description, 0, 200);
                $chargeDescription = 'Damage - ' . $baseDescription;

                $taxAmountDamage = $amount * $damageTaxRate;
                $netAmountDamage = $amount + $taxAmountDamage;

                FolioCharge::create([
                    'guest_folio_id' => $folio->id,
                    'charge_code' => 'SERVICE',
                    'description' => $chargeDescription,
                    'charge_date' => now()->toDateString(),
                    'charge_time' => now()->format('H:i:s'),
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_amount' => $amount,
                    'tax_rate' => $damageTaxRatePercent,
                    'tax_amount' => $taxAmountDamage,
                    'discount_rate' => 0,
                    'discount_amount' => 0,
                    'net_amount' => $netAmountDamage,
                    'reference_type' => 'damage',
                    'reference_id' => $reservation->id,
                    'department' => 'Housekeeping',
                    'posted_by' => auth()->check() ? auth()->id() : null,
                    'posted_at' => now(),
                ]);
            }
        }

        // Get existing service charges and POS charges from folio charges (including newly added damages)
        $allCharges = $folio ? FolioCharge::where('guest_folio_id', $folio->id)
            ->where('is_voided', false)
            ->get() : collect();

        $existingServiceCharges = $allCharges->where('charge_code', 'SERVICE')->sum('net_amount');
        $existingPosCharges = $allCharges->where('charge_code', 'POS')->sum('net_amount');
        // Only include per-charge taxes when tax rate is non-zero (prevents stale taxes when rate is 0%)
        $existingTaxFromCharges = $taxRate > 0 ? $allCharges->sum('tax_amount') : 0;

        // Update folio summary fields with recalculated amounts
        $folio->room_charges = $actualRoomCharges;
        $folio->service_charges = $existingServiceCharges + $existingPosCharges;
        $folio->tax_amount = $taxAmount + $existingTaxFromCharges;
        $folio->discount_amount = $totalDiscountAmount;

        // Calculate total amount
        $folio->total_amount = $folio->room_charges + $folio->service_charges + $folio->tax_amount - $folio->discount_amount;

        $previousPaid = (float) ($folio->paid_amount ?? 0);
        $totalAmount = (float) $folio->total_amount;
        $paymentStatus = $validated['payment_status'];
        $paymentMethod = $validated['payment_method'] ?? 'cash';
        $outstandingAmount = (float) ($validated['outstanding_amount'] ?? 0);

        // Handle early checkout refund: guest paid more than the recalculated total
        $refundAmount = 0;
        if ($previousPaid > $totalAmount && $totalAmount > 0) {
            $refundAmount = round($previousPaid - $totalAmount, 2);
            // Record a refund payment (negative amount)
            Payment::create([
                'payment_number' => 'REF-' . strtoupper(Str::random(8)),
                'guest_folio_id' => $folio->id,
                'reservation_id' => $reservation->id,
                'payment_method' => $paymentMethod,
                'amount' => -$refundAmount,
                'currency' => Setting::get('currency', 'USD'),
                'exchange_rate' => 1,
                'local_amount' => -$refundAmount,
                'status' => 'completed',
                'processed_at' => now(),
                'processed_by' => auth()->check() ? auth()->id() : null,
                'notes' => 'Early checkout refund – ' . ($actualNights ?? 0) . ' night(s) charged instead of ' . ($scheduledNights ?? 0),
            ]);
            $folio->paid_amount = $totalAmount;
            $folio->balance_amount = 0;
            $folio->save();
        } else {
            if ($paymentStatus === 'paid') {
                $outstandingAmount = 0;
            } elseif ($paymentStatus === 'pending') {
                $outstandingAmount = max($totalAmount - $previousPaid, 0);
            }

            $maxOutstanding = max($totalAmount - $previousPaid, 0);
            $outstandingAmount = min($outstandingAmount, $maxOutstanding);
            $additionalPayment = max($totalAmount - $outstandingAmount - $previousPaid, 0);

            if ($additionalPayment > 0) {
                Payment::create([
                    'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
                    'guest_folio_id' => $folio->id,
                    'reservation_id' => $reservation->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $additionalPayment,
                    'currency' => Setting::get('currency', 'USD'),
                    'exchange_rate' => 1,
                    'local_amount' => $additionalPayment,
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processed_by' => auth()->check() ? auth()->id() : null,
                    'notes' => 'Checkout payment',
                ]);
            }

            $folio->paid_amount = $previousPaid + $additionalPayment;
            $folio->balance_amount = $totalAmount - $folio->paid_amount;
            $folio->save();
        }

        // Update reservation with actual checkout time and recalculated amounts
        $reservation->update([
            'status' => 'checked_out',
            'actual_check_out' => $actualCheckOut,
            'checked_out_by' => auth()->check() ? auth()->id() : null,
            'nights' => $actualNights,
            'total_room_charges' => $actualRoomCharges,
            'discount_amount' => $totalDiscountAmount,
            'discount_reason' => $discountReason ?: $reservation->discount_reason,
            'taxes' => $taxAmount,
            'service_charges' => $serviceChargeAmount,
            'total_amount' => $folio->total_amount,
            'paid_amount' => $folio->paid_amount,
            'balance_amount' => $folio->balance_amount,
        ]);

        // Store refund amount in session for the receipt
        session(['checkout_refund_amount_' . $reservation->id => $refundAmount]);

        if ($reservation->room) {
            $room = $reservation->room;
            $room->update([
                'status' => 'cleaning', // Room needs cleaning before becoming available
                'housekeeping_status' => 'dirty', // Room needs cleaning after checkout
            ]);

            // Find if there's an active schedule for this room
            $activeSchedule = HousekeepingSchedule::where('status', 'active')
                ->where('start_date', '<=', now()->toDateString())
                ->where('end_date', '>=', now()->toDateString())
                ->get()
                ->filter(function($schedule) use ($room) {
                    $roomNumbers = $schedule->room_numbers ?? [];
                    return in_array($room->room_number, $roomNumbers);
                })
                ->first();

            // Create housekeeping cleaning task (not 'checkout' - checkout is an event, cleaning is the task)
            $task = HousekeepingTask::create([
                'room_id' => $room->id,
                'assigned_to' => $activeSchedule ? $activeSchedule->assigned_to : null,
                'task_type' => 'cleaning',
                'priority' => 'high',
                'status' => 'pending',
                'scheduled_date' => now()->toDateString(),
                'scheduled_time' => $activeSchedule?->preferred_start_time ?? now()->toTimeString(),
                'instructions' => "Room {$room->room_number} checked out. Full cleaning required.",
            ]);

            // Notify assigned housekeeper if schedule exists
            if ($activeSchedule) {
                HousekeepingNotification::create([
                    'user_id' => $activeSchedule->assigned_to,
                    'room_id' => $room->id,
                    'reservation_id' => $reservation->id,
                    'housekeeping_task_id' => $task->id,
                    'type' => 'checkout',
                    'title' => 'Room Checkout - Cleaning Required',
                    'message' => "Room {$room->room_number} has been checked out and requires cleaning. This room is part of your assigned schedule.",
                    'priority' => 'high',
                    'metadata' => [
                        'room_number' => $room->room_number,
                        'schedule_id' => $activeSchedule->id,
                        'task_id' => $task->id,
                    ],
                ]);
            } else {
                // Notify all housekeepers if no schedule
                $housekeepers = \App\Models\User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['housekeeping', 'front_desk_manager']);
                })->get();

                foreach ($housekeepers as $housekeeper) {
                    HousekeepingNotification::create([
                        'user_id' => $housekeeper->id,
                        'room_id' => $room->id,
                        'reservation_id' => $reservation->id,
                        'housekeeping_task_id' => $task->id,
                        'type' => 'checkout',
                        'title' => 'Room Checkout - Cleaning Required',
                        'message' => "Room {$room->room_number} has been checked out and requires cleaning.",
                        'priority' => 'high',
                        'metadata' => [
                            'room_number' => $room->room_number,
                            'task_id' => $task->id,
                        ],
                    ]);
                }
            }
        }

        // Mark all pending POS sales as completed (paid at checkout)
        if ($hasUnpaidBills && $paymentStatus !== 'pending') {
            Sale::where('reservation_id', $reservation->id)
                ->where('is_charged_to_room', true)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'completed',
                    'payment_method' => 'room_charge'
                ]);
        }

            // Handle key card return
            if (!empty($validated['key_card_id'])) {
                $keyCard = KeyCard::findOrFail($validated['key_card_id']);

                if ($validated['key_card_status'] === 'returned') {
                    $keyCard->returnCard(auth()->check() ? auth()->id() : null);
                } elseif ($validated['key_card_status'] === 'lost') {
                    $keyCard->markAsLost();
                } elseif ($validated['key_card_status'] === 'damaged') {
                    $keyCard->markAsDamaged();
                }
            } else {
                // Auto-return key cards for this reservation
                $keyCards = KeyCard::where('reservation_id', $reservation->id)
                    ->where('status', 'assigned')
                    ->get();

                foreach ($keyCards as $keyCard) {
                    $keyCard->returnCard(auth()->check() ? auth()->id() : null);
                }
            }

        // Redirect to print receipt (A4 front desk) so user can print the bill
        $user = auth()->user();
        $printRoute = 'front-desk.checkout.print';
        if ($user->hasRole('admin')) {
            $printRoute = 'admin.checkout.print';
        } elseif ($user->hasRole('manager')) {
            $printRoute = 'manager.checkout.print';
        }
        return redirect()->route($printRoute, ['reservation_id' => $reservation->id])
            ->with('success', 'Guest checked out successfully. Print the bill below.');
    }

    /**
     * Print checkout bill (A4 front desk receipt). Only for checked_out reservations.
     */
    public function printReceipt(Request $request)
    {
        $reservationId = $request->query('reservation_id');
        if (!$reservationId) {
            return redirect()->back()->withErrors(['reservation_id' => 'Reservation ID required.']);
        }

        $reservation = Reservation::with(['guest', 'room', 'roomType'])
            ->find($reservationId);

        if (!$reservation || $reservation->status !== 'checked_out') {
            return redirect()->back()->withErrors(['reservation' => 'Reservation not found or not yet checked out.']);
        }

        $folio = GuestFolio::where('reservation_id', $reservation->id)->first();
        $allCharges = $folio ? FolioCharge::where('guest_folio_id', $folio->id)
            ->where('is_voided', false)
            ->orderBy('charge_date')->orderBy('charge_time')
            ->get() : collect();
        $posSales = Sale::where('reservation_id', $reservation->id)
            ->where('is_charged_to_room', true)
            ->with(['items.product'])
            ->get();

        $serviceCharges = $allCharges->where('charge_code', 'SERVICE')->sum('net_amount');
        $posCharges = $allCharges->where('charge_code', 'POS')->sum('net_amount');
        $roomCharges = $folio ? ($folio->room_charges ?? 0) : ($reservation->total_room_charges ?? 0);
        $taxAmount = $folio ? ($folio->tax_amount ?? 0) : ($reservation->taxes ?? 0);
        $discountAmount = $folio ? ($folio->discount_amount ?? 0) : ($reservation->discount_amount ?? 0);
        $totalAmount = $folio ? ($folio->total_amount ?? 0) : ($reservation->total_amount ?? 0);
        $paidAmount = $folio ? ($folio->paid_amount ?? 0) : ($reservation->paid_amount ?? 0);
        $balanceAmount = $folio ? ($folio->balance_amount ?? 0) : ($reservation->balance_amount ?? 0);

        $bill = [
            'reservation_number' => $reservation->reservation_number,
            'guest_name' => $reservation->guest->full_name ?? 'N/A',
            'room_number' => $reservation->room->room_number ?? 'N/A',
            'check_in_date' => $reservation->check_in_date?->format('Y-m-d'),
            'check_out_date' => $reservation->check_out_date?->format('Y-m-d'),
            'actual_check_in' => $reservation->actual_check_in?->format('Y-m-d H:i'),
            'actual_check_out' => $reservation->actual_check_out?->format('Y-m-d H:i'),
            'nights' => $reservation->nights ?? 0,
            'room_charges' => $roomCharges,
            'service_charges' => $serviceCharges,
            'pos_charges' => $posCharges,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balanceAmount,
            'folio_number' => $folio->folio_number ?? null,
            'charges' => $allCharges->map(fn($c) => [
                'charge_code' => $c->charge_code,
                'description' => $c->description,
                'charge_date' => $c->charge_date->format('Y-m-d'),
                'charge_time' => $c->charge_time?->format('H:i'),
                'quantity' => $c->quantity,
                'unit_price' => $c->unit_price,
                'net_amount' => $c->net_amount,
            ])->values(),
            'pos_sales' => $posSales->map(fn($s) => [
                'sale_number' => $s->sale_number,
                'sale_date' => $s->sale_date?->format('Y-m-d H:i'),
                'total_amount' => $s->total_amount,
                'items' => $s->items->map(fn($i) => [
                    'product_name' => $i->product->name ?? 'N/A',
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'total_price' => $i->total_price,
                ]),
            ])->values(),
            'refund_amount' => session('checkout_refund_amount_' . $reservation->id, 0),
        ];

        $user = auth()->user();
        $role = 'front_desk';
        if ($user->hasRole('admin')) $role = 'admin';
        elseif ($user->hasRole('manager')) $role = 'manager';

        $receiptSize = Setting::get('receipt_size_front_desk', 'A4');
        $refundAmount = session('checkout_refund_amount_' . $reservation->id, 0);

        return Inertia::render('CheckOut/Print', [
            'user' => $user->load('roles'),
            'bill' => $bill,
            'role' => $role,
            'receiptSize' => $receiptSize,
            'hotelName' => Setting::get('hotel_name', 'Hotel'),
            'hotelAddress' => Setting::get('hotel_address', ''),
            'hotelPhone' => Setting::get('hotel_phone', ''),
            'hotelEmail' => Setting::get('hotel_email', ''),
            'hotelLogo' => Setting::get('hotel_logo', ''),
            'hotelWebsite' => Setting::get('hotel_website', ''),
            'refundAmount' => $refundAmount,
        ]);
    }
}
