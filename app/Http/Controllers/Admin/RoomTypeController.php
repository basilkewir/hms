<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Models\RoomAmenity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->get();

        return Inertia::render('Admin/RoomTypes/Index', [
            'user' => auth()->user()->load('roles'),
            'roomTypes' => $roomTypes->map(fn($roomType) => [
                'id' => $roomType->id,
                'name' => $roomType->name,
                'code' => $roomType->code,
                'description' => $roomType->description,
                'max_occupancy' => $roomType->max_occupancy,
                'max_adults' => $roomType->max_adults,
                'max_children' => $roomType->max_children,
                'base_price' => $roomType->base_price,
                'extra_adult_charge' => $roomType->extra_adult_charge,
                'extra_child_charge' => $roomType->extra_child_charge,
                'amenities' => $roomType->amenities,
                'iptv_package' => $roomType->iptv_package,
                'room_size_sqft' => $roomType->room_size_sqft,
                'bed_type' => $roomType->bed_type,
                'bed_count' => $roomType->bed_count,
                'has_balcony' => $roomType->has_balcony,
                'has_living_room' => $roomType->has_living_room,
                'view_type' => $roomType->view_type,
                'is_active' => $roomType->is_active,
                'room_count' => $roomType->rooms_count,
                'status' => $roomType->is_active ? 'active' : 'inactive'
            ]),
        ]);
    }

    public function create()
    {
        $amenities = RoomAmenity::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/RoomTypes/Create', [
            'user' => auth()->user()->load('roles'),
            'amenities' => $amenities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:room_types',
            'description' => 'nullable|string',
            'max_occupancy' => 'required|integer|min:1',
            'max_adults' => 'required|integer|min:1',
            'max_children' => 'required|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'extra_adult_charge' => 'nullable|numeric|min:0',
            'extra_child_charge' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'iptv_package' => 'nullable|string',
            'room_size_sqft' => 'nullable|integer|min:0',
            'bed_type' => 'nullable|string',
            'bed_count' => 'nullable|integer|min:1',
            'has_balcony' => 'nullable|boolean',
            'has_living_room' => 'nullable|boolean',
            'view_type' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $roomType = RoomType::create($validated);

        return redirect()->route('admin.room-types.index')->with('success', 'Room type created successfully');
    }

    public function edit(RoomType $roomType)
    {
        $amenities = RoomAmenity::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/RoomTypes/Edit', [
            'user' => auth()->user()->load('roles'),
            'roomType' => [
                'id' => $roomType->id,
                'name' => $roomType->name,
                'code' => $roomType->code,
                'description' => $roomType->description,
                'max_occupancy' => $roomType->max_occupancy,
                'max_adults' => $roomType->max_adults,
                'max_children' => $roomType->max_children,
                'base_price' => $roomType->base_price,
                'extra_adult_charge' => $roomType->extra_adult_charge,
                'extra_child_charge' => $roomType->extra_child_charge,
                'amenities' => $roomType->amenities,
                'iptv_package' => $roomType->iptv_package,
                'room_size_sqft' => $roomType->room_size_sqft,
                'bed_type' => $roomType->bed_type,
                'bed_count' => $roomType->bed_count,
                'has_balcony' => $roomType->has_balcony,
                'has_living_room' => $roomType->has_living_room,
                'view_type' => $roomType->view_type,
                'is_active' => $roomType->is_active,
            ],
            'amenities' => $amenities,
        ]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:room_types,code,' . $roomType->id,
            'description' => 'nullable|string',
            'max_occupancy' => 'required|integer|min:1',
            'max_adults' => 'required|integer|min:1',
            'max_children' => 'required|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'extra_adult_charge' => 'nullable|numeric|min:0',
            'extra_child_charge' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'iptv_package' => 'nullable|string',
            'room_size_sqft' => 'nullable|integer|min:0',
            'bed_type' => 'nullable|string',
            'bed_count' => 'nullable|integer|min:1',
            'has_balcony' => 'nullable|boolean',
            'has_living_room' => 'nullable|boolean',
            'view_type' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $roomType->update($validated);

        return redirect()->route('admin.room-types.index')->with('success', 'Room type updated successfully');
    }

    public function destroy(RoomType $roomType)
    {
        // Check if room type is used by any rooms
        if ($roomType->rooms()->exists()) {
            return back()->with('error', 'Cannot delete room type that is currently used by rooms');
        }

        $roomType->delete();

        return back()->with('success', 'Room type deleted successfully');
    }
}
