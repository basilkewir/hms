<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BedType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;

class BedTypeController extends Controller
{
    public function index()
    {
        $bedTypes = BedType::orderBy('sort_order')->orderBy('name')->get();
        
        // Only count if columns exist
        $hasBedTypeId = Schema::hasColumn('rooms', 'bed_type_id');
        $hasRoomTypeBedTypeId = Schema::hasColumn('room_types', 'bed_type_id');
        
        if ($hasBedTypeId) {
            $bedTypes->loadCount('rooms');
        }
        if ($hasRoomTypeBedTypeId) {
            $bedTypes->loadCount('roomTypes');
        }

        return Inertia::render('Admin/BedTypes/Index', [
            'user' => auth()->user()->load('roles'),
            'bedTypes' => $bedTypes->map(function($bedType) use ($hasBedTypeId, $hasRoomTypeBedTypeId) {
                return [
                'id' => $bedType->id,
                'name' => $bedType->name,
                'code' => $bedType->code,
                'description' => $bedType->description,
                'width_inches' => $bedType->width_inches,
                'length_inches' => $bedType->length_inches,
                'is_active' => $bedType->is_active,
                'sort_order' => $bedType->sort_order,
                'room_count' => $hasBedTypeId ? ($bedType->rooms_count ?? 0) : 0,
                'room_type_count' => $hasRoomTypeBedTypeId ? ($bedType->room_types_count ?? 0) : 0,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/BedTypes/Create', [
            'user' => auth()->user()->load('roles'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bed_types,name',
            'code' => 'nullable|string|max:50|unique:bed_types,code',
            'description' => 'nullable|string',
            'width_inches' => 'nullable|numeric|min:0',
            'length_inches' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        BedType::create($validated);

        return redirect()->route('admin.bed-types.index')->with('success', 'Bed type created successfully');
    }

    public function edit(BedType $bedType)
    {
        return Inertia::render('Admin/BedTypes/Edit', [
            'user' => auth()->user()->load('roles'),
            'bedType' => [
                'id' => $bedType->id,
                'name' => $bedType->name,
                'code' => $bedType->code,
                'description' => $bedType->description,
                'width_inches' => $bedType->width_inches,
                'length_inches' => $bedType->length_inches,
                'is_active' => $bedType->is_active,
                'sort_order' => $bedType->sort_order,
            ],
        ]);
    }

    public function update(Request $request, BedType $bedType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bed_types,name,' . $bedType->id,
            'code' => 'nullable|string|max:50|unique:bed_types,code,' . $bedType->id,
            'description' => 'nullable|string',
            'width_inches' => 'nullable|numeric|min:0',
            'length_inches' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $bedType->update($validated);

        return redirect()->route('admin.bed-types.index')->with('success', 'Bed type updated successfully');
    }

    public function destroy(BedType $bedType)
    {
        $hasBedTypeId = Schema::hasColumn('rooms', 'bed_type_id');
        $hasRoomTypeBedTypeId = Schema::hasColumn('room_types', 'bed_type_id');
        
        if ($hasBedTypeId && $bedType->rooms()->count() > 0) {
            return redirect()->route('admin.bed-types.index')->with('error', 'Cannot delete bed type that is in use');
        }
        
        if ($hasRoomTypeBedTypeId && $bedType->roomTypes()->count() > 0) {
            return redirect()->route('admin.bed-types.index')->with('error', 'Cannot delete bed type that is in use');
        }

        $bedType->delete();

        return redirect()->route('admin.bed-types.index')->with('success', 'Bed type deleted successfully');
    }
}
