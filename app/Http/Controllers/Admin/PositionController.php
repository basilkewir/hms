<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = Position::with(['department', 'roles'])->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Admin/Positions/Index', [
            'positions' => $positions,
            'departments' => $departments,
            'user' => auth()->user()->load('roles')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Admin/Positions/Create', [
            'departments' => $departments,
            'user' => auth()->user()->load('roles')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Position::create([
                'name' => $request->name,
                'department_id' => $request->department_id,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return redirect()->route('admin.positions.index')
                ->with('success', 'Position created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create position: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $position = Position::with(['department', 'roles'])->findOrFail($id);

        return Inertia::render('Admin/Positions/Show', [
            'position' => $position,
            'user' => auth()->user()->load('roles')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $position = Position::findOrFail($id);
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Admin/Positions/Edit', [
            'position' => $position,
            'departments' => $departments,
            'user' => auth()->user()->load('roles')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $position->update([
                'name' => $request->name,
                'department_id' => $request->department_id,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return redirect()->route('admin.positions.index')
                ->with('success', 'Position updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update position: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $position = Position::findOrFail($id);
            $position->delete();

            return redirect()->route('admin.positions.index')
                ->with('success', 'Position deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete position: ' . $e->getMessage());
        }
    }

    /**
     * Get positions by department (for API/JSON responses)
     */
    public function getByDepartment($departmentId)
    {
        $positions = Position::where('department_id', $departmentId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($positions);
    }

    /**
     * Get all positions (for API/JSON responses)
     */
    public function getAll()
    {
        $positions = Position::with('department')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($positions);
    }
}
