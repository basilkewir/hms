<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\EmployeeShift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $today = Carbon::today();
        
        $employees = User::with(['roles', 'department', 'position'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $attendanceRecords = Attendance::whereDate('date', $today)
            ->get()
            ->keyBy('user_id');

        $attendance = $employees->map(function ($employee) use ($attendanceRecords, $today) {
            $record = $attendanceRecords->get($employee->id);
            
            return [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'department' => is_object($employee->department) ? $employee->department->name : ($employee->department ?? 'N/A'),
                'position' => is_object($employee->position) ? $employee->position->name : ($employee->position ?? 'N/A'),
                'status' => $record ? $record->status : 'absent',
                'check_in' => $record ? $record->check_in : null,
                'check_out' => $record ? $record->check_out : null,
                'notes' => $record ? $record->notes : null,
                'attendance_id' => $record ? $record->id : null,
            ];
        });

        return Inertia::render('Admin/Attendance/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'attendance' => $attendance,
            'date' => $today->format('Y-m-d')
        ]);
    }

    public function markAttendance(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'notes' => 'nullable|string'
        ]);

        $status = $validated['status'];

        // Resolve date as Carbon instance
        $workDate = Carbon::parse($validated['date'])->startOfDay();

        // Try to find today's active shift for this user
        $employeeShift = EmployeeShift::with('workShift')
            ->where('user_id', $validated['user_id'])
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $workDate);
            })
            ->first();

        // Determine check-in time: use provided time, otherwise shift start, otherwise now
        if (!empty($validated['check_in'])) {
            $checkInTime = $validated['check_in'];
        } elseif ($employeeShift && $employeeShift->workShift && $employeeShift->workShift->start_time) {
            $checkInTime = $employeeShift->workShift->start_time->format('H:i');
        } else {
            $checkInTime = Carbon::now()->format('H:i');
        }

        // Auto-determine if late: if we know a shift start, compare against it; otherwise keep existing 9:00 default
        if ($status === 'present') {
            $checkInCarbon = Carbon::createFromFormat('H:i', $checkInTime);

            if ($employeeShift && $employeeShift->workShift && $employeeShift->workShift->start_time) {
                $shiftStart = Carbon::parse($employeeShift->workShift->start_time->format('H:i'));
                if ($checkInCarbon->gt($shiftStart)) {
                    $status = 'late';
                }
            } elseif ($checkInCarbon->gt(Carbon::createFromTime(9, 0))) {
                // Fallback: treat after 9:00 AM as late when no shift is available
                $status = 'late';
            }
        }

        Attendance::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'date' => $validated['date']
            ],
            [
                'check_in' => $checkInTime,
                'status' => $status,
                'notes' => $validated['notes'] ?? null
            ]
        );

        return back()->with('success', 'Attendance marked successfully');
    }

    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_out' => 'nullable|date_format:H:i'
        ]);

        $attendance = Attendance::where('user_id', $validated['user_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        if (!$attendance) {
            return back()->withErrors(['message' => 'No check-in record found']);
        }

        $workDate = Carbon::parse($validated['date'])->startOfDay();

        // Determine checkout time: provided value, shift end time, or now
        if (!empty($validated['check_out'])) {
            $checkOutTime = $validated['check_out'];
        } else {
            $employeeShift = EmployeeShift::with('workShift')
                ->where('user_id', $validated['user_id'])
                ->where('is_active', true)
                ->whereDate('effective_date', '<=', $workDate)
                ->where(function ($q) use ($workDate) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $workDate);
                })
                ->first();

            if ($employeeShift && $employeeShift->workShift && $employeeShift->workShift->end_time) {
                $checkOutTime = $employeeShift->workShift->end_time->format('H:i');
            } else {
                $checkOutTime = Carbon::now()->format('H:i');
            }
        }

        $attendance->update([
            'check_out' => $checkOutTime,
        ]);

        return back()->with('success', 'Check-out recorded successfully');
    }

    public function bulkMark(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'status' => 'required|in:present,absent,late,half_day,on_leave'
        ]);

        $workDate = Carbon::parse($validated['date'])->startOfDay();

        foreach ($validated['user_ids'] as $userId) {
            $checkInTime = null;

            if ($validated['status'] === 'present') {
                // Try to use shift start if available
                $employeeShift = EmployeeShift::with('workShift')
                    ->where('user_id', $userId)
                    ->where('is_active', true)
                    ->whereDate('effective_date', '<=', $workDate)
                    ->where(function ($q) use ($workDate) {
                        $q->whereNull('end_date')
                          ->orWhereDate('end_date', '>=', $workDate);
                    })
                    ->first();

                if ($employeeShift && $employeeShift->workShift && $employeeShift->workShift->start_time) {
                    $checkInTime = $employeeShift->workShift->start_time->format('H:i');
                } else {
                    $checkInTime = Carbon::now()->format('H:i');
                }
            }

            Attendance::updateOrCreate(
                [
                    'user_id' => $userId,
                    'date' => $validated['date']
                ],
                [
                    'status' => $validated['status'],
                    'check_in' => $checkInTime,
                ]
            );
        }

        return back()->with('success', 'Bulk attendance marked successfully');
    }
}
