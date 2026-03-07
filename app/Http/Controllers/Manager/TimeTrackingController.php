<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class TimeTrackingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get date from request or use today
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        // Calculate time tracking stats
        $timeStats = $this->getTimeStats($date);

        // Get current status counts
        $currentStatus = $this->getCurrentStatus($date);

        // Get time records for the selected date
        $timeRecords = $this->getTimeRecords($date, $request);

        // Get available departments for filtering
        $departments = User::active()
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();

        return Inertia::render('Manager/Staff/TimeTracking', [
            'user' => $user->load('roles'),
            'timeStats' => $timeStats,
            'currentStatus' => $currentStatus,
            'timeRecords' => $timeRecords,
            'selectedDate' => $date->toDateString(),
            'departments' => $departments,
            'filters' => [
                'department' => $request->input('department'),
                'status' => $request->input('status'),
            ],
        ]);
    }

    private function getTimeStats($date)
    {
        $timeEntries = TimeEntry::whereDate('work_date', $date)->get();

        return [
            'totalHoursToday' => round($timeEntries->sum('total_hours'), 2),
            'employeesPresent' => $timeEntries->whereNotNull('clock_in_time')->unique('user_id')->count(),
            'lateArrivals' => $timeEntries->where('is_late', true)->count(),
            'overtimeHours' => round($timeEntries->sum('overtime_hours'), 2),
        ];
    }

    private function getCurrentStatus($date)
    {
        // Count employees by status for the given date
        $clockedIn = TimeEntry::whereDate('work_date', $date)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->count();

        $onBreak = TimeEntry::whereDate('work_date', $date)
            ->whereNotNull('break_start_time')
            ->whereNull('break_end_time')
            ->count();

        $clockedOut = TimeEntry::whereDate('work_date', $date)
            ->whereNotNull('clock_out_time')
            ->count();

        return [
            'clockedIn' => $clockedIn,
            'onBreak' => $onBreak,
            'clockedOut' => $clockedOut,
        ];
    }

    private function getTimeRecords($date, Request $request)
    {
        $query = TimeEntry::with(['user', 'workShift'])
            ->whereDate('work_date', $date);

        // Apply department filter
        if ($request->filled('department')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('department', $request->input('department'));
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            switch ($status) {
                case 'working':
                    $query->whereNotNull('clock_in_time')
                          ->whereNull('clock_out_time');
                    break;
                case 'on_break':
                    $query->whereNotNull('break_start_time')
                          ->whereNull('break_end_time');
                    break;
                case 'completed':
                    $query->whereNotNull('clock_out_time');
                    break;
            }
        }

        return $query->orderBy('clock_in_time')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'employee_name' => $entry->user->full_name ?? ($entry->user->first_name . ' ' . $entry->user->last_name),
                    'employee_id' => $entry->user->employee_id ?? 'EMP' . $entry->user_id,
                    'department' => $entry->user->department ?? 'General',
                    'clock_in' => $entry->clock_in_time ? $entry->clock_in_time->format('h:i A') : null,
                    'clock_out' => $entry->clock_out_time ? $entry->clock_out_time->format('h:i A') : null,
                    'break_start' => $entry->break_start_time ? $entry->break_start_time->format('h:i A') : null,
                    'break_end' => $entry->break_end_time ? $entry->break_end_time->format('h:i A') : null,
                    'regular_hours' => round($entry->regular_hours ?? 0, 2),
                    'overtime_hours' => round($entry->overtime_hours ?? 0, 2),
                    'total_hours' => round($entry->total_hours ?? 0, 2),
                    'hours_worked' => round($entry->total_hours ?? 0, 2),
                    'status' => $this->determineStatus($entry),
                    'is_late' => $entry->is_late,
                    'late_minutes' => $entry->late_minutes,
                    'notes' => $entry->notes,
                    'work_date' => $entry->work_date->format('Y-m-d'),
                ];
            });
    }

    private function determineStatus($entry)
    {
        if ($entry->clock_out_time) {
            return 'completed';
        } elseif ($entry->break_start_time && !$entry->break_end_time) {
            return 'on_break';
        } elseif ($entry->clock_in_time) {
            return 'working';
        } else {
            return 'absent';
        }
    }

    public function show(TimeEntry $timeEntry)
    {
        $timeEntry->load(['user', 'workShift', 'approver']);

        return Inertia::render('Manager/Staff/TimeEntryDetails', [
            'user' => Auth::user()->load('roles'),
            'timeEntry' => [
                'id' => $timeEntry->id,
                'employee_name' => $timeEntry->user->full_name ?? ($timeEntry->user->first_name . ' ' . $timeEntry->user->last_name),
                'employee_id' => $timeEntry->user->employee_id ?? 'EMP' . $timeEntry->user_id,
                'department' => $timeEntry->user->department ?? 'General',
                'work_date' => $timeEntry->work_date->format('M d, Y'),
                'shift_name' => $timeEntry->workShift->name ?? 'N/A',
                'clock_in' => $timeEntry->clock_in_time ? $timeEntry->clock_in_time->format('h:i A') : null,
                'clock_out' => $timeEntry->clock_out_time ? $timeEntry->clock_out_time->format('h:i A') : null,
                'break_start' => $timeEntry->break_start_time ? $timeEntry->break_start_time->format('h:i A') : null,
                'break_end' => $timeEntry->break_end_time ? $timeEntry->break_end_time->format('h:i A') : null,
                'regular_hours' => round($timeEntry->regular_hours ?? 0, 2),
                'overtime_hours' => round($timeEntry->overtime_hours ?? 0, 2),
                'break_hours' => round($timeEntry->break_hours ?? 0, 2),
                'total_hours' => round($timeEntry->total_hours ?? 0, 2),
                'is_late' => $timeEntry->is_late,
                'late_minutes' => $timeEntry->late_minutes,
                'is_early_out' => $timeEntry->is_early_out,
                'early_out_minutes' => $timeEntry->early_out_minutes,
                'notes' => $timeEntry->notes,
                'admin_notes' => $timeEntry->admin_notes,
                'approved_by' => $timeEntry->approver ? $timeEntry->approver->name : null,
                'approved_at' => $timeEntry->approved_at ? $timeEntry->approved_at->format('M d, Y h:i A') : null,
            ],
        ]);
    }

    public function update(Request $request, TimeEntry $timeEntry)
    {
        $validator = Validator::make($request->all(), [
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update times if provided
        $workDate = $timeEntry->work_date;
        
        if ($request->filled('clock_in_time')) {
            $timeEntry->clock_in_time = Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->clock_in_time);
        }

        if ($request->filled('clock_out_time')) {
            $timeEntry->clock_out_time = Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->clock_out_time);
        }

        if ($request->filled('break_start_time')) {
            $timeEntry->break_start_time = Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->break_start_time);
        }

        if ($request->filled('break_end_time')) {
            $timeEntry->break_end_time = Carbon::parse($workDate->format('Y-m-d') . ' ' . $request->break_end_time);
        }

        if ($request->filled('admin_notes')) {
            $timeEntry->admin_notes = $request->admin_notes;
        }

        // Recalculate hours
        if ($timeEntry->clock_in_time && $timeEntry->clock_out_time) {
            $totalMinutes = $timeEntry->clock_in_time->diffInMinutes($timeEntry->clock_out_time);
            
            // Subtract break time if exists
            if ($timeEntry->break_start_time && $timeEntry->break_end_time) {
                $breakMinutes = $timeEntry->break_start_time->diffInMinutes($timeEntry->break_end_time);
                $totalMinutes -= $breakMinutes;
                $timeEntry->break_hours = round($breakMinutes / 60, 2);
            }

            $timeEntry->total_hours = round($totalMinutes / 60, 2);

            // Calculate overtime (anything over 8 hours)
            if ($timeEntry->total_hours > 8) {
                $timeEntry->overtime_hours = round($timeEntry->total_hours - 8, 2);
                $timeEntry->regular_hours = 8;
            } else {
                $timeEntry->regular_hours = $timeEntry->total_hours;
                $timeEntry->overtime_hours = 0;
            }
        }

        $timeEntry->save();

        return redirect()->route('manager.staff.time-tracking')
            ->with('success', 'Time entry updated successfully.');
    }

    public function export(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $timeRecords = $this->getTimeRecords($date, $request);
        $fileName = 'time_tracking_' . $date->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($timeRecords) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Employee Name',
                'Employee ID',
                'Department',
                'Clock In',
                'Clock Out',
                'Break Start',
                'Break End',
                'Regular Hours',
                'Overtime Hours',
                'Total Hours',
                'Status',
                'Late',
                'Notes'
            ]);

            // Data rows
            foreach ($timeRecords as $record) {
                fputcsv($handle, [
                    $record['employee_name'],
                    $record['employee_id'],
                    $record['department'],
                    $record['clock_in'] ?? '-',
                    $record['clock_out'] ?? '-',
                    $record['break_start'] ?? '-',
                    $record['break_end'] ?? '-',
                    $record['regular_hours'],
                    $record['overtime_hours'],
                    $record['total_hours'],
                    ucfirst(str_replace('_', ' ', $record['status'])),
                    $record['is_late'] ? 'Yes' : 'No',
                    $record['notes'] ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function approve(TimeEntry $timeEntry)
    {
        $timeEntry->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Time entry approved successfully.');
    }
}
