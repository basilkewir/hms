<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Carbon\Carbon;

class HousekeepingTaskController extends Controller
{
    public function index()
    {
        $tasks = HousekeepingTask::with(['room', 'assignedTo', 'inspectedBy'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('priority', 'desc')
            ->paginate(30)
            ->through(function($task) {
                // Auto-update task status based on room status
                if ($task->room) {
                    $room = $task->room;

                    // If room is marked as clean, update pending tasks to completed
                    if ($task->status === 'pending' && $room->housekeeping_status === 'clean') {
                        $task->status = 'completed';
                        $task->actual_minutes = $task->estimated_minutes; // Mark as completed with estimated time
                        $task->completed_at = now();
                        // Update the database
                        \App\Models\HousekeepingTask::where('id', $task->id)->update([
                            'status' => 'completed',
                            'actual_minutes' => $task->estimated_minutes,
                            'completed_at' => now()
                        ]);
                    }
                }

                // Format scheduled_date for display (user-friendly)
                if ($task->scheduled_date) {
                    try {
                        $task->scheduled_date = \Carbon\Carbon::parse($task->scheduled_date)->format('M d, Y');
                    } catch (\Exception $e) {
                        $task->scheduled_date = 'Invalid Date';
                    }
                }

                // Format scheduled_time for display (user-friendly)
                if ($task->scheduled_time) {
                    try {
                        $task->scheduled_time = \Carbon\Carbon::parse($task->scheduled_time)->format('g:i A');
                    } catch (\Exception $e) {
                        $task->scheduled_time = 'Invalid Time';
                    }
                }

                return $task;
            });

        // Calculate stats
        $total = HousekeepingTask::count();
        $pending = HousekeepingTask::where('status', 'pending')->count();
        $inProgress = HousekeepingTask::where('status', 'in_progress')->count();
        $completed = HousekeepingTask::where('status', 'completed')->count();
        $today = HousekeepingTask::whereDate('scheduled_date', today())->count();

        $stats = [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'today' => $today,
        ];

        return Inertia::render('Admin/HousekeepingTasks/Index', [
            'tasks' => $tasks,
            'stats' => $stats,
            'rooms' => Room::where('is_active', true)->get(['id', 'room_number']),
            'users' => User::whereHas('roles', function($query) {
                $query->where('name', 'housekeeping');
            })->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function create()
    {
        $rooms = Room::where('is_active', true)
            ->with('roomType')
            ->get(['id', 'room_number', 'status', 'housekeeping_status', 'room_type_id']);

        $housekeepers = User::where('employment_status', 'active')
            ->where(function ($q) {
                $q->where('position', 'Housekeeper')
                  ->orWhereHas('position', fn($inner) => $inner->where('name', 'Housekeeper'));
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($user) => [
                'id'   => $user->id,
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Staff #' . $user->id,
            ]);

        return Inertia::render('Admin/HousekeepingTasks/Create', [
            'rooms' => $rooms,
            'housekeepers' => $housekeepers,
        ]);
    }

    public function edit(HousekeepingTask $housekeepingTask)
    {
        $housekeepingTask->load(['room', 'assignedTo', 'inspectedBy']);

        // Format scheduled_date for display
        if ($housekeepingTask->scheduled_date) {
            $housekeepingTask->scheduled_date = \Carbon\Carbon::parse($housekeepingTask->scheduled_date)->format('Y-m-d');
        }

        // Format scheduled_time for display
        if ($housekeepingTask->scheduled_time) {
            $housekeepingTask->scheduled_time = \Carbon\Carbon::parse($housekeepingTask->scheduled_time)->format('H:i');
        }

        $housekeepers = User::where('employment_status', 'active')
            ->where(function ($q) {
                $q->where('position', 'Housekeeper')
                  ->orWhereHas('position', fn($inner) => $inner->where('name', 'Housekeeper'));
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($user) => [
                'id'   => $user->id,
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Staff #' . $user->id,
            ]);

        return Inertia::render('Admin/HousekeepingTasks/Edit', [
            'task'         => $housekeepingTask,
            'rooms'        => Room::where('is_active', true)->with('roomType')->get(['id', 'room_number', 'status', 'housekeeping_status', 'room_type_id']),
            'housekeepers' => $housekeepers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'assigned_to' => 'nullable|exists:users,id',
            'task_type' => 'required|in:checkout,cleaning,check_cleaning,stayover,deep_clean,inspection,maintenance',
            'priority' => 'required|in:low,normal,high,urgent',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'estimated_minutes' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';

        // Create the main task
        $mainTask = HousekeepingTask::create($validated);

        // Load room relationship
        $mainTask->load('room');

        // Update room status if needed
        if ($mainTask->room) {
            $room = $mainTask->room;

            // If room is available and a cleaning task is assigned, change status to cleaning
            if (in_array($validated['task_type'], ['cleaning', 'deep_clean'])) {
                // If room is available, change to cleaning
                if ($room->status === 'available') {
                    $room->update([
                        'housekeeping_status' => 'dirty',
                        'status' => 'cleaning'
                    ]);
                }
                // If room is occupied, just update housekeeping status
                elseif ($room->status === 'occupied') {
                    $room->update(['housekeeping_status' => 'dirty']);
                }
            }
        }

        return redirect()->route('admin.housekeeping-tasks.index')
            ->with('success', 'Housekeeping task created successfully!');
    }

    public function update(Request $request, HousekeepingTask $housekeepingTask)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'assigned_to' => 'nullable|exists:users,id',
            'task_type' => 'required|in:checkout,cleaning,check_cleaning,stayover,deep_clean,inspection,maintenance',
            'priority' => 'required|in:low,normal,high,urgent',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'estimated_minutes' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $housekeepingTask->update($validated);

        return redirect()->route('admin.housekeeping-tasks.index')
            ->with('success', 'Housekeeping task updated successfully!');
    }

    public function updateStatus(Request $request, HousekeepingTask $housekeepingTask)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,in_progress,completed,skipped',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Update status
        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_progress' && !$housekeepingTask->started_at) {
                $validated['started_at'] = now();
            }

            if ($validated['status'] === 'completed' && !$housekeepingTask->completed_at) {
                $validated['completed_at'] = now();
                if ($housekeepingTask->started_at) {
                    $validated['actual_minutes'] = $housekeepingTask->started_at->diffInMinutes(now());
                }

                // Update room status based on task type
                $housekeepingTask->load('room');
                if ($housekeepingTask->room) {
                    // If it's a check_cleaning or inspection task, mark room as clean and available
                    if (in_array($housekeepingTask->task_type, ['check_cleaning', 'inspection'])) {
                        $housekeepingTask->room->update([
                            'housekeeping_status' => 'clean',
                            'status' => 'available',
                            'last_cleaned_at' => now(),
                            'last_cleaned_by' => Auth::id(),
                        ]);
                    } else {
                        // For other tasks (cleaning, checkout, etc.), set to waiting_for_check
                        $housekeepingTask->room->update([
                            'housekeeping_status' => 'waiting_for_check',
                        ]);
                    }
                }
            }
        }

        $housekeepingTask->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully!');
    }

    /**
     * Validate a cleaning task as clean
     */
    public function validateCleaning(Request $request, HousekeepingTask $housekeepingTask)
    {
        $validated = $request->validate([
            'validation_status' => 'required|in:validated,rejected',
            'validation_notes' => 'nullable|string',
            'inspection_photos' => 'nullable|array',
            'inspection_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Only allow validation of completed cleaning tasks
        if ($housekeepingTask->status !== 'completed' || $housekeepingTask->task_type !== 'cleaning') {
            return redirect()->back()->with('error', 'Only completed cleaning tasks can be validated.');
        }

        // Update validation status
        $housekeepingTask->update([
            'validation_status' => $validated['validation_status'],
            'validation_notes' => $validated['validation_notes'],
            'validation_timestamp' => now(),
            'inspected_by' => Auth::id(),
            'inspected_at' => now(),
        ]);

        // Handle inspection photos
        if ($request->hasFile('inspection_photos')) {
            $photos = [];
            foreach ($request->file('inspection_photos') as $photo) {
                $path = $photo->store('housekeeping/inspections', 'public');
                $photos[] = $path;
            }
            $housekeepingTask->update(['inspection_photos' => $photos]);
        }

        // Update room status based on validation
        $room = $housekeepingTask->room;
        if ($room) {
            if ($validated['validation_status'] === 'validated') {
                $room->update([
                    'housekeeping_status' => 'clean',
                    'last_cleaned_at' => now(),
                    'last_cleaned_by' => $housekeepingTask->assigned_to,
                ]);

                // If room was in cleaning status, make it available
                if ($room->status === 'cleaning') {
                    $room->update(['status' => 'available']);
                }
            } else {
                // Validation rejected - mark room as needing attention
                $room->update([
                    'housekeeping_status' => 'dirty',
                ]);
            }
        }

        $statusMessage = $validated['validation_status'] === 'validated' ? 'validated' : 'rejected';
        return redirect()->back()->with('success', "Cleaning task has been {$statusMessage}.");
    }

    /**
     * Mark room as clean (manual override by cleaner)
     */
    public function markRoomClean(Request $request, Room $room)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update room status
        $room->update([
            'housekeeping_status' => 'clean',
            'last_cleaned_at' => now(),
            'last_cleaned_by' => Auth::id(),
        ]);

        // If room was in cleaning status, make it available
        if ($room->status === 'cleaning') {
            $room->update(['status' => 'available']);
        }

        // Complete any pending cleaning tasks for this room today
        $today = Carbon::today();
        HousekeepingTask::where('room_id', $room->id)
            ->where('task_type', 'cleaning')
            ->where('status', 'pending')
            ->whereDate('scheduled_date', $today)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'validation_status' => 'validated',
                'validation_timestamp' => now(),
                'inspected_by' => Auth::id(),
                'inspected_at' => now(),
                'completion_notes' => $validated['notes'] ?? 'Room marked as clean by cleaner',
            ]);

        return redirect()->back()->with('success', "Room {$room->room_number} has been marked as clean.");
    }

    public function show(HousekeepingTask $housekeepingTask)
    {
        $housekeepingTask->load(['room', 'assignedTo', 'inspectedBy']);

        // Format scheduled_date for display
        if ($housekeepingTask->scheduled_date) {
            $housekeepingTask->scheduled_date = \Carbon\Carbon::parse($housekeepingTask->scheduled_date)->format('M d, Y');
        }

        // Format scheduled_time for display
        if ($housekeepingTask->scheduled_time) {
            $housekeepingTask->scheduled_time = \Carbon\Carbon::parse($housekeepingTask->scheduled_time)->format('g:i A');
        }

        return Inertia::render('Admin/HousekeepingTasks/Show', [
            'task' => $housekeepingTask,
        ]);
    }

    public function destroy(HousekeepingTask $housekeepingTask)
    {
        $housekeepingTask->delete();

        return redirect()->route('admin.housekeeping-tasks.index')
            ->with('success', 'Task deleted successfully!');
    }

    public function generateDailyTasks()
    {
        $exitCode = Artisan::call('housekeeping:generate-daily-tasks');
        $output   = Artisan::output();

        $created  = 0;
        $assigned = 0;
        if (preg_match('/Tasks Created:\s*(\d+)/', $output, $m)) $created  = (int) $m[1];
        if (preg_match('/Tasks Auto-Assigned:\s*(\d+)/', $output, $m)) $assigned = (int) $m[1];

        if ($exitCode === 0) {
            return redirect()->route('admin.housekeeping-tasks.index')
                ->with('success', "Daily tasks generated: {$created} created, {$assigned} assigned.");
        }

        return redirect()->route('admin.housekeeping-tasks.index')
            ->with('error', 'Task generation failed. Check server logs.');
    }
}
