<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestFolio;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\FolioCharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $today = Carbon::today();

        $query = GuestFolio::with(['guest', 'reservation', 'room'])
            ->orderByDesc('folio_date');

        // Apply date filters
        if ($startDate) {
            $query->whereDate('folio_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('folio_date', '<=', $endDate);
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('folio_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($subQuery) use ($search) {
                      $subQuery->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $folios = $query->limit(200)
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $status = 'open';
                if ($balance <= 0) {
                    $status = 'paid';
                } elseif ($dueDate && Carbon::parse($dueDate)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $folio->id,
                    'invoice_number' => $folio->folio_number,
                    'customer_name' => $folio->guest?->full_name ?? $folio->customer_name ?? 'Guest',
                    'customer_email' => $folio->guest?->email ?? $folio->customer_email ?? null,
                    'total_amount' => (float) ($folio->total_amount ?? 0),
                    'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                    'due_date' => optional($dueDate)->format('Y-m-d'),
                    'status' => $status,
                    'reservation_id' => $folio->reservation_id,
                    'balance_amount' => $balance,
                ];
            });

        // Apply status filter after mapping
        if ($statusFilter) {
            $folios = $folios->filter(fn ($folio) => $folio['status'] === $statusFilter)->values();
        }

        // Calculate additional stats
        $paidCount = $folios->where('status', 'paid')->count();
        $thisMonthCount = $folios->filter(function ($folio) use ($today) {
            return Carbon::parse($folio['issue_date'])->month === $today->month &&
                   Carbon::parse($folio['issue_date'])->year === $today->year;
        })->count();

        $invoiceStats = [
            'total' => $folios->count(),
            'totalAmount' => round($folios->sum('total_amount'), 2),
            'pending' => $folios->whereIn('status', ['open'])->count(),
            'overdue' => $folios->where('status', 'overdue')->count(),
            'paid' => $paidCount,
            'thisMonth' => $thisMonthCount,
        ];

        // Determine which view to render based on the route
        $view = 'Admin/Invoices/Index';
        $role = 'admin';

        if (request()->is('manager/*')) {
            $view = 'Manager/Invoices/Index';
            $role = 'manager';
        } elseif (request()->is('front-desk/*')) {
            $view = 'FrontDesk/Invoices/Index';
            $role = 'front_desk';
        }

        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role);

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'invoiceStats' => $invoiceStats,
            'invoices' => $folios->values(),
            'filters' => [
                'status' => $statusFilter,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $search,
            ],
        ]);
    }

    public function show(GuestFolio $folio)
    {
        $folio->load(['guest', 'reservation', 'room', 'charges', 'payments']);
        $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
        $balance = (float) ($folio->balance_amount ?? 0);
        $status = $balance <= 0 ? 'paid' : 'open';
        if ($balance > 0 && $dueDate && Carbon::parse($dueDate)->lt(Carbon::today())) {
            $status = 'overdue';
        }

        // Determine which view to render based on the route
        $view = 'Admin/Invoices/Show';
        $role = 'admin';

        if (request()->is('manager/*')) {
            $view = 'Manager/Invoices/Show';
            $role = 'manager';
        } elseif (request()->is('front-desk/*')) {
            $view = 'FrontDesk/Invoices/Show';
            $role = 'front_desk';
        }

        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role);

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'invoice' => [
                'id' => $folio->id,
                'folio_number' => $folio->folio_number,
                'customer_name' => $folio->guest?->full_name ?? $folio->customer_name ?? 'Guest',
                'customer_email' => $folio->guest?->email ?? $folio->customer_email ?? null,
                'customer_phone' => $folio->customer_phone ?? null,
                'room_number' => $folio->room?->room_number ?? 'N/A',
                'total_amount' => (float) ($folio->total_amount ?? 0),
                'paid_amount' => (float) ($folio->paid_amount ?? 0),
                'balance_amount' => $balance,
                'folio_date' => optional($folio->folio_date)->format('Y-m-d'),
                'due_date' => optional($dueDate)->format('Y-m-d'),
                'status' => $status,
                'notes' => $folio->notes,
                'invoice_type' => $folio->reservation_id ? 'guest' : 'outsider',
                'charges' => $folio->charges->map(fn ($charge) => [
                    'id' => $charge->id,
                    'description' => $charge->description,
                    'total_amount' => (float) ($charge->total_amount ?? 0),
                    'quantity' => $charge->quantity ?? 1,
                    'unit_price' => (float) ($charge->unit_price ?? 0),
                    'created_at' => optional($charge->created_at)->toDateString(),
                ]),
                'payments' => $folio->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'amount' => (float) ($payment->local_amount ?? $payment->amount ?? 0),
                    'method' => $payment->payment_method,
                    'status' => $payment->status,
                    'processed_at' => optional($payment->processed_at)->toDateTimeString(),
                ]),
                'reservation' => $folio->reservation,
                'guest' => $folio->guest,
                'room' => $folio->room,
            ],
        ]);
    }

    public function create()
    {
        // Only get reservations with unsettled bills for guest invoices
        $reservations = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->whereDoesntHave('guestFolios') // No existing invoice
            ->orWhereHas('guestFolios', function ($query) {
                $query->where('balance_amount', '>', 0); // Or has unsettled balance
            })
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest?->full_name ?? 'Guest',
                    'room_number' => $reservation->room?->room_number ?? 'N/A',
                    'check_out_date' => optional($reservation->check_out_date)->format('Y-m-d'),
                    'total_amount' => (float) ($reservation->total_amount ?? 0),
                    'balance_amount' => (float) ($reservation->balance_amount ?? 0),
                ];
            });

        // Determine which view to render based on the route
        $view = request()->is('manager/*') ? 'Manager/Invoices/Create' : 'Admin/Invoices/Create';
        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole(
            request()->is('manager/*') ? 'manager' : 'admin'
        );

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'reservations' => $reservations,
        ]);
    }

    public function store(Request $request)
    {
        // Debug logging
        \Log::info('Invoice store method called', [
            'request_data' => $request->all(),
            'request_method' => $request->method(),
            'request_path' => $request->path()
        ]);

        $validated = $request->validate([
            'invoice_type' => 'required|in:guest,outsider',
            'reservation_id' => 'required_if:invoice_type,guest|nullable',
            'customer_name' => 'required_if:invoice_type,outsider|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required_if:invoice_type,outsider|array|min:1',
            'items.*.description' => 'required_if:invoice_type,outsider|string|max:255',
            'items.*.quantity' => 'required_if:invoice_type,outsider|integer|min:1',
            'items.*.unit_price' => 'required_if:invoice_type,outsider|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Additional validation for guest invoices
        if ($validated['invoice_type'] === 'guest') {
            $request->validate([
                'reservation_id' => 'exists:reservations,id'
            ]);
        }

        \Log::info('Validation passed', ['validated_data' => $validated]);

        if ($validated['invoice_type'] === 'guest') {
            $reservation = Reservation::findOrFail($validated['reservation_id']);

            // Check if existing invoice exists
            $existingFolio = GuestFolio::where('reservation_id', $reservation->id)->first();
            if ($existingFolio) {
                $route = request()->is('manager/*') ? 'manager.invoices.index' : 'admin.invoices.index';
                return redirect()->route($route)
                    ->with('error', 'An invoice already exists for this reservation.');
            }

            $totalAmount = $reservation->total_amount ?? 0;

            GuestFolio::create([
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id,
                'folio_number' => 'FOLIO-' . strtoupper(Str::random(8)),
                'status' => 'open',
                'folio_date' => now()->toDateString(),
                'room_charges' => $reservation->total_room_charges ?? 0,
                'service_charges' => $reservation->service_charges ?? 0,
                'tax_amount' => $reservation->taxes ?? 0,
                'discount_amount' => $reservation->discount_amount ?? 0,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
            ]);
        } else {
            // Outsider invoice
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $folio = GuestFolio::create([
                'folio_number' => 'FOLIO-' . strtoupper(Str::random(8)),
                'reservation_id' => null, // Allow null for outsider invoices
                'guest_id' => null, // Allow null for outsider invoices
                'room_id' => null, // Allow null for outsider invoices
                'status' => 'open',
                'folio_date' => now()->toDateString(),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create custom items as folio charges
            foreach ($validated['items'] as $item) {
                $totalAmount = $item['quantity'] * $item['unit_price'];
                FolioCharge::create([
                    'guest_folio_id' => $folio->id,
                    'charge_code' => 'CUSTOM-' . strtoupper(Str::random(6)),
                    'description' => $item['description'],
                    'charge_date' => now()->toDateString(),
                    'charge_time' => now(),
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $totalAmount,
                    'net_amount' => $totalAmount,
                    'reference_type' => 'custom_invoice',
                    'department' => 'Front Desk',
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);
            }
        }

        $route = request()->is('manager/*') ? 'manager.invoices.index' : 'admin.invoices.index';
        return redirect()->route($route)
            ->with('success', 'Invoice created successfully.');
    }

    public function markPaid(GuestFolio $folio)
    {
        $balance = (float) ($folio->balance_amount ?? 0);
        if ($balance <= 0) {
            return redirect()->route('admin.invoices.index');
        }

        $currency = Setting::get('currency', 'USD');
        $exchangeRate = 1.0;
        $localAmount = $balance * $exchangeRate;

        Payment::create([
            'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
            'guest_folio_id' => $folio->id,
            'reservation_id' => $folio->reservation_id,
            'payment_method' => 'bank_transfer',
            'amount' => $balance,
            'currency' => $currency,
            'exchange_rate' => $exchangeRate,
            'local_amount' => $localAmount,
            'status' => 'completed',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        $folio->paid_amount = (float) ($folio->paid_amount ?? 0) + $balance;
        $folio->balance_amount = 0;
        $folio->status = 'closed';
        $folio->closed_at = now();
        $folio->closed_by = auth()->id();
        $folio->save();

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice marked as paid.');
    }

    public function overdue()
    {
        $statusFilter = 'overdue';
        $today = Carbon::today();

        $folios = GuestFolio::with(['guest', 'reservation', 'room'])
            ->orderByDesc('folio_date')
            ->limit(200)
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $status = 'sent';
                if ($balance <= 0) {
                    $status = 'paid';
                } elseif ($dueDate && Carbon::parse($dueDate)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $folio->id,
                    'invoice_number' => $folio->folio_number,
                    'customer_name' => $folio->guest?->full_name ?? $folio->customer_name ?? 'Guest',
                    'customer_email' => $folio->guest?->email ?? $folio->customer_email ?? null,
                    'total_amount' => (float) ($folio->total_amount ?? 0),
                    'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                    'due_date' => optional($dueDate)->format('Y-m-d'),
                    'paid_date' => $balance <= 0 ? optional($folio->updated_at)->format('Y-m-d') : null,
                    'status' => $status,
                    'reservation_id' => $folio->reservation_id,
                    'balance_amount' => $balance,
                    'room_number' => $folio->room?->room_number,
                ];
            })
            ->filter(fn ($folio) => $folio['status'] === 'overdue')
            ->values();

        $invoiceStats = [
            'overdue' => $folios->count(),
            'overdueAmount' => round($folios->sum('balance_amount'), 2),
        ];

        return Inertia::render('Admin/Invoices/Overdue', [
            'user' => auth()->user()->load('roles'),
            'invoiceStats' => $invoiceStats,
            'invoices' => $folios,
        ]);
    }

    public function paid()
    {
        $statusFilter = 'paid';
        $today = Carbon::today();

        $folios = GuestFolio::with(['guest', 'reservation', 'room'])
            ->orderByDesc('folio_date')
            ->limit(200)
            ->get()
            ->map(function ($folio) use ($today) {
                $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
                $balance = (float) ($folio->balance_amount ?? 0);
                $status = 'sent';
                if ($balance <= 0) {
                    $status = 'paid';
                } elseif ($dueDate && Carbon::parse($dueDate)->lt($today)) {
                    $status = 'overdue';
                }

                return [
                    'id' => $folio->id,
                    'invoice_number' => $folio->folio_number,
                    'customer_name' => $folio->guest?->full_name ?? $folio->customer_name ?? 'Guest',
                    'customer_email' => $folio->guest?->email ?? $folio->customer_email ?? null,
                    'total_amount' => (float) ($folio->total_amount ?? 0),
                    'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                    'due_date' => optional($dueDate)->format('Y-m-d'),
                    'paid_date' => $balance <= 0 ? optional($folio->updated_at)->format('Y-m-d') : null,
                    'status' => $status,
                    'reservation_id' => $folio->reservation_id,
                    'balance_amount' => $balance,
                    'room_number' => $folio->room?->room_number,
                    'payment_method' => 'cash', // This would come from payment records
                ];
            })
            ->filter(fn ($folio) => $folio['status'] === 'paid')
            ->values();

        $invoiceStats = [
            'paid' => $folios->count(),
            'paidAmount' => round($folios->sum('total_amount'), 2),
        ];

        return Inertia::render('Admin/Invoices/Paid', [
            'user' => auth()->user()->load('roles'),
            'invoiceStats' => $invoiceStats,
            'invoices' => $folios,
        ]);
    }

    public function sendReminders()
    {
        $today = Carbon::today();

        // Get overdue invoices
        $overdueFolios = GuestFolio::with(['guest', 'reservation'])
            ->where('balance_amount', '>', 0)
            ->whereHas('reservation', function ($query) use ($today) {
                $query->where('check_out_date', '<', $today);
            })
            ->get();

        $remindersSent = 0;

        foreach ($overdueFolios as $folio) {
            $email = $folio->guest?->email ?? $folio->customer_email;
            if ($email) {
                // Here you would implement actual email sending
                // For now, we'll just count how many reminders would be sent
                $remindersSent++;

                // TODO: Implement actual email sending
                // Mail::to($email)->send(new InvoiceReminder($folio));
            }
        }

        return redirect()->route('admin.invoices.index')
            ->with('success', "Payment reminders sent to {$remindersSent} customers with overdue invoices.");
    }

    public function edit(GuestFolio $folio)
    {
        $folio->load(['guest', 'reservation', 'room', 'charges', 'payments']);
        $dueDate = $folio->reservation?->check_out_date ?? $folio->folio_date;
        $balance = (float) ($folio->balance_amount ?? 0);
        $status = $balance <= 0 ? 'paid' : 'open';
        if ($balance > 0 && $dueDate && Carbon::parse($dueDate)->lt(Carbon::today())) {
            $status = 'overdue';
        }

        // Get guests for dropdown
        $guests = \App\Models\Guest::select('id', 'first_name', 'last_name', 'email')->get()
            ->map(fn($guest) => [
                'id' => $guest->id,
                'name' => $guest->full_name,
                'email' => $guest->email,
            ]);

        // Determine which view to render based on the route
        $view = 'Admin/Invoices/Edit';
        $role = 'admin';

        if (request()->is('manager/*')) {
            $view = 'Manager/Invoices/Edit';
            $role = 'manager';
        }

        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role);

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'invoice' => [
                'id' => $folio->id,
                'invoice_number' => $folio->folio_number,
                'customer_name' => $folio->guest?->full_name ?? $folio->customer_name ?? 'Guest',
                'customer_email' => $folio->guest?->email ?? $folio->customer_email ?? null,
                'total_amount' => (float) ($folio->total_amount ?? 0),
                'issue_date' => optional($folio->folio_date)->format('Y-m-d'),
                'due_date' => optional($dueDate)->format('Y-m-d'),
                'status' => $status,
                'balance_amount' => $balance,
                'charges' => $folio->charges ?? [],
                'payments' => $folio->payments ?? [],
            ],
            'guests' => $guests,
        ]);
    }

    public function update(Request $request, GuestFolio $folio)
    {
        $validated = $request->validate([
            'folio_number' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'folio_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Update the folio in database
        try {
            $folio->update($validated);

            $route = request()->is('manager/*') ? 'manager.invoices.show' : 'admin.invoices.show';
            return redirect()->route($route, $folio->id)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function destroy(GuestFolio $folio)
    {
        try {
            // Soft delete or hard delete (configure based on your needs)
            $folio->delete();

            $route = request()->is('manager/*') ? 'manager.invoices.index' : 'admin.invoices.index';
            return redirect()->route($route)
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }
}
