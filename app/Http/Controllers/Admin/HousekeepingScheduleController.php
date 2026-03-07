<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingSchedule;
use App\Models\HousekeepingScheduleRoom;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class HousekeepingScheduleController extends Controller
{
    /**
     * Display a listing of the schedules.
     */
    public function index(Request $request)
    {
        $query = HousekeepingSchedule::with(['assignedTo', 'createdBy'])
            ->orderBy('start_date', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $schedules = $query->paginate(15)->through(function($schedule) {
            $schedule->room_count = is_array($schedule->room_numbers) ? count($schedule->room_numbers) : 0;
            return $schedule;
        });

        return Inertia::render('Admin/Housekeeping/Schedules/Index', [
            'schedules' => $schedules,
            'filters' => $request->only(['status', 'assigned_to', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create()
    {
        // Get housekeeping staff - users with housekeeping role or position
        $housekeepers = User::whereHas('roles', function($query) {
                $query->where('name', 'housekeeping');
            })
            ->orWhereHas('position', function($query) {
                $query->where('name', 'like', '%housekeeping%')
                      ->orWhere('name', 'like', '%cleaner%')
                      ->orWhere('name', 'like', '%maid%');
            })
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'employee_id' => $user->employee_id ?? $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                ];
            });

        return Inertia::render('Admin/Housekeeping/Schedules/Create', [
            'rooms' => Room::where('is_active', true)
                ->orderBy('room_number')
                ->get(['id', 'room_number', 'status', 'housekeeping_status']),
            'housekeepers' => $housekeepers,
        ]);
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'room_numbers' => 'required|array|min:1',
            'room_numbers.*' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'preferred_start_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $schedule = HousekeepingSchedule::create([
            'assigned_to' => $validated['assigned_to'] ?? null,
            'room_numbers' => $validated['room_numbers'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'preferred_start_time' => $validated['preferred_start_time'] ?? null,
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.housekeeping-schedules.index')
            ->with('success', 'Housekeeping schedule created successfully!');
    }

    /**
     * Display the specified schedule.
     */
    public function show(HousekeepingSchedule $housekeepingSchedule)
    {
        $housekeepingSchedule->load(['assignedTo', 'createdBy']);

        return Inertia::render('Admin/Housekeeping/Schedules/Show', [
            'schedule' => $housekeepingSchedule,
        ]);
    }

    /**
     * Show the form for editing the schedule.
     */
    public function edit(HousekeepingSchedule $housekeepingSchedule)
    {
        $housekeepingSchedule->load([
            'rooms' => function($query) {
                $query->withPivot(['task_type', 'priority', 'status', 'notes']);
            }
        ]);

        // Add computed properties
        $housekeepingSchedule->room_numbers = $housekeepingSchedule->rooms->pluck('room_number')->toArray();

        // Get housekeeping staff
        $housekeepers = User::whereHas('roles', function($query) {
                $query->where('name', 'housekeeping');
            })
            ->orWhereHas('position', function($query) {
                $query->where('name', 'like', '%housekeeping%')
                      ->orWhere('name', 'like', '%cleaner%')
                      ->orWhere('name', 'like', '%maid%');
            })
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'employee_id' => $user->employee_id ?? $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                ];
            });

        return Inertia::render('Admin/Housekeeping/Schedules/Edit', [
            'schedule' => $housekeepingSchedule,
            'rooms' => Room::where('is_active', true)
                ->orderBy('room_number')
                ->get(['id', 'room_number', 'status', 'housekeeping_status']),
            'housekeepers' => $housekeepers,
        ]);
    }

    /**
     * Update the specified schedule.
     */
    public function update(Request $request, HousekeepingSchedule $housekeepingSchedule)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'preferred_start_time' => 'nullable|date_format:H:i',
            'preferred_end_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:pending,active,completed,cancelled',
            'notes' => 'nullable|string',
            'instructions' => 'nullable|string',
            'rooms' => 'required|array|min:1',
            'rooms.*.id' => 'required|exists:rooms,id',
            'rooms.*.task_type' => 'required|in:checkout,cleaning,check_cleaning,stayover,deep_clean,inspection',
            'rooms.*.priority' => 'required|in:low,medium,high,urgent',
            'rooms.*.status' => 'nullable|in:pending,in_progress,completed,skipped',
            'rooms.*.notes' => 'nullable|string',
        ]);

        // Update schedule
        $housekeepingSchedule->update([
            'assigned_to' => $validated['assigned_to'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'preferred_start_time' => $validated['preferred_start_time'] ?? null,
            'preferred_end_time' => $validated['preferred_end_time'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
        ]);

        // Sync rooms
        $existingRoomIds = $housekeepingSchedule->rooms()->pluck('rooms.id')->toArray();
        $newRoomIds = collect($validated['rooms'])->pluck('id')->toArray();

        // Remove rooms that are no longer in the list
        $roomsToRemove = array_diff($existingRoomIds, $newRoomIds);
        if (!empty($roomsToRemove)) {
            HousekeepingScheduleRoom::where('housekeeping_schedule_id', $housekeepingSchedule->id)
                ->whereIn('room_id', $roomsToRemove)
                ->delete();
        }

        // Update or create room assignments
        foreach ($validated['rooms'] as $room) {
            HousekeepingScheduleRoom::updateOrCreate(
                [
                    'housekeeping_schedule_id' => $housekeepingSchedule->id,
                    'room_id' => $room['id'],
                ],
                [
                    'task_type' => $room['task_type'],
                    'priority' => $room['priority'],
                    'status' => $room['status'] ?? 'pending',
                    'notes' => $room['notes'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.housekeeping-schedules.index')
            ->with('success', 'Housekeeping schedule updated successfully!');
    }

    /**
     * Update schedule status.
     */
    public function updateStatus(Request $request, HousekeepingSchedule $housekeepingSchedule)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,active,completed,cancelled',
        ]);

        $housekeepingSchedule->update($validated);

        return redirect()->back()->with('success', 'Schedule status updated!');
    }

    /**
     * Update room task status within a schedule.
     */
    public function updateRoomStatus(Request $request, HousekeepingSchedule $housekeepingSchedule, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,skipped',
            'notes' => 'nullable|string',
        ]);

        $scheduleRoom = HousekeepingScheduleRoom::where('housekeeping_schedule_id', $housekeepingSchedule->id)
            ->where('room_id', $room->id)
            ->firstOrFail();

        $scheduleRoom->update($validated);

        // Update overall schedule status if all rooms are completed
        $pendingRooms = HousekeepingScheduleRoom::where('housekeeping_schedule_id', $housekeepingSchedule->id)
            ->whereNotIn('status', ['completed', 'skipped'])
            ->count();

        if ($pendingRooms === 0 && $housekeepingSchedule->status !== 'completed') {
            $housekeepingSchedule->update(['status' => 'completed']);
        }

        return redirect()->back()->with('success', 'Room task status updated!');
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(HousekeepingSchedule $housekeepingSchedule)
    {
        $housekeepingSchedule->delete();

        return redirect()->route('admin.housekeeping-schedules.index')
            ->with('success', 'Schedule deleted successfully!');
    }

    /**
     * Get schedules for calendar view.
     */
    public function calendar(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $schedules = HousekeepingSchedule::with(['assignedTo', 'rooms'])
            ->dateRange($request->start, $request->end)
            ->get()
            ->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->assignedTo ?
                        $schedule->assignedTo->full_name . ' (' . $schedule->rooms->count() . ' rooms)' :
                        'Unassigned (' . $schedule->rooms->count() . ' rooms)',
                    'start' => $schedule->start_date->format('Y-m-d'),
                    'end' => $schedule->end_date ? $schedule->end_date->addDay()->format('Y-m-d') : null,
                    'status' => $schedule->status,
                    'url' => route('admin.housekeeping-schedules.show', $schedule->id),
                    'backgroundColor' => match($schedule->status) {
                        'pending' => '#f59e0b',
                        'active' => '#3b82f6',
                        'completed' => '#10b981',
                        'cancelled' => '#ef4444',
                        default => '#6b7280',
                    },
                ];
            });

        return response()->json($schedules);
    }

    /**
     * Duplicate a schedule to new dates.
     */
    public function duplicate(Request $request, HousekeepingSchedule $housekeepingSchedule)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $newSchedule = $housekeepingSchedule->replicate();
        $newSchedule->start_date = $validated['start_date'];
        $newSchedule->end_date = $validated['end_date'];
        $newSchedule->status = 'pending';
        $newSchedule->created_by = Auth::id();
        $newSchedule->save();

        // Duplicate room assignments
        foreach ($housekeepingSchedule->scheduleRooms as $scheduleRoom) {
            $newScheduleRoom = $scheduleRoom->replicate();
            $newScheduleRoom->housekeeping_schedule_id = $newSchedule->id;
            $newScheduleRoom->status = 'pending';
            $newScheduleRoom->save();
        }

        return redirect()->route('admin.housekeeping-schedules.show', $newSchedule->id)
            ->with('success', 'Schedule duplicated successfully!');
    }
}
