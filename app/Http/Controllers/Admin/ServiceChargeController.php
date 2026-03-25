<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use App\Models\HotelService;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ServiceChargeController extends Controller
{
    /**
     * Display service charges for a reservation
     */
    public function index(Request $request, Reservation $reservation)
    {
        $user = auth()->user();
        $role = $user->roles->first()?->name ?? 'admin';
        $routePrefix = match($role) {
            'manager'    => 'manager',
            'front_desk' => 'front-desk',
            default      => 'admin',
        };

        $folio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        if (!$folio) {
            // Create folio if it doesn't exist
            $folio = $this->createFolio($reservation);
        }

        $charges = FolioCharge::where('guest_folio_id', $folio->id)
            ->where('is_voided', false)
            ->orderBy('charge_date', 'desc')
            ->orderBy('charge_time', 'desc')
            ->with('postedBy')
            ->get();

        $services = HotelService::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/ServiceCharges/Index', [
            'void_route_name'  => $routePrefix . '.folio-charges.void',
            'store_route_name' => $routePrefix . '.reservations.service-charges.store',
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'guest' => $reservation->guest->full_name ?? 'N/A',
                'room' => $reservation->room->room_number ?? 'N/A',
            ],
            'folio' => [
                'id' => $folio->id,
                'folio_number' => $folio->folio_number,
                'total_amount' => $folio->total_amount,
                'paid_amount' => $folio->paid_amount,
                'balance_amount' => $folio->balance_amount,
            ],
            'charges' => $charges->map(fn($charge) => [
                'id' => $charge->id,
                'charge_code' => $charge->charge_code,
                'description' => $charge->description,
                'charge_date' => $charge->charge_date->format('Y-m-d'),
                'charge_time' => $charge->charge_time?->format('H:i'),
                'quantity' => $charge->quantity,
                'unit_price' => $charge->unit_price,
                'net_amount' => $charge->net_amount,
                'posted_by' => $charge->postedBy?->full_name ?? 'N/A',
                'posted_at' => $charge->posted_at->format('Y-m-d H:i'),
            ]),
            'services' => $services->map(fn($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'description' => $service->description,
                'price' => $service->price,
                'is_free' => $service->is_free,
                'pricing_type' => $service->pricing_type,
            ]),
        ]);
    }

    /**
     * Add a service charge to a folio
     */
    public function store(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'hotel_service_id' => 'required|exists:hotel_services,id',
            'quantity' => 'required|numeric|min:0.01',
            'charge_date' => 'required|date',
            'charge_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $service = HotelService::findOrFail($validated['hotel_service_id']);

        // Get or create folio
        $folio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        if (!$folio) {
            $folio = $this->createFolio($reservation);
        }

        // Calculate amounts
        $unitPrice = $service->is_free ? 0 : $service->price;
        $totalAmount = $unitPrice * $validated['quantity'];
        
        // Get tax rate from settings
        $taxRate = \App\Models\Setting::get('tax_rate', 0) / 100;
        $taxAmount = $totalAmount * $taxRate;
        $netAmount = $totalAmount + $taxAmount;

        // Create folio charge
        $charge = FolioCharge::create([
            'guest_folio_id' => $folio->id,
            'charge_code' => 'SERVICE',
            'description' => $service->name . ($validated['notes'] ? ' - ' . $validated['notes'] : ''),
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
            'department' => 'Front Office',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        // Update folio totals
        $this->updateFolioTotals($folio);

        return redirect()->back()->with('success', 'Service charge added successfully');
    }

    /**
     * Create a guest folio for a reservation
     */
    private function createFolio(Reservation $reservation)
    {
        // Generate unique folio number
        $folioNumber = 'FOL' . strtoupper(uniqid());
        while (GuestFolio::where('folio_number', $folioNumber)->exists()) {
            $folioNumber = 'FOL' . strtoupper(uniqid());
        }

        return GuestFolio::create([
            'folio_number' => $folioNumber,
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $reservation->room_id ?? null,
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

    /**
     * Update folio totals based on charges
     */
    private function updateFolioTotals(GuestFolio $folio)
    {
        $charges = FolioCharge::where('guest_folio_id', $folio->id)
            ->where('is_voided', false)
            ->get();

        $serviceCharges = $charges->where('charge_code', 'SERVICE')->sum('net_amount');
        $roomCharges = $folio->room_charges ?? 0;
        $taxAmount = $charges->sum('tax_amount');
        $totalAmount = $roomCharges + $serviceCharges + $taxAmount - ($folio->discount_amount ?? 0);
        $balanceAmount = $totalAmount - ($folio->paid_amount ?? 0);

        $folio->update([
            'service_charges' => $serviceCharges,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount,
        ]);
    }

    /**
     * Void a charge
     */
    public function void(Request $request, FolioCharge $charge)
    {
        $validated = $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        $charge->update([
            'is_voided' => true,
            'voided_by' => auth()->id(),
            'voided_at' => now(),
            'void_reason' => $validated['void_reason'],
        ]);

        // Update folio totals
        $this->updateFolioTotals($charge->folio);

        return redirect()->back()->with('success', 'Charge voided successfully');
    }
}
