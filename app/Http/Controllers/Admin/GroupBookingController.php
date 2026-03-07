<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Models\Guest;
use App\Models\GroupBooking;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupBookingController extends Controller
{
    protected function generateGroupNumber(): string
    {
        return 'GB-' . now()->format('Ymd-His') . '-' . random_int(1000, 9999);
    }

    /**
     * Display a listing of the group bookings.
     */
    public function index()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $groupBookings = GroupBooking::with(['reservations', 'packages', 'halls'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $allGroups = GroupBooking::query()->get(['id', 'status']);
        $stats = [
            'total' => $allGroups->count(),
            'pending' => $allGroups->where('status', 'pending')->count(),
            'confirmed' => $allGroups->where('status', 'confirmed')->count(),
            'checked_in' => $allGroups->where('status', 'checked_in')->count(),
        ];

        return Inertia::render('Admin/GroupBookings/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'groupBookings' => $groupBookings,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new group booking.
     */
    public function create()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $guests = Guest::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $halls = \App\Models\Hall::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'capacity', 'base_price']);

        $packages = \App\Models\Package::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price']);

        return Inertia::render('Admin/GroupBookings/Create', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'guests' => $guests,
            'halls' => $halls,
            'packages' => $packages,
        ]);
    }

    /**
     * Store a newly created group booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'primary_guest_id' => 'required|exists:guests,id',
            'contact_person_id' => 'nullable|exists:guests,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'total_rooms' => 'required|integer|min:1',
            'total_adults' => 'required|integer|min:1',
            'total_children' => 'nullable|integer|min:0',
            'group_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'billing_type' => 'required|in:consolidated,individual,split',
            'billing_instructions' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'group_notes' => 'nullable|string',
            'hall_ids' => 'nullable|array',
            'hall_ids.*' => 'exists:halls,id',
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        $totalChildren = (int) ($validated['total_children'] ?? 0);
        $totalAdults = (int) $validated['total_adults'];
        $totalGuests = $totalAdults + $totalChildren;

        $groupBooking = GroupBooking::create([
            'group_number' => $this->generateGroupNumber(),
            'group_name' => $validated['group_name'],
            'primary_guest_id' => $validated['primary_guest_id'],
            'contact_person_id' => $validated['contact_person_id'] ?? null,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'total_rooms' => $validated['total_rooms'],
            'total_guests' => $totalGuests,
            'total_adults' => $totalAdults,
            'total_children' => $totalChildren,
            'group_discount_percentage' => $validated['group_discount_percentage'] ?? 0,
            'group_discount_amount' => 0,
            'total_group_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 0,
            'billing_type' => $validated['billing_type'],
            'billing_instructions' => $validated['billing_instructions'] ?? null,
            'special_requests' => $validated['special_requests'] ?? null,
            'group_notes' => $validated['group_notes'] ?? null,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        if (!empty($validated['hall_ids'])) {
            $groupBooking->halls()->sync($validated['hall_ids']);
        }

        if (!empty($validated['package_ids'])) {
            $groupBooking->packages()->sync($validated['package_ids']);
        }

        return redirect()->route('admin.group-bookings.index')
            ->with('success', 'Group booking created successfully.');
    }

    /**
     * Display the specified group booking.
     */
    public function show(GroupBooking $groupBooking)
    {
        $groupBooking->load(['reservations.guest', 'packages', 'halls']);

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        return Inertia::render('Admin/GroupBookings/Show', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'groupBooking' => $groupBooking,
        ]);
    }

    /**
     * Show the form for editing the specified group booking.
     */
    public function edit(GroupBooking $groupBooking)
    {
        $groupBooking->load(['halls', 'packages']);

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        return Inertia::render('Admin/GroupBookings/Edit', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'groupBooking' => $groupBooking,
        ]);
    }

    /**
     * Update the specified group booking.
     */
    public function update(Request $request, GroupBooking $groupBooking)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'primary_guest_id' => 'required|exists:guests,id',
            'contact_person_id' => 'nullable|exists:guests,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'total_rooms' => 'required|integer|min:1',
            'total_adults' => 'required|integer|min:1',
            'total_children' => 'nullable|integer|min:0',
            'group_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'billing_type' => 'required|in:consolidated,individual,split',
            'billing_instructions' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'group_notes' => 'nullable|string',
            'hall_ids' => 'nullable|array',
            'hall_ids.*' => 'exists:halls,id',
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
        ]);

        $totalChildren = (int) ($validated['total_children'] ?? 0);
        $totalAdults = (int) $validated['total_adults'];
        $totalGuests = $totalAdults + $totalChildren;

        $groupBooking->update([
            'group_name' => $validated['group_name'],
            'primary_guest_id' => $validated['primary_guest_id'],
            'contact_person_id' => $validated['contact_person_id'] ?? null,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'total_rooms' => $validated['total_rooms'],
            'total_guests' => $totalGuests,
            'total_adults' => $totalAdults,
            'total_children' => $totalChildren,
            'group_discount_percentage' => $validated['group_discount_percentage'] ?? 0,
            'billing_type' => $validated['billing_type'],
            'billing_instructions' => $validated['billing_instructions'] ?? null,
            'special_requests' => $validated['special_requests'] ?? null,
            'group_notes' => $validated['group_notes'] ?? null,
            'status' => $validated['status'],
            'updated_by' => auth()->id(),
        ]);

        if ($request->has('hall_ids')) {
            $groupBooking->halls()->sync($validated['hall_ids']);
        }

        if ($request->has('package_ids')) {
            $groupBooking->packages()->sync($validated['package_ids']);
        }

        return redirect()->route('admin.group-bookings.index')
            ->with('success', 'Group booking updated successfully.');
    }

    /**
     * Remove the specified group booking.
     */
    public function destroy(GroupBooking $groupBooking)
    {
        $groupBooking->delete();

        return redirect()->route('admin.group-bookings.index')
            ->with('success', 'Group booking deleted successfully.');
    }

    /**
     * Get group booking details.
     */
    public function details(GroupBooking $groupBooking)
    {
        $groupBooking->load(['reservations.guest', 'packages', 'halls']);

        return response()->json([
            'groupBooking' => $groupBooking,
            'totalGuests' => $groupBooking->reservations->sum('adults'),
            'totalRevenue' => $groupBooking->reservations->sum('total_amount'),
        ]);
    }

    /**
     * Get group booking invoices.
     */
    public function invoices(GroupBooking $groupBooking)
    {
        $reservations = $groupBooking->reservations()->with('payments')->get();

        return response()->json([
            'invoices' => $reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'total_amount' => $reservation->total_amount,
                    'paid_amount' => $reservation->payments->sum('amount'),
                    'balance' => $reservation->total_amount - $reservation->payments->sum('amount'),
                    'status' => $reservation->status,
                ];
            }),
        ]);
    }

    /**
     * Process payment for group booking.
     */
    public function processPayment(Request $request, GroupBooking $groupBooking)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        // Create payment records for all reservations in the group
        foreach ($groupBooking->reservations as $reservation) {
            $reservation->payments()->create([
                'amount' => $validated['amount'] / $groupBooking->reservations->count(),
                'payment_method' => $validated['payment_method'],
                'payment_date' => now(),
                'status' => 'completed',
                'notes' => $validated['notes'] ?? 'Group booking payment',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Payment processed successfully.');
    }
}
