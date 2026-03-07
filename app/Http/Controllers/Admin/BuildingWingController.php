<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuildingWing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;

class BuildingWingController extends Controller
{
    public function index()
    {
        $wings = BuildingWing::orderBy('sort_order')->orderBy('name')->get();
        
        // Only count if column exists
        $hasBuildingWingId = Schema::hasColumn('rooms', 'building_wing_id');
        if ($hasBuildingWingId) {
            $wings->loadCount('rooms');
        }

        return Inertia::render('Admin/BuildingWings/Index', [
            'user' => auth()->user()->load('roles'),
            'wings' => $wings->map(function($wing) use ($hasBuildingWingId) {
                return [
                'id' => $wing->id,
                'name' => $wing->name,
                'code' => $wing->code,
                'description' => $wing->description,
                'is_active' => $wing->is_active,
                'sort_order' => $wing->sort_order,
                'room_count' => $hasBuildingWingId ? ($wing->rooms_count ?? 0) : 0,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/BuildingWings/Create', [
            'user' => auth()->user()->load('roles'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:building_wings,name',
            'code' => 'nullable|string|max:50|unique:building_wings,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        BuildingWing::create($validated);

        return redirect()->route('admin.building-wings.index')->with('success', 'Building wing created successfully');
    }

    public function edit(BuildingWing $buildingWing)
    {
        return Inertia::render('Admin/BuildingWings/Edit', [
            'user' => auth()->user()->load('roles'),
            'wing' => [
                'id' => $buildingWing->id,
                'name' => $buildingWing->name,
                'code' => $buildingWing->code,
                'description' => $buildingWing->description,
                'is_active' => $buildingWing->is_active,
                'sort_order' => $buildingWing->sort_order,
            ],
        ]);
    }

    public function update(Request $request, BuildingWing $buildingWing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:building_wings,name,' . $buildingWing->id,
            'code' => 'nullable|string|max:50|unique:building_wings,code,' . $buildingWing->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $buildingWing->update($validated);

        return redirect()->route('admin.building-wings.index')->with('success', 'Building wing updated successfully');
    }

    public function destroy(BuildingWing $buildingWing)
    {
        $hasBuildingWingId = Schema::hasColumn('rooms', 'building_wing_id');
        if ($hasBuildingWingId && $buildingWing->rooms()->count() > 0) {
            return redirect()->route('admin.building-wings.index')->with('error', 'Cannot delete building wing with existing rooms');
        }

        $buildingWing->delete();

        return redirect()->route('admin.building-wings.index')->with('success', 'Building wing deleted successfully');
    }
}
