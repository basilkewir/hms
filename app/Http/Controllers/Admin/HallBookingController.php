<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Requests\Admin\HallBookingRequest;
use App\Models\Guest;
use App\Models\Hall;
use App\Models\HallBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HallBookingController extends Controller
{
    protected function generateBookingNumber(): string
    {
        return 'HB-' . now()->format('Ymd-His') . '-' . random_int(1000, 9999);
    }

    protected function computeTotalAmount(Hall $hall, string $startTime, string $endTime): float
    {
        $start   = Carbon::createFromTimeString($startTime);
        $end     = Carbon::createFromTimeString($endTime);
        $minutes = $start->diffInMinutes($end, false);
        if ($minutes <= 0) {
            $minutes += 24 * 60;
        }
        $hours = $minutes / 60;
        return round((float) $hall->base_price * $hours, 2);
    }

    public function index(Request $request)
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $query = HallBooking::query()->with(['hall', 'guest'])->orderByDesc('event_date');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->string('date'));
        }

        $bookings = $query->paginate(10)->withQueryString();

        $allBookings = HallBooking::query()->get(['id', 'status']);
        $stats = [
            'total' => $allBookings->count(),
            'pending' => $allBookings->where('status', 'pending')->count(),
            'confirmed' => $allBookings->where('status', 'confirmed')->count(),
            'cancelled' => $allBookings->where('status', 'cancelled')->count(),
            'completed' => $allBookings->where('status', 'completed')->count(),
        ];

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/HallBookings/Index', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'bookings'    => $bookings,
            'stats'       => $stats,
            'routePrefix' => $routePrefix,
            'filters'     => [
                'status' => $request->get('status'),
                'date'   => $request->get('date'),
            ],
        ]);
    }

    public function create()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $halls = Hall::query()->orderBy('name')->get(['id', 'name', 'code', 'capacity', 'base_price']);
        $guests = Guest::query()->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/HallBookings/Create', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => $routePrefix,
            'halls'       => $halls,
            'guests' => $guests,
        ]);
    }

    public function store(HallBookingRequest $request)
    {
        $validated = $request->validated();
        $hall      = Hall::findOrFail($validated['hall_id']);

        $booking = HallBooking::create([
            'booking_number' => $this->generateBookingNumber(),
            'hall_id' => $validated['hall_id'],
            'guest_id' => $validated['guest_id'] ?? null,
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'attendees' => $validated['attendees'],
            'total_amount' => $this->computeTotalAmount($hall, $validated['start_time'], $validated['end_time']),
            'paid_amount' => $validated['paid_amount'] ?? 0,
            'status' => $validated['status'] ?? 'pending',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.hall-bookings.index' : 'admin.hall-bookings.index';
        return redirect()->route($indexRoute)->with('success', 'Hall booking created successfully.');
    }

    public function show(HallBooking $hallBooking)
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $hallBooking->load(['hall', 'guest', 'createdBy', 'updatedBy']);

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/HallBookings/Show', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => $routePrefix,
            'booking'     => $hallBooking,
        ]);
    }

    public function edit(HallBooking $hallBooking)
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'admin';

        $halls  = Hall::query()->orderBy('name')->get(['id', 'name', 'code', 'capacity', 'base_price']);
        $guests = Guest::query()->orderBy('first_name')->orderBy('last_name')
                       ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        $routePrefix = $role === 'manager' ? 'manager' : 'admin';
        return Inertia::render('Admin/HallBookings/Edit', [
            'user'        => $user,
            'navigation'  => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => $routePrefix,
            'booking'     => $hallBooking,
            'halls'      => $halls,
            'guests'     => $guests,
        ]);
    }

    public function update(HallBookingRequest $request, HallBooking $hallBooking)
    {
        $validated   = $request->validated();
        $hall        = Hall::findOrFail($validated['hall_id']);
        $totalAmount = $this->computeTotalAmount($hall, $validated['start_time'], $validated['end_time']);

        $hallBooking->update(array_merge($validated, [
            'total_amount' => $totalAmount,
            'updated_by'   => auth()->id(),
        ]));

        $routeName = request()->route()->getName() ?? '';
        $showRoute = str_starts_with($routeName, 'manager.') ? 'manager.hall-bookings.show' : 'admin.hall-bookings.show';
        return redirect()->route($showRoute, $hallBooking)->with('success', 'Hall booking updated successfully.');
    }

    public function destroy(HallBooking $hallBooking)
    {
        $hallBooking->delete();

        $routeName = request()->route()->getName() ?? '';
        $indexRoute = str_starts_with($routeName, 'manager.') ? 'manager.hall-bookings.index' : 'admin.hall-bookings.index';
        return redirect()->route($indexRoute)->with('success', 'Hall booking deleted successfully.');
    }

    public function processPayment(\Illuminate\Http\Request $request, HallBooking $hallBooking)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque',
            'notes'          => 'nullable|string|max:500',
        ]);

        $newPaid = (float) $hallBooking->paid_amount + (float) $validated['amount'];
        $total   = (float) $hallBooking->total_amount;

        $updates = [
            'paid_amount' => min($newPaid, $total),
            'updated_by'  => auth()->id(),
        ];

        // Auto-confirm when fully paid and still pending
        if ($newPaid >= $total && $hallBooking->status === 'pending') {
            $updates['status'] = 'confirmed';
        }

        $hallBooking->update($updates);

        $formatted = number_format($validated['amount'], 2);

        return redirect()->back()
            ->with('success', "Payment of {$formatted} recorded successfully.");
    }
}
