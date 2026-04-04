<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Reservation;
use App\Models\GuestFolio;
use App\Models\FolioCharge;
use App\Services\SystemActivityNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');

        // Build query with filters
        $query = Quote::query();

        // Apply filters
        if ($status) {
            $query->byStatus($status);
        }
        if ($startDate || $endDate) {
            $query->byDateRange($startDate, $endDate);
        }
        if ($search) {
            $query->search($search);
        }

        // Get paginated quotes
        $quotes = $query->orderByDesc('created_at')->get();

        // Calculate statistics
        $allQuotes = Quote::query();
        $quoteStats = [
            'total' => $allQuotes->count(),
            'totalAmount' => $allQuotes->sum('total_amount'),
            'pending' => $allQuotes->where('status', 'sent')->count(),
            'accepted' => $allQuotes->where('status', 'accepted')->count(),
            'thisMonth' => $allQuotes->where('status', 'draft')->count(),
        ];

        // Determine which view to render based on the route
        $view = 'Admin/Quotes/Index';
        $role = 'admin';

        if (request()->is('manager/*')) {
            $view = 'Manager/Quotes/Index';
            $role = 'manager';
        } elseif (request()->is('front-desk/*')) {
            $view = 'FrontDesk/Quotes/Index';
            $role = 'front_desk';
        }

        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role);

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'quoteStats' => $quoteStats,
            'quotes' => $quotes,
            'filters' => [
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        // Get active reservations from database
        $reservations = Reservation::select('id', 'id as guest_id', 'created_at')
            ->with(['guest:id,first_name,last_name', 'rooms:id,room_number'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest ? $reservation->guest->first_name . ' ' . $reservation->guest->last_name : 'Unknown',
                    'room_number' => $reservation->rooms->first()?->room_number ?? 'N/A',
                    'check_out_date' => $reservation->created_at->format('Y-m-d'),
                ];
            });

        // Determine which view to render based on the route
        $view = 'Admin/Quotes/Create';
        $role = 'admin';

        if (request()->is('manager/*')) {
            $view = 'Manager/Quotes/Create';
            $role = 'manager';
        } elseif (request()->is('front-desk/*')) {
            $view = 'FrontDesk/Quotes/Create';
            $role = 'front_desk';
        }

        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role);

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'reservations' => $reservations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_type' => 'required|in:guest,outsider',
            'reservation_id' => 'nullable|exists:reservations,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'valid_until' => 'required|date|after_or_equal:today',
            'total_amount' => 'required|numeric|min:0.01',
            'status' => 'nullable|in:draft,sent',
            'items' => 'nullable|array',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validate based on quote type
        if ($request->quote_type === 'guest' && !$request->reservation_id) {
            return back()->withErrors(['reservation_id' => 'Reservation is required for guest quotes.']);
        }
        if ($request->quote_type === 'outsider' && !$request->customer_name) {
            return back()->withErrors(['customer_name' => 'Customer name is required for outsider quotes.']);
        }

        // Create quote
        $quote = Quote::create([
            'quote_number' => Quote::generateQuoteNumber(),
            'quote_type' => $validated['quote_type'],
            'reservation_id' => $validated['reservation_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'total_amount' => $validated['total_amount'],
            'valid_until' => $validated['valid_until'],
            'status' => $validated['status'] ?? 'draft',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'issue_date' => now()->toDateString(),
        ]);

        // Add items to quote
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                    $quote->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }
            }
        }

        app(SystemActivityNotifier::class)->notifyRoles(
            ['admin', 'manager'],
            'quote.created',
            'New estimate created',
            sprintf(
                'Estimate %s for %s was created by %s.',
                $quote->quote_number,
                $quote->customer_name ?: ($quote->reservation_id ? ('reservation ' . $quote->reservation_id) : 'a guest'),
                auth()->user()?->full_name ?? auth()->user()?->email ?? 'Staff'
            ),
            [
                'manager' => route('manager.quotes.show', $quote->id),
                'default' => route('admin.quotes.show', $quote->id),
            ],
            [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'quote_status' => $quote->status,
            ],
            auth()->user(),
        );

        // Redirect based on role
        if (request()->is('front-desk/*')) {
            return redirect()->route('front-desk.quotes.index')
                ->with('success', 'Quote created successfully.');
        }

        $route = request()->is('manager/*') ? 'manager.quotes.index' : 'admin.quotes.index';
        return redirect()->route($route)
            ->with('success', 'Quote created successfully.');
    }

    public function show($id)
    {
        $quote = Quote::with('items', 'reservation', 'customer')->findOrFail($id);

        // Determine which view to render based on the route
        $view = request()->is('manager/*') ? 'Manager/Quotes/Show' : 'Admin/Quotes/Show';
        $role = request()->is('manager/*') ? 'manager' : 'admin';

        if (request()->is('front-desk/*')) {
            $view = 'FrontDesk/Quotes/Show';
            $role = 'front_desk';
        }

        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role);

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'quote' => $quote,
        ]);
    }

    public function edit($id)
    {
        $quote = Quote::with('items', 'reservation')->findOrFail($id);

        // Get reservations
        $reservations = Reservation::select('id', 'id as guest_id', 'created_at')
            ->with(['guest:id,first_name,last_name', 'rooms:id,room_number'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'guest_name' => $reservation->guest ? $reservation->guest->first_name . ' ' . $reservation->guest->last_name : 'Unknown',
                    'room_number' => $reservation->rooms->first()?->room_number ?? 'N/A',
                    'check_out_date' => $reservation->created_at->format('Y-m-d'),
                ];
            });

        // Determine which view to render based on the route
        $view = request()->is('manager/*') ? 'Manager/Quotes/Edit' : 'Admin/Quotes/Edit';
        $role = request()->is('manager/*') ? 'manager' : 'admin';

        if (request()->is('front-desk/*')) {
            $view = 'FrontDesk/Quotes/Edit';
            $role = 'front_desk';
        }

        $navigation = app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role);

        return Inertia::render($view, [
            'user' => auth()->user()->load('roles'),
            'navigation' => $navigation,
            'quote' => $quote,
            'reservations' => $reservations,
        ]);
    }

    public function update(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);

        $validated = $request->validate([
            'quote_type' => 'required|in:guest,outsider',
            'reservation_id' => 'nullable|exists:reservations,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'valid_until' => 'required|date|after_or_equal:today',
            'total_amount' => 'required|numeric|min:0.01',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired',
            'items' => 'nullable|array',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validate based on quote type
        if ($request->quote_type === 'guest' && !$request->reservation_id) {
            return back()->withErrors(['reservation_id' => 'Reservation is required for guest quotes.']);
        }
        if ($request->quote_type === 'outsider' && !$request->customer_name) {
            return back()->withErrors(['customer_name' => 'Customer name is required for outsider quotes.']);
        }

        // Update quote
        $quote->update([
            'quote_type' => $validated['quote_type'],
            'reservation_id' => $validated['reservation_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'total_amount' => $validated['total_amount'],
            'valid_until' => $validated['valid_until'],
            'status' => $validated['status'] ?? 'draft',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update items
        $quote->items()->delete();
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                    $quote->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }
            }
        }

        // Redirect based on role
        if (request()->is('front-desk/*')) {
            return redirect()->route('front-desk.quotes.show', $id)
                ->with('success', 'Quote updated successfully.');
        }

        $route = request()->is('manager/*') ? 'manager.quotes.show' : 'admin.quotes.show';
        return redirect()->route($route, $id)
            ->with('success', 'Quote updated successfully.');
    }

    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);
        $quote->delete();

        // Redirect based on role
        if (request()->is('front-desk/*')) {
            return redirect()->route('front-desk.quotes.index')
                ->with('success', 'Quote deleted successfully.');
        }

        $route = request()->is('manager/*') ? 'manager.quotes.index' : 'admin.quotes.index';
        return redirect()->route($route)
            ->with('success', 'Quote deleted successfully.');
    }

    public function convert($id)
    {
        $quote = Quote::with('items')->findOrFail($id);

        if ($quote->status === 'accepted') {
            return back()->with('error', 'This quote has already been converted to an invoice.');
        }

        // Determine total amount from quote
        $totalAmount = (float) ($quote->total_amount ?? 0);

        if ($quote->quote_type === 'guest' && $quote->reservation_id) {
            // Guest quote — create folio linked to reservation
            $reservation = Reservation::find($quote->reservation_id);

            $existingFolio = GuestFolio::where('reservation_id', $quote->reservation_id)->first();
            if ($existingFolio) {
                return back()->with('error', 'An invoice already exists for this reservation.');
            }

            GuestFolio::create([
                'reservation_id' => $quote->reservation_id,
                'guest_id'       => $reservation?->guest_id ?? null,
                'room_id'        => $reservation?->room_id ?? null,
                'folio_number'   => 'FOLIO-' . strtoupper(Str::random(8)),
                'status'         => 'open',
                'folio_date'     => now()->toDateString(),
                'room_charges'   => $reservation?->total_room_charges ?? 0,
                'service_charges'=> $reservation?->service_charges ?? 0,
                'tax_amount'     => $reservation?->taxes ?? 0,
                'discount_amount'=> $reservation?->discount_amount ?? 0,
                'total_amount'   => $totalAmount,
                'paid_amount'    => 0,
                'balance_amount' => $totalAmount,
                'notes'          => $quote->notes,
            ]);
        } else {
            // Outsider quote — create standalone folio
            $folio = GuestFolio::create([
                'folio_number'   => 'FOLIO-' . strtoupper(Str::random(8)),
                'reservation_id' => null,
                'guest_id'       => null,
                'room_id'        => null,
                'status'         => 'open',
                'folio_date'     => now()->toDateString(),
                'customer_name'  => $quote->customer_name,
                'customer_email' => $quote->customer_email,
                'customer_phone' => $quote->customer_phone,
                'total_amount'   => $totalAmount,
                'paid_amount'    => 0,
                'balance_amount' => $totalAmount,
                'notes'          => $quote->notes,
            ]);

            // Create folio charges from quote items
            foreach ($quote->items as $item) {
                $itemTotal = (float) ($item->quantity ?? 1) * (float) ($item->unit_price ?? 0);
                FolioCharge::create([
                    'guest_folio_id' => $folio->id,
                    'charge_code'    => 'QUOTE-' . strtoupper(Str::random(6)),
                    'description'    => $item->description,
                    'charge_date'    => now()->toDateString(),
                    'charge_time'    => now(),
                    'quantity'       => $item->quantity ?? 1,
                    'unit_price'     => $item->unit_price ?? 0,
                    'total_amount'   => $itemTotal,
                    'net_amount'     => $itemTotal,
                    'reference_type' => 'quote_conversion',
                    'department'     => 'Front Desk',
                    'posted_by'      => auth()->id(),
                    'posted_at'      => now(),
                ]);
            }
        }

        // Mark quote as accepted
        $quote->update(['status' => 'accepted']);

        // Redirect to invoices page (not quotes)
        if (request()->is('front-desk/*')) {
            return redirect()->route('front-desk.invoices.index')
                ->with('success', 'Quote converted to invoice successfully.');
        }

        $route = request()->is('manager/*') ? 'manager.invoices.index' : 'admin.invoices.index';
        return redirect()->route($route)
            ->with('success', 'Quote converted to invoice successfully.');
    }
}
