<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaundryOrder;
use App\Models\LaundryItem;
use App\Models\Guest;
use App\Models\Room;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LaundryController extends Controller
{
    public function index(Request $request)
    {
        $query = LaundryOrder::with(['guest', 'room', 'items'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('guest', fn($g) => $g->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%'));
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $stats = [
            'total'       => LaundryOrder::count(),
            'pending'     => LaundryOrder::where('status', 'pending')->count(),
            'in_progress' => LaundryOrder::where('status', 'in_progress')->count(),
            'ready'       => LaundryOrder::where('status', 'ready')->count(),
            'delivered'   => LaundryOrder::where('status', 'delivered')->count(),
            'revenue'     => LaundryOrder::whereIn('status', ['delivered', 'ready'])->sum('total_amount'),
        ];

        return Inertia::render('Admin/Laundry/Index', [
            'orders'  => $orders,
            'stats'   => $stats,
            'filters' => $request->only(['status', 'priority', 'search']),
        ]);
    }

    public function create()
    {
        // Only guests who currently occupy a room (checked-in reservations)
        $checkedInGuests = Guest::select('guests.id', 'guests.first_name', 'guests.last_name', 'guests.guest_id',
                                        'rooms.id as room_id', 'rooms.room_number')
            ->join('reservations', 'reservations.guest_id', '=', 'guests.id')
            ->join('rooms', 'rooms.id', '=', 'reservations.room_id')
            ->where('reservations.status', 'checked_in')
            ->orderBy('guests.first_name')
            ->get()
            ->map(fn($g) => [
                'id'          => $g->id,
                'first_name'  => $g->first_name,
                'last_name'   => $g->last_name,
                'guest_id'    => $g->guest_id,
                'room_id'     => $g->room_id,
                'room_number' => $g->room_number,
            ]);

        return Inertia::render('Admin/Laundry/Create', [
            'guests' => $checkedInGuests,
            'rooms'  => Room::select('id', 'room_number')->orderBy('room_number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id'             => 'nullable|exists:guests,id',
            'room_id'              => 'nullable|exists:rooms,id',
            'priority'             => 'required|in:normal,express,overnight',
            'pickup_date'          => 'required|date',
            'delivery_date'        => 'required|date|after_or_equal:pickup_date',
            'pickup_time'          => 'nullable|string',
            'delivery_time'        => 'nullable|string',
            'payment_status'       => 'required|in:unpaid,paid,billed_to_room',
            'special_instructions' => 'nullable|string',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.item_name'    => 'required|string',
            'items.*.service_type' => 'required|in:wash,dry_clean,iron,wash_iron,dry_clean_iron',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.notes'        => 'nullable|string',
        ]);

        $order = LaundryOrder::create([
            'order_number'         => LaundryOrder::generateOrderNumber(),
            'guest_id'             => $validated['guest_id'],
            'room_id'              => $validated['room_id'],
            'user_id'              => auth()->id(),
            'status'               => 'pending',
            'priority'             => $validated['priority'],
            'pickup_date'          => $validated['pickup_date'],
            'delivery_date'        => $validated['delivery_date'],
            'pickup_time'          => $validated['pickup_time'] ?? null,
            'delivery_time'        => $validated['delivery_time'] ?? null,
            'payment_status'       => $validated['payment_status'],
            'special_instructions' => $validated['special_instructions'] ?? null,
            'notes'                => $validated['notes'] ?? null,
            'subtotal'             => 0,
            'express_fee'          => 0,
            'total_amount'         => 0,
        ]);

        foreach ($validated['items'] as $item) {
            $total = $item['quantity'] * $item['unit_price'];
            $order->items()->create([
                'item_name'    => $item['item_name'],
                'service_type' => $item['service_type'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'total_price'  => $total,
                'notes'        => $item['notes'] ?? null,
            ]);
        }

        $order->recalculateTotals();

        return redirect()->route('admin.laundry.index')->with('success', 'Laundry order created successfully.');
    }

    public function show(LaundryOrder $laundry)
    {
        $laundry->load(['guest', 'room', 'items', 'staff']);
        return Inertia::render('Admin/Laundry/Show', ['order' => $laundry]);
    }

    public function updateStatus(Request $request, LaundryOrder $laundry)
    {
        $request->validate(['status' => 'required|in:pending,picked_up,in_progress,ready,delivered,cancelled']);
        $laundry->update(['status' => $request->status]);

        // Post to folio when delivered and billed_to_room (if not already posted)
        if ($request->status === 'delivered' && $laundry->payment_status === 'billed_to_room') {
            $this->billToFolio($laundry);
        }

        return back()->with('success', 'Order status updated.');
    }

    public function destroy(LaundryOrder $laundry)
    {
        $laundry->delete();
        return redirect()->route('admin.laundry.index')->with('success', 'Laundry order deleted.');
    }

    private function billToFolio(LaundryOrder $order): void
    {
        // Skip if already posted (avoid duplicate charges)
        $alreadyPosted = FolioCharge::where('reference_type', 'laundry_order')
            ->where('reference_id', $order->id)
            ->exists();
        if ($alreadyPosted) return;

        // Find the guest's open folio, prefer matching room
        $folio = null;
        if ($order->room_id) {
            $folio = GuestFolio::where('room_id', $order->room_id)
                ->where('status', 'open')
                ->latest()
                ->first();
        }
        if (!$folio && $order->guest_id) {
            $folio = GuestFolio::where('guest_id', $order->guest_id)
                ->where('status', 'open')
                ->latest()
                ->first();
        }
        if (!$folio) return;

        $charge = FolioCharge::create([
            'guest_folio_id' => $folio->id,
            'charge_code'    => 'LAUNDRY',
            'description'    => 'Laundry Service - Order ' . $order->order_number,
            'charge_date'    => now()->toDateString(),
            'charge_time'    => now(),
            'quantity'       => 1,
            'unit_price'     => $order->total_amount,
            'total_amount'   => $order->total_amount,
            'tax_rate'       => 0,
            'tax_amount'     => 0,
            'discount_rate'  => 0,
            'discount_amount'=> 0,
            'net_amount'     => $order->total_amount,
            'reference_type' => 'laundry_order',
            'reference_id'   => $order->id,
            'department'     => 'Laundry',
            'posted_by'      => auth()->id(),
            'posted_at'      => now(),
            'is_voided'      => false,
        ]);

        // Recalculate folio totals
        $folio->service_charges = $folio->charges()->notVoided()
            ->where('charge_code', '!=', 'ROOM')->sum('net_amount');
        $folio->total_amount    = $folio->room_charges + $folio->service_charges + $folio->tax_amount - $folio->discount_amount;
        $folio->balance_amount  = $folio->total_amount - $folio->paid_amount;
        $folio->save();
    }
}
