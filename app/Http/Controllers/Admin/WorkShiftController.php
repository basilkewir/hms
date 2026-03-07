<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkShift;
use App\Models\EmployeeShift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WorkShiftController extends Controller
{
    public function index()
    {
        $workShifts = WorkShift::withCount('employeeShifts')->get();
        $shiftStats = $this->calculateShiftStats();

        // Staff users who can be scheduled (all active users except pure system/admin accounts)
        $staffUsers = User::active()
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('name', ['admin']);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'department', 'employee_id']);

        return Inertia::render('Admin/WorkShifts/Index', [
            'user' => auth()->user()->load('roles'),
            'workShifts' => $workShifts,
            'shiftStats' => $shiftStats,
            'shiftTemplates' => $workShifts->map(function($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => $shift->start_time->format('H:i'),
                    'end_time' => $shift->end_time->format('H:i'),
                    'duration' => $shift->hours,
                    'type' => $shift->is_overnight ? 'night' : 'regular',
                    'department' => 'general',
                ];
            }),
            'currentShifts' => $this->getCurrentShifts(),
            'staffUsers' => $staffUsers,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'hours' => 'required|numeric|min:0',
            'break_minutes' => 'nullable|numeric|min:0',
            'is_overnight' => 'boolean',
            'is_active' => 'boolean',
            'user_id' => 'nullable|exists:users,id', // Employee assignment
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // First create or find the work shift template
        $workShift = WorkShift::updateOrCreate([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'hours' => $request->hours,
            'break_minutes' => $request->break_minutes ?? 0,
            'is_overnight' => $request->is_overnight ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        // If a user_id is provided, create an employee shift assignment
        if ($request->user_id) {
            EmployeeShift::create([
                'user_id' => $request->user_id,
                'work_shift_id' => $workShift->id,
                'effective_date' => $request->date,
                'end_date' => null, // Ongoing assignment
                'days_of_week' => null, // Specific date assignment
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    public function update(Request $request, WorkShift $workShift)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'hours' => 'required|numeric|min:0',
            'break_minutes' => 'nullable|numeric|min:0',
            'is_overnight' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $workShift->update($request->all());

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Work shift updated successfully.');
    }

    public function destroy(WorkShift $workShift)
    {
        $workShift->delete();

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Work shift deleted successfully.');
    }

    public function assignShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'work_shift_id' => 'required|exists:work_shifts,id',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:effective_date',
            'days_of_week' => 'required|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        EmployeeShift::create($request->all());

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Shift assigned successfully.');
    }

    public function updateAssignment(Request $request, EmployeeShift $employeeShift)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'work_shift_id' => 'required|exists:work_shifts,id',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:effective_date',
            'days_of_week' => 'required|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employeeShift->update($request->all());

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Shift assignment updated successfully.');
    }

    public function cancelAssignment(EmployeeShift $employeeShift)
    {
        $employeeShift->delete();

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Shift assignment cancelled successfully.');
    }

    protected function calculateShiftStats()
    {
        $totalShifts = WorkShift::count();
        $activeToday = EmployeeShift::where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function($query) {
                $query->where('end_date', '>=', now())
                      ->orWhereNull('end_date');
            })
            ->count();

        $uncoveredShifts = 0; // Would need more complex logic to calculate
        $overtimeHours = 0; // Would need to calculate from time entries

        return [
            'total' => $totalShifts,
            'activeToday' => $activeToday,
            'uncovered' => $uncoveredShifts,
            'overtimeHours' => $overtimeHours,
        ];
    }

    protected function getCurrentShifts()
    {
        $today = now()->toDateString();

        return EmployeeShift::with(['user', 'workShift'])
            ->where('is_active', true)
            ->where('effective_date', '<=', $today)
            ->where(function($query) use ($today) {
                $query->where('end_date', '>=', $today)
                      ->orWhereNull('end_date');
            })
            ->where(function($query) {
                $query->whereJsonContains('days_of_week', now()->dayOfWeek)
                      ->orWhereNull('days_of_week');
            })
            ->get()
            ->map(function($shift) {
                $user = $shift->user;
                $workShift = $shift->workShift;

                // Build a proper full name from the User model (first_name/last_name accessors)
                $fullName = $user?->full_name
                    ?? trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? ''));

                if ($fullName === '') {
                    $fullName = 'Unknown Employee';
                }

                return [
                    'id' => $shift->id,
                    'employee_name' => $fullName,
                    'employee_id' => $user?->employee_id ?? ('EMP' . ($user?->id ?? '')),
                    'department' => $user?->department ?? 'general',
                    'start_time' => $workShift?->start_time?->format('H:i') ?? '',
                    'end_time' => $workShift?->end_time?->format('H:i') ?? '',
                    'duration' => $workShift?->hours ?? 0,
                    'type' => $workShift?->is_overnight ? 'night' : 'regular',
                    'status' => 'active',
                ];
            });
    }
}
