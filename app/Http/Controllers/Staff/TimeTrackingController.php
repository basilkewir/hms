<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeTrackingController extends Controller
{
    public function clock()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $entry = TimeEntry::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->orderByDesc('clock_in_time')
            ->first();

        [$hoursWorkedToday, $totalBreakTime, $netHours] = $this->calculateTodayStats($entry);

        return Inertia::render('Staff/TimeTracking/Clock', [
            'user' => $user->load('roles'),
            'timeEntry' => $entry ? $this->formatEntry($entry) : null,
            'isClockedIn' => $entry && $entry->clock_in_time && !$entry->clock_out_time,
            'isOnBreak' => $entry && $entry->break_start_time && !$entry->break_end_time,
            'hoursWorkedToday' => $hoursWorkedToday,
            'totalBreakTime' => $totalBreakTime,
            'netHours' => $netHours,
            'todaysActivity' => $this->buildActivity($entry),
        ]);
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $entry = TimeEntry::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'status' => 'active',
            ]
        );

        if ($entry->clock_in_time) {
            return back()->with('error', 'You are already clocked in.');
        }

        $entry->update([
            'clock_in_time' => now(),
            'status' => 'active',
        ]);

        return back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(Request $request)
    {
        $entry = $this->getTodayEntry();

        if (!$entry || !$entry->clock_in_time) {
            return back()->with('error', 'You are not clocked in.');
        }

        if ($entry->clock_out_time) {
            return back()->with('error', 'You are already clocked out.');
        }

        $entry->update([
            'clock_out_time' => now(),
            'status' => 'completed',
        ]);

        $this->recalculateHours($entry);

        return back()->with('success', 'Clocked out successfully.');
    }

    public function startBreak()
    {
        $entry = $this->getTodayEntry();

        if (!$entry || !$entry->clock_in_time || $entry->clock_out_time) {
            return back()->with('error', 'You must be clocked in to start a break.');
        }

        if ($entry->break_start_time && !$entry->break_end_time) {
            return back()->with('error', 'You are already on a break.');
        }

        $entry->update([
            'break_start_time' => now(),
        ]);

        return back()->with('success', 'Break started.');
    }

    public function endBreak()
    {
        $entry = $this->getTodayEntry();

        if (!$entry || !$entry->break_start_time) {
            return back()->with('error', 'No break in progress.');
        }

        if ($entry->break_end_time) {
            return back()->with('error', 'Break already ended.');
        }

        $entry->update([
            'break_end_time' => now(),
        ]);

        $this->recalculateHours($entry);

        return back()->with('success', 'Break ended.');
    }

    public function timesheet(Request $request)
    {
        $user = auth()->user();
        $week = $request->get('week', 'current');

        [$startDate, $endDate] = $this->resolveWeekRange($week);

        $entries = TimeEntry::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        $weeklyStats = [
            'totalHours' => round($entries->sum('total_hours'), 2) . 'h',
            'regularHours' => round($entries->sum('regular_hours'), 2) . 'h',
            'overtimeHours' => round($entries->sum('overtime_hours'), 2) . 'h',
            'breakHours' => round($entries->sum('break_hours'), 2) . 'h',
        ];

        $timeEntries = $entries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->work_date->toDateString(),
                'clockIn' => $entry->clock_in_time ? $entry->clock_in_time->format('H:i') : null,
                'clockOut' => $entry->clock_out_time ? $entry->clock_out_time->format('H:i') : null,
                'breakTime' => $entry->break_hours ? round($entry->break_hours, 2) . 'h' : '0h',
                'totalHours' => $entry->total_hours ? round($entry->total_hours, 2) . 'h' : '0h',
                'status' => $entry->status,
            ];
        });

        return Inertia::render('Staff/TimeTracking/Timesheet', [
            'user' => $user->load('roles'),
            'selectedWeek' => $week,
            'weeklyStats' => $weeklyStats,
            'timeEntries' => $timeEntries,
        ]);
    }

    public function schedule(Request $request)
    {
        $user = auth()->user();
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : now()->startOfWeek();

        $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $shifts = EmployeeShift::where('user_id', $user->id)
            ->whereBetween('effective_date', [$weekStart, (clone $weekStart)->endOfWeek()])
            ->where('is_active', true)
            ->with('workShift')
            ->get();

        $schedule = [];
        $currentDate = $weekStart->copy();

        for ($i = 0; $i < 7; $i++) {
            $shift = $shifts->first(function ($entry) use ($currentDate) {
                return $entry->effective_date->toDateString() === $currentDate->toDateString()
                    || ($entry->days_of_week && in_array($currentDate->dayOfWeek, $entry->days_of_week));
            });

            if ($shift && $shift->workShift) {
                $schedule[] = [
                    'date' => $currentDate->toDateString(),
                    'day' => $weekDays[$i],
                    'start' => $shift->workShift->start_time->format('H:i'),
                    'end' => $shift->workShift->end_time->format('H:i'),
                    'shift_name' => $shift->workShift->name,
                ];
            } else {
                $schedule[] = [
                    'date' => $currentDate->toDateString(),
                    'day' => $weekDays[$i],
                    'start' => null,
                    'end' => null,
                    'shift_name' => 'Off',
                ];
            }

            $currentDate->addDay();
        }

        return Inertia::render('Staff/TimeTracking/Schedule', [
            'user' => $user->load('roles'),
            'weekStart' => $weekStart->toDateString(),
            'schedule' => $schedule,
        ]);
    }

    private function getTodayEntry()
    {
        return TimeEntry::where('user_id', auth()->id())
            ->whereDate('work_date', Carbon::today())
            ->orderByDesc('clock_in_time')
            ->first();
    }

    private function recalculateHours(TimeEntry $entry)
    {
        if (!$entry->clock_in_time || !$entry->clock_out_time) {
            return;
        }

        $totalMinutes = $entry->clock_in_time->diffInMinutes($entry->clock_out_time);

        if ($entry->break_start_time && $entry->break_end_time) {
            $breakMinutes = $entry->break_start_time->diffInMinutes($entry->break_end_time);
            $entry->break_hours = round($breakMinutes / 60, 2);
            $totalMinutes -= $breakMinutes;
        }

        $entry->total_hours = round($totalMinutes / 60, 2);
        $entry->regular_hours = min($entry->total_hours, 8);
        $entry->overtime_hours = max($entry->total_hours - 8, 0);
        $entry->save();
    }

    private function calculateTodayStats(?TimeEntry $entry): array
    {
        if (!$entry || !$entry->clock_in_time) {
            return ['0h', '0m', '0h'];
        }

        $endTime = $entry->clock_out_time ?: now();
        $totalMinutes = $entry->clock_in_time->diffInMinutes($endTime);

        $breakMinutes = 0;
        if ($entry->break_start_time) {
            $breakEnd = $entry->break_end_time ?: now();
            $breakMinutes = $entry->break_start_time->diffInMinutes($breakEnd);
        }

        $netMinutes = max($totalMinutes - $breakMinutes, 0);

        return [
            round($totalMinutes / 60, 2) . 'h',
            round($breakMinutes) . 'm',
            round($netMinutes / 60, 2) . 'h'
        ];
    }

    private function buildActivity(?TimeEntry $entry): array
    {
        if (!$entry) return [];

        $activity = [];
        if ($entry->clock_in_time) {
            $activity[] = [
                'id' => 1,
                'type' => 'clock_in',
                'action' => 'Clocked In',
                'time' => $entry->clock_in_time->format('h:i A'),
                'duration' => ''
            ];
        }
        if ($entry->break_start_time) {
            $activity[] = [
                'id' => 2,
                'type' => 'break_start',
                'action' => 'Started Break',
                'time' => $entry->break_start_time->format('h:i A'),
                'duration' => ''
            ];
        }
        if ($entry->break_end_time) {
            $activity[] = [
                'id' => 3,
                'type' => 'break_end',
                'action' => 'Ended Break',
                'time' => $entry->break_end_time->format('h:i A'),
                'duration' => ''
            ];
        }
        if ($entry->clock_out_time) {
            $activity[] = [
                'id' => 4,
                'type' => 'clock_out',
                'action' => 'Clocked Out',
                'time' => $entry->clock_out_time->format('h:i A'),
                'duration' => ''
            ];
        }

        return $activity;
    }

    private function resolveWeekRange(string $week): array
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        if ($week === 'last') {
            $start = Carbon::now()->subWeek()->startOfWeek();
            $end = Carbon::now()->subWeek()->endOfWeek();
        } elseif ($week === 'two_weeks') {
            $start = Carbon::now()->subWeeks(2)->startOfWeek();
            $end = Carbon::now()->subWeeks(2)->endOfWeek();
        }

        return [$start, $end];
    }
}
