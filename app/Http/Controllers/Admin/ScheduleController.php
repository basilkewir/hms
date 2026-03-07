<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use App\Models\WorkShift;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // Determine which week to show (default: current week)
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = (clone $weekStart)->endOfWeek();

        $scheduleStats = $this->calculateScheduleStats($weekStart, $weekEnd);
        $schedule = $this->getWeeklySchedule($weekStart, $weekEnd);
        $scheduleRequests = $this->getScheduleRequests();

        // Staff users who can be scheduled (all active users except pure system/admin accounts)
        $staffUsers = User::where('is_active', true)
            ->where('employment_status', 'active')
            ->where(function($query) {
                $query->whereDoesntHave('roles')
                      ->orWhereHas('roles', function ($q) {
                          $q->whereNotIn('name', ['admin']);
                      });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'department', 'employee_id']);

        // Debug: Log the count of staff users found
        \Log::info('Staff users found for scheduling: ' . $staffUsers->count());
        \Log::info('Staff users data: ' . json_encode($staffUsers->toArray()));

        // Get all active work shifts
        $workShifts = WorkShift::where('is_active', true)
            ->orderBy('start_time')
            ->get(['id', 'name', 'start_time', 'end_time', 'hours', 'is_overnight']);

        return Inertia::render('Admin/Schedules/Index', [
            'user' => auth()->user()->load('roles'),
            'scheduleStats' => $scheduleStats,
            'scheduleData' => $schedule['byEmployee'],
            'scheduleByDay' => $schedule['byDay'],
            'scheduleRequests' => $scheduleRequests,
            'currentWeek' => $this->formatWeekLabel($weekStart, $weekEnd),
            'weekStart' => $weekStart->toDateString(),
            'weekDays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'staffUsers' => $staffUsers,
            'workShifts' => $workShifts,
        ]);
    }

    /**
     * Export weekly schedule as CSV.
     */
    public function export(Request $request)
    {
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = (clone $weekStart)->endOfWeek();

        $schedule = $this->getWeeklySchedule($weekStart, $weekEnd);
        $scheduleData = $schedule['byEmployee'];
        $weekLabel = $this->formatWeekLabel($weekStart, $weekEnd);
        $fileName = 'weekly_schedule_' . $weekStart->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($scheduleData, $weekStart) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, ['Employee', 'Department', 'Date', 'Day', 'Shift Start', 'Shift End', 'Type']);

            $currentDate = $weekStart->copy();

            foreach ($scheduleData as $employee) {
                foreach ($employee['shifts'] as $dayIndex => $shift) {
                    if ($shift) {
                        fputcsv($handle, [
                            $employee['name'],
                            $employee['department'],
                            $currentDate->toDateString(),
                            $currentDate->format('D'),
                            $shift['start'],
                            $shift['end'],
                            $shift['type'],
                        ]);
                    }
                    $currentDate->addDay();
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Render a print-friendly weekly schedule view.
     */
    public function print(Request $request)
    {
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = (clone $weekStart)->endOfWeek();

        $schedule = $this->getWeeklySchedule($weekStart, $weekEnd);
        $scheduleData = $schedule['byEmployee'];

        return Inertia::render('Admin/Schedules/Print', [
            'user' => auth()->user()->load('roles'),
            'scheduleData' => $scheduleData,
            'currentWeek' => $this->formatWeekLabel($weekStart, $weekEnd),
            'weekDays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ]);
    }

    public function generate()
    {
        // Auto-generate schedule logic would go here
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule generation functionality would be implemented here.');
    }

    public function store(Request $request)
    {
        // Check if this is a bulk create request
        if ($request->has('schedules') && is_array($request->schedules)) {
            return $this->storeBulk($request);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'work_shift_id' => 'required|exists:work_shifts,id',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_days' => 'nullable|array',
            'end_date' => 'nullable|date|after_or_equal:date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->is_recurring) {
            $startDate = Carbon::parse($request->date);
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : $startDate->copy()->addMonth();

            $dates = [];
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                if (in_array($currentDate->dayOfWeek, $request->recurring_days)) {
                    $dates[] = $currentDate->toDateString();
                }
                $currentDate->addDay();
            }

            foreach ($dates as $date) {
                EmployeeShift::create([
                    'user_id' => $request->user_id,
                    'work_shift_id' => $request->work_shift_id,
                    'effective_date' => $date,
                    'days_of_week' => $request->recurring_days,
                    'is_active' => true,
                ]);
            }
        } else {
            $date = Carbon::parse($request->date);
            EmployeeShift::create([
                'user_id' => $request->user_id,
                'work_shift_id' => $request->work_shift_id,
                'effective_date' => $request->date,
                // Single specific day – store its dayOfWeek in the JSON column
                'days_of_week' => [$date->dayOfWeek],
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Store multiple schedules at once (bulk create)
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedules' => 'required|array|min:1',
            'schedules.*.user_id' => 'required|exists:users,id',
            'schedules.*.work_shift_id' => 'required|exists:work_shifts,id',
            'schedules.*.date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $created = 0;
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek;

        foreach ($request->schedules as $scheduleData) {
            // Check if schedule already exists to avoid duplicates
            $exists = EmployeeShift::where('user_id', $scheduleData['user_id'])
                ->where('work_shift_id', $scheduleData['work_shift_id'])
                ->where('effective_date', $scheduleData['date'])
                ->where('is_active', true)
                ->exists();

            if (!$exists) {
                EmployeeShift::create([
                    'user_id' => $scheduleData['user_id'],
                    'work_shift_id' => $scheduleData['work_shift_id'],
                    'effective_date' => $scheduleData['date'],
                    'days_of_week' => [$dayOfWeek],
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', "Successfully created {$created} schedule(s).");
    }

    public function update(Request $request, EmployeeShift $schedule)
    {
        $validator = Validator::make($request->all(), [
            'work_shift_id' => 'required|exists:work_shifts,id',
            'effective_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $schedule->update($request->all());

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(EmployeeShift $schedule)
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    public function approveRequest(LeaveRequest $request)
    {
        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Request approved successfully.');
    }

    public function rejectRequest(LeaveRequest $request)
    {
        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Request rejected successfully.');
    }

    protected function calculateScheduleStats(?Carbon $startOfWeek = null, ?Carbon $endOfWeek = null)
    {
        $startOfWeek = $startOfWeek ?? now()->startOfWeek();
        $endOfWeek = $endOfWeek ?? now()->endOfWeek();

        $thisWeekShifts = EmployeeShift::whereBetween('effective_date', [$startOfWeek, $endOfWeek])
            ->where('is_active', true)
            ->count();

        $scheduledStaff = User::whereHas('employeeShifts', function($query) use ($startOfWeek, $endOfWeek) {
            $query->whereBetween('effective_date', [$startOfWeek, $endOfWeek])
                  ->where('is_active', true);
        })->count();

        $conflicts = 0; // Would need complex logic to detect scheduling conflicts
        $totalHours = EmployeeShift::whereBetween('effective_date', [$startOfWeek, $endOfWeek])
            ->where('is_active', true)
            ->with('workShift')
            ->get()
            ->sum(function($shift) {
                return $shift->workShift ? $shift->workShift->hours : 0;
            });

        return [
            'thisWeek' => $thisWeekShifts,
            'scheduledStaff' => $scheduledStaff,
            'conflicts' => $conflicts,
            'totalHours' => $totalHours,
        ];
    }

    protected function getWeeklySchedule(?Carbon $startOfWeek = null, ?Carbon $endOfWeek = null)
    {
        $startOfWeek = $startOfWeek ?? now()->startOfWeek();
        $endOfWeek = $endOfWeek ?? now()->endOfWeek();

        // Get all employee shifts for the week
        $employeeShifts = EmployeeShift::whereBetween('effective_date', [$startOfWeek, $endOfWeek])
            ->where('is_active', true)
            ->with(['user', 'workShift'])
            ->get();

        // Organize by day of week (0 = Sunday, 1 = Monday, etc.)
        $scheduleByDay = [];
        $currentDate = $startOfWeek->copy();

        for ($i = 0; $i < 7; $i++) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
            $dateString = $currentDate->toDateString();
            
            $dayShifts = $employeeShifts->filter(function($shift) use ($dateString, $dayOfWeek) {
                return $shift->effective_date == $dateString ||
                       ($shift->days_of_week && in_array($dayOfWeek, $shift->days_of_week));
            });

            $scheduleByDay[] = [
                'date' => $dateString,
                'dayIndex' => $i,
                'dayOfWeek' => $dayOfWeek,
                'employees' => $dayShifts->map(function($shift) {
                    if (!$shift->workShift || !$shift->user) {
                        return null;
                    }
                    return [
                        'id' => $shift->id,
                        'user_id' => $shift->user_id,
                        'employee_id' => $shift->user->employee_id ?? 'EMP' . $shift->user_id,
                        'name' => $shift->user->full_name ?? ($shift->user->first_name . ' ' . $shift->user->last_name),
                        'department' => $shift->user->department ?? 'general',
                        'shift_id' => $shift->work_shift_id,
                        'shift_name' => $shift->workShift->name,
                        'start' => $shift->workShift->start_time->format('H:i'),
                        'end' => $shift->workShift->end_time->format('H:i'),
                        'hours' => $shift->workShift->hours,
                        'type' => $shift->workShift->is_overnight ? 'night' : 'regular',
                    ];
                })->filter()->values()->all(),
            ];

            $currentDate->addDay();
        }

        // Also return employee-based view for backward compatibility
        $users = User::with(['employeeShifts' => function($query) use ($startOfWeek, $endOfWeek) {
            $query->whereBetween('effective_date', [$startOfWeek, $endOfWeek])
                  ->where('is_active', true)
                  ->with('workShift');
        }])->get();

        $employeeView = $users->map(function($user) use ($startOfWeek) {
            $shifts = [];
            $currentDate = $startOfWeek->copy();

            for ($i = 0; $i < 7; $i++) {
                $shift = $user->employeeShifts->first(function($employeeShift) use ($currentDate) {
                    return $employeeShift->effective_date == $currentDate->toDateString() ||
                           ($employeeShift->days_of_week && in_array($currentDate->dayOfWeek, $employeeShift->days_of_week));
                });

                if ($shift && $shift->workShift) {
                    $shifts[] = [
                        'id' => $shift->id,
                        'shift_id' => $shift->work_shift_id,
                        'start' => $shift->workShift->start_time->format('H:i'),
                        'end' => $shift->workShift->end_time->format('H:i'),
                        'type' => $shift->workShift->is_overnight ? 'night' : 'regular',
                    ];
                } else {
                    $shifts[] = null;
                }

                $currentDate->addDay();
            }

            return [
                'id' => $user->id,
                'name' => $user->full_name ?? ($user->first_name . ' ' . $user->last_name),
                'department' => $user->department ?? 'general',
                'shifts' => $shifts,
            ];
        });

        return [
            'byDay' => $scheduleByDay,
            'byEmployee' => $employeeView,
        ];
    }

    protected function getScheduleRequests()
    {
        return LeaveRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'employee_name' => $request->user->full_name ?? ($request->user->first_name . ' ' . $request->user->last_name),
                    'employee_id' => $request->user->employee_id ?? 'EMP' . $request->user->id,
                    'type' => $request->leave_type,
                    'date_time' => $request->start_date->format('M d, Y') . ' - ' . $request->end_date->format('M d, Y'),
                    'reason' => $request->reason,
                    'status' => $request->status,
                ];
            });
    }

    protected function formatWeekLabel(Carbon $startOfWeek, Carbon $endOfWeek): string
    {
        return $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d, Y');
    }
}
