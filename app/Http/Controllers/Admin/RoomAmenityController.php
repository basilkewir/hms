<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomAmenity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomAmenityController extends Controller
{
    public function index()
    {
        $amenities = RoomAmenity::withCount('roomTypes')->get();
        
        return Inertia::render('Admin/RoomAmenities/Index', [
            'user' => auth()->user()->load('roles'),
            'amenities' => $amenities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        RoomAmenity::create($validated);

        return redirect()->back()->with('success', 'Amenity created successfully');
    }

    public function update(Request $request, RoomAmenity $amenity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $amenity->update($validated);

        return redirect()->back()->with('success', 'Amenity updated successfully');
    }

    public function destroy(RoomAmenity $amenity)
    {
        $amenity->delete();

        return redirect()->back()->with('success', 'Amenity deleted successfully');
    }
}
