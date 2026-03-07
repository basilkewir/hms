<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeTrackingController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('roles');

        // Get today's date
        $today = Carbon::today();

        // Calculate time tracking stats
        $timeStats = $this->getTimeStats($today);

        // Get current status counts
        $currentStatus = $this->getCurrentStatus();

        // Get today's time records
        $timeRecords = $this->getTimeRecords($today);

        return inertia('Admin/TimeTracking/Index', [
            'user' => $user,
            'timeStats' => $timeStats,
            'currentStatus' => $currentStatus,
            'timeRecords' => $timeRecords,
        ]);
    }

    private function getTimeStats($date)
    {
        $timeEntries = TimeEntry::whereDate('work_date', $date)->get();

        return [
            'totalHoursToday' => $timeEntries->sum('total_hours'),
            'employeesPresent' => $timeEntries->whereNotNull('clock_in_time')->count(),
            'lateArrivals' => $timeEntries->where('is_late', true)->count(),
            'overtimeHours' => $timeEntries->sum('overtime_hours'),
        ];
    }

    private function getCurrentStatus()
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // Count employees by status
        $clockedIn = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->count();

        $onBreak = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('break_start_time')
            ->whereNull('break_end_time')
            ->count();

        $clockedOut = TimeEntry::whereDate('work_date', $today)
            ->whereNotNull('clock_out_time')
            ->count();

        return [
            'clockedIn' => $clockedIn,
            'onBreak' => $onBreak,
            'clockedOut' => $clockedOut,
        ];
    }

    private function getTimeRecords($date)
    {
        return TimeEntry::with(['user', 'workShift'])
            ->whereDate('work_date', $date)
            ->orderBy('clock_in_time')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'employee_name' => $entry->user->name,
                    'employee_id' => $entry->user->employee_id ?? 'EMP' . $entry->user_id,
                    'department' => $entry->user->department ?? 'General',
                    'clock_in' => $entry->clock_in_time ? $entry->clock_in_time->format('h:i A') : null,
                    'clock_out' => $entry->clock_out_time ? $entry->clock_out_time->format('h:i A') : null,
                    'hours_worked' => round($entry->total_hours, 2),
                    'status' => $this->determineStatus($entry),
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

    public function export()
    {
        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $today = Carbon::today();

        $query = TimeEntry::with(['user', 'workShift'])->orderBy('work_date');
        if ($startDate && $endDate) {
            $query->whereBetween('work_date', [$startDate, $endDate]);
        } else {
            $query->whereDate('work_date', $today);
        }

        $entries = $query->get();

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, [
                'Employee',
                'Employee ID',
                'Department',
                'Work Date',
                'Clock In',
                'Clock Out',
                'Hours Worked',
                'Status'
            ]);
            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->user?->name ?? 'N/A',
                    $entry->user?->employee_id ?? ('EMP' . $entry->user_id),
                    $entry->user?->department ?? 'General',
                    optional($entry->work_date)->format('Y-m-d'),
                    $entry->clock_in_time?->format('H:i') ?? '',
                    $entry->clock_out_time?->format('H:i') ?? '',
                    round($entry->total_hours ?? 0, 2),
                    $this->determineStatus($entry),
                ]);
            }
            fclose($handle);
        }, 'time-tracking.csv');
    }
}
