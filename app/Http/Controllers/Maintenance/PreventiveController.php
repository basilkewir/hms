<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PreventiveController extends Controller
{
    public function scheduled(Request $request)
    {
        $tasks = $this->scheduledTasksQuery()
            ->get()
            ->map(fn($task) => $this->mapTask($task));

        $rooms = Room::query()
            ->orderBy('room_number')
            ->get(['id', 'room_number']);

        return Inertia::render('Maintenance/Preventive/Scheduled', [
            'user' => $request->user()->load('roles'),
            'tasks' => $tasks,
            'rooms' => $rooms,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'room_id' => 'nullable|exists:rooms,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
        ]);

        MaintenanceRequest::create([
            'request_number' => 'PM' . strtoupper(Str::random(8)),
            'room_id' => $validated['room_id'] ?? null,
            'reported_by' => $request->user()?->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'other',
            'priority' => 'normal',
            'status' => 'open',
            'reported_at' => now(),
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Preventive maintenance scheduled successfully!');
    }

    public function overdue(Request $request)
    {
        $today = Carbon::today();
        $tasks = $this->scheduledTasksQuery()
            ->whereDate('scheduled_date', '<', $today)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->get()
            ->map(fn($task) => $this->mapTask($task));

        return Inertia::render('Maintenance/Preventive/Overdue', [
            'user' => $request->user()->load('roles'),
            'tasks' => $tasks,
        ]);
    }

    public function calendar(Request $request)
    {
        $tasks = $this->scheduledTasksQuery()
            ->orderBy('scheduled_date')
            ->get()
            ->map(fn($task) => $this->mapTask($task));

        return Inertia::render('Maintenance/Preventive/Calendar', [
            'user' => $request->user()->load('roles'),
            'tasks' => $tasks,
        ]);
    }

    public function equipment(Request $request)
    {
        $equipment = MaintenanceRequest::whereNotNull('scheduled_date')
            ->selectRaw('category, count(*) as total, max(scheduled_date) as last_due')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'equipment' => $row->category ? ucfirst($row->category) : 'General',
                    'total' => $row->total,
                    'last_due' => $row->last_due ? Carbon::parse($row->last_due)->format('Y-m-d') : null,
                ];
            });

        return Inertia::render('Maintenance/Preventive/Equipment', [
            'user' => $request->user()->load('roles'),
            'equipment' => $equipment,
        ]);
    }

    private function scheduledTasksQuery()
    {
        return MaintenanceRequest::whereNotNull('scheduled_date')
            ->orderBy('scheduled_date');
    }

    private function mapTask(MaintenanceRequest $task): array
    {
        $today = Carbon::today();
        $status = 'pending';
        if (in_array($task->status, ['resolved', 'closed'])) {
            $status = 'completed';
        } elseif ($task->scheduled_date && Carbon::parse($task->scheduled_date)->lt($today)) {
            $status = 'overdue';
        }

        return [
            'id' => $task->id,
            'description' => $task->title ?? $task->category ?? 'Scheduled Maintenance',
            'equipment' => $task->category ? ucfirst($task->category) : 'General',
            'due_date' => $task->scheduled_date?->format('Y-m-d'),
            'status' => $status,
        ];
    }
}
