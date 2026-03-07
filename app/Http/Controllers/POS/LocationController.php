<?php

namespace App\Http\Controllers\POS;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        $this->authorize('manage_inventory');

        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $locations = Location::with('warehouse')
            ->latest()
            ->paginate(20);

        return Inertia::render('POS/Locations/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'locations' => $locations
        ]);
    }

    public function create()
    {
        $this->authorize('manage_inventory');

        $warehouses = Warehouse::where('is_active', true)->get();

        return Inertia::render('POS/Locations/Create', [
            'warehouses' => $warehouses
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations',
            'type' => 'required|in:warehouse,restaurant,frontdesk,bar,kitchen,other',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $location = Location::create($validated);

        return redirect()->route('pos.locations.index')
            ->with('success', 'Location created successfully');
    }

    public function edit(Location $location)
    {
        $this->authorize('manage_inventory');

        $warehouses = Warehouse::where('is_active', true)->get();

        return Inertia::render('POS/Locations/Edit', [
            'location' => $location,
            'warehouses' => $warehouses
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'type' => 'required|in:warehouse,restaurant,frontdesk,bar,kitchen,other',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $location->update($validated);

        return redirect()->route('pos.locations.index')
            ->with('success', 'Location updated successfully');
    }

    public function destroy(Location $location)
    {
        $this->authorize('manage_inventory');

        // Check if location has users assigned
        if ($location->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete location with assigned users');
        }

        $location->delete();

        return redirect()->route('pos.locations.index')
            ->with('success', 'Location deleted successfully');
    }
}
