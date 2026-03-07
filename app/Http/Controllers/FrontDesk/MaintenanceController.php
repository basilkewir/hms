<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $requests = MaintenanceRequest::with(['room', 'reportedBy', 'assignedTo', 'department', 'resolvedBy'])
            ->orderBy('reported_at', 'desc')
            ->paginate(20)
            ->through(function ($request) {
                return [
                    'id' => $request->id,
                    'request_number' => $request->request_number,
                    'room' => $request->room ? [
                        'id' => $request->room->id,
                        'room_number' => $request->room->room_number,
                    ] : null,
                    'title' => $request->title,
                    'category' => $request->category,
                    'priority' => $request->priority,
                    'status' => $request->status,
                    'location' => $request->location,
                    'reported_by' => $request->reportedBy ? [
                        'id' => $request->reportedBy->id,
                        'name' => $request->reportedBy->full_name,
                    ] : null,
                    'assigned_to' => $request->assignedTo ? [
                        'id' => $request->assignedTo->id,
                        'name' => $request->assignedTo->full_name,
                    ] : null,
                    'department' => $request->department ? [
                        'id' => $request->department->id,
                        'name' => $request->department->name,
                    ] : null,
                    'reported_at' => $request->reported_at?->format('Y-m-d H:i:s'),
                    'resolved_at' => $request->resolved_at?->format('Y-m-d H:i:s'),
                    'cost' => $request->cost,
                ];
            });

        $stats = [
            'total' => MaintenanceRequest::count(),
            'open' => MaintenanceRequest::where('status', 'open')->count(),
            'in_progress' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'resolved' => MaintenanceRequest::where('status', 'resolved')->count(),
            'urgent' => MaintenanceRequest::where('priority', 'urgent')
                ->whereIn('status', ['open', 'assigned', 'in_progress'])
                ->count(),
        ];

        return Inertia::render('FrontDesk/Services/Maintenance', [
            'user' => $request->user()->load('roles'),
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'category' => 'required|string|in:electrical,plumbing,hvac,carpentry,painting,general',
            'priority' => 'required|string|in:low,normal,high,urgent',
            'description' => 'required|string',
        ]);

        MaintenanceRequest::create([
            'request_number' => 'MR' . strtoupper(Str::random(8)),
            'title' => $validated['title'],
            'location' => $validated['location'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'status' => 'open',
            'reported_at' => now(),
            'reported_by' => $request->user()->id,
        ]);

        return redirect()->route('front-desk.services.maintenance')
            ->with('success', 'Maintenance request created successfully.');
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:open,assigned,in_progress,on_hold,resolved,closed,cancelled',
        ]);

        $nextStatus = $validated['status'] ?? match ($maintenanceRequest->status) {
            'open' => 'assigned',
            'assigned' => 'in_progress',
            'in_progress' => 'resolved',
            default => 'resolved',
        };

        $maintenanceRequest->update([
            'status' => $nextStatus,
            'resolved_at' => $nextStatus === 'resolved' ? now() : null,
        ]);

        return redirect()->route('front-desk.services.maintenance')
            ->with('success', 'Maintenance request status updated successfully.');
    }
}
