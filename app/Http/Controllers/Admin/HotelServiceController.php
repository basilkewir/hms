<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotelService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HotelServiceController extends Controller
{
    public function index()
    {
        $perPage = (int) request()->get('per_page', 15);
        $perPage = max(5, min($perPage, 100));

        $allServices = HotelService::select(['id', 'is_active', 'is_free'])->get();

        // Calculate stats
        $stats = [
            'total' => $allServices->count(),
            'active' => $allServices->where('is_active', true)->count(),
            'free' => $allServices->where('is_free', true)->count(),
            'paid' => $allServices->where('is_free', false)->count(),
        ];

        $services = HotelService::orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();
        
        return Inertia::render('Admin/Services/Index', [
            'user' => auth()->user()->load('roles'),
            'services' => $services,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'pricing_type' => 'required|string',
            'is_active' => 'boolean',
            'available_online' => 'boolean',
            'requires_advance_booking' => 'boolean',
            'advance_hours' => 'nullable|integer',
        ]);

        // If service is free, set price to 0
        if ($validated['is_free'] ?? false) {
            $validated['price'] = 0;
        } else {
            // If not free, price is required
            $request->validate(['price' => 'required|numeric|min:0']);
        }

        HotelService::create($validated);

        return redirect()->back()->with('success', 'Service created successfully');
    }

    public function update(Request $request, HotelService $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'pricing_type' => 'required|string',
            'is_active' => 'boolean',
            'available_online' => 'boolean',
            'requires_advance_booking' => 'boolean',
            'advance_hours' => 'nullable|integer',
        ]);

        // If service is free, set price to 0
        if ($validated['is_free'] ?? false) {
            $validated['price'] = 0;
        } else {
            // If not free, price is required
            $request->validate(['price' => 'required|numeric|min:0']);
        }

        $service->update($validated);

        return redirect()->back()->with('success', 'Service updated successfully');
    }

    public function destroy(HotelService $service)
    {
        $service->delete();

        return redirect()->back()->with('success', 'Service deleted successfully');
    }
}
