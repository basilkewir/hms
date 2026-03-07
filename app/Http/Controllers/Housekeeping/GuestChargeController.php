<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use App\Models\HotelService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestChargeController extends Controller
{
    /**
     * Show form to add a charge (cleaning, car wash, etc.) to a checked-in guest.
     */
    public function create(Request $request)
    {
        $reservations = Reservation::with(['guest', 'room', 'roomType'])
            ->where('status', 'checked_in')
            ->orderBy('check_out_date')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'reservation_number' => $r->reservation_number,
                'guest_name' => $r->guest->full_name ?? 'N/A',
                'room_number' => $r->room->room_number ?? 'N/A',
            ]);

        $services = HotelService::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'category' => $s->category,
                'price' => (float) $s->price,
                'is_free' => (bool) $s->is_free,
            ]);

        return Inertia::render('Housekeeping/GuestCharges/Add', [
            'user' => auth()->user()->load('roles'),
            'reservations' => $reservations,
            'services' => $services,
            'selectedReservationId' => $request->query('reservation_id'),
        ]);
    }

    /**
     * Store a service charge (cleaning, car wash, etc.) for a checked-in guest.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'hotel_service_id' => 'required|exists:hotel_services,id',
            'quantity' => 'required|numeric|min:0.01',
            'charge_date' => 'required|date',
            'charge_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($validated['reservation_id']);
        if ($reservation->status !== 'checked_in') {
            return redirect()->back()->withErrors(['reservation_id' => 'Guest must be checked in to add charges.']);
        }

        $service = HotelService::findOrFail($validated['hotel_service_id']);

        $folio = GuestFolio::where('reservation_id', $reservation->id)->where('status', 'open')->first();
        if (!$folio) {
            $folio = GuestFolio::create([
                'folio_number' => 'FOL-' . str_pad($reservation->id, 6, '0', STR_PAD_LEFT),
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id ?? 0,
                'status' => 'open',
                'folio_date' => now()->toDateString(),
                'room_charges' => $reservation->total_room_charges ?? 0,
                'service_charges' => 0,
                'tax_amount' => $reservation->taxes ?? 0,
                'discount_amount' => $reservation->discount_amount ?? 0,
                'total_amount' => $reservation->total_amount ?? 0,
                'paid_amount' => $reservation->paid_amount ?? 0,
                'balance_amount' => $reservation->balance_amount ?? 0,
            ]);
        }

        $unitPrice = $service->is_free ? 0 : $service->price;
        $totalAmount = $unitPrice * $validated['quantity'];
        $taxRate = \App\Models\Setting::get('tax_rate', 0) / 100;
        $taxAmount = $totalAmount * $taxRate;
        $netAmount = $totalAmount + $taxAmount;

        $description = $service->name . ($validated['notes'] ? ' - ' . $validated['notes'] : '');

        FolioCharge::create([
            'guest_folio_id' => $folio->id,
            'charge_code' => 'SERVICE',
            'description' => $description,
            'charge_date' => $validated['charge_date'],
            'charge_time' => $validated['charge_time'] ? now()->parse($validated['charge_time'])->format('H:i:s') : now()->format('H:i:s'),
            'quantity' => $validated['quantity'],
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'tax_rate' => $taxRate * 100,
            'tax_amount' => $taxAmount,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'net_amount' => $netAmount,
            'reference_type' => 'hotel_service',
            'reference_id' => $service->id,
            'department' => 'Housekeeping',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        $this->updateFolioTotals($folio);

        return redirect()->back()->with('success', "Charge \"{$service->name}\" added to guest folio.");
    }

    private function updateFolioTotals(GuestFolio $folio): void
    {
        $charges = FolioCharge::where('guest_folio_id', $folio->id)->where('is_voided', false)->get();
        $serviceCharges = $charges->where('charge_code', 'SERVICE')->sum('net_amount');
        $posCharges = $charges->where('charge_code', 'POS')->sum('net_amount');
        $roomCharges = $folio->room_charges ?? 0;
        $taxAmount = $charges->sum('tax_amount');
        $totalAmount = $roomCharges + $serviceCharges + $posCharges + $taxAmount - ($folio->discount_amount ?? 0);
        $balanceAmount = $totalAmount - ($folio->paid_amount ?? 0);

        $folio->update([
            'service_charges' => $serviceCharges + $posCharges,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount,
        ]);
    }
}
