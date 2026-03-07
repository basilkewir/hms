<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingTask;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $tasks = HousekeepingTask::with(['room', 'assignedTo'])
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20)
            ->through(function ($task) {
                return [
                    'id' => $task->id,
                    'room' => $task->room ? [
                        'id' => $task->room->id,
                        'room_number' => $task->room->room_number,
                    ] : null,
                    'task_type' => $task->task_type,
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'assigned_to' => $task->assignedTo ? [
                        'id' => $task->assignedTo->id,
                        'name' => $task->assignedTo->full_name,
                    ] : null,
                    'scheduled_date' => $task->scheduled_date?->format('Y-m-d'),
                    'scheduled_time' => $task->scheduled_time ? (is_string($task->scheduled_time) ? substr($task->scheduled_time, 0, 5) : $task->scheduled_time->format('H:i')) : null,
                    'notes' => $task->notes,
                ];
            });

        $stats = [
            'pending' => HousekeepingTask::where('status', 'pending')->count(),
            'in_progress' => HousekeepingTask::where('status', 'in_progress')->count(),
            'completed' => HousekeepingTask::where('status', 'completed')->count(),
            'urgent' => HousekeepingTask::where('priority', 'urgent')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];

        return Inertia::render('FrontDesk/Services/Housekeeping', [
            'user' => $request->user()->load('roles'),
            'tasks' => $tasks,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|exists:rooms,room_number',
            'task_type' => 'required|in:checkout,cleaning,check_cleaning,stayover,deep_clean,inspection,maintenance',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        $room = Room::where('room_number', $validated['room_number'])->firstOrFail();

        HousekeepingTask::create([
            'room_id' => $room->id,
            'task_type' => $validated['task_type'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'scheduled_date' => now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('front-desk.services.housekeeping')
            ->with('success', 'Housekeeping request submitted successfully.');
    }

    public function updateStatus(Request $request, HousekeepingTask $housekeepingTask)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);

        $nextStatus = $validated['status'] ?? match ($housekeepingTask->status) {
            'pending' => 'in_progress',
            'in_progress' => 'completed',
            default => 'completed',
        };

        $housekeepingTask->update([
            'status' => $nextStatus,
        ]);

        return redirect()->back();
    }
}
