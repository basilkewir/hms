<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Reservation;
use Illuminate\Http\Request;
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
            'issue_date' => now()->date(),
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
}
