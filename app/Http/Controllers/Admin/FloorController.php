<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;

class FloorController extends Controller
{
    public function index()
    {
        $floors = Floor::orderBy('sort_order')->orderBy('floor_number')->get();
        
        // Only count if column exists
        $hasFloorId = Schema::hasColumn('rooms', 'floor_id');
        if ($hasFloorId) {
            $floors->loadCount('rooms');
        }

        return Inertia::render('Admin/Floors/Index', [
            'user' => auth()->user()->load('roles'),
            'floors' => $floors->map(function($floor) use ($hasFloorId) {
                return [
                    'id' => $floor->id,
                    'floor_number' => $floor->floor_number,
                    'name' => $floor->name,
                    'description' => $floor->description,
                    'is_active' => $floor->is_active,
                    'sort_order' => $floor->sort_order,
                    'room_count' => $hasFloorId ? ($floor->rooms_count ?? 0) : 0,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Floors/Create', [
            'user' => auth()->user()->load('roles'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'floor_number' => 'required|integer|unique:floors,floor_number',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Floor::create($validated);

        return redirect()->route('admin.floors.index')->with('success', 'Floor created successfully');
    }

    public function edit(Floor $floor)
    {
        return Inertia::render('Admin/Floors/Edit', [
            'user' => auth()->user()->load('roles'),
            'floor' => [
                'id' => $floor->id,
                'floor_number' => $floor->floor_number,
                'name' => $floor->name,
                'description' => $floor->description,
                'is_active' => $floor->is_active,
                'sort_order' => $floor->sort_order,
            ],
        ]);
    }

    public function update(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'floor_number' => 'required|integer|unique:floors,floor_number,' . $floor->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $floor->update($validated);

        return redirect()->route('admin.floors.index')->with('success', 'Floor updated successfully');
    }

    public function destroy(Floor $floor)
    {
        $hasFloorId = Schema::hasColumn('rooms', 'floor_id');
        if ($hasFloorId && $floor->rooms()->count() > 0) {
            return redirect()->route('admin.floors.index')->with('error', 'Cannot delete floor with existing rooms');
        }

        $floor->delete();

        return redirect()->route('admin.floors.index')->with('success', 'Floor deleted successfully');
    }
}
