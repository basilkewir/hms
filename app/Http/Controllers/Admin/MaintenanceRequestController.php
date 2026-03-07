<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestController extends Controller
{
    public function index()
    {
        $requests = MaintenanceRequest::with(['room', 'reportedBy', 'assignedTo', 'department', 'resolvedBy'])
            ->orderBy('reported_at', 'desc')
            ->paginate(20)
            ->through(function($request) {
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
                    'reported_at' => $request->reported_at->format('Y-m-d H:i:s'),
                    'resolved_at' => $request->resolved_at?->format('Y-m-d H:i:s'),
                    'cost' => $request->cost,
                ];
            });

        $stats = [
            'total' => MaintenanceRequest::count(),
            'open' => MaintenanceRequest::where('status', 'open')->count(),
            'in_progress' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'resolved' => MaintenanceRequest::where('status', 'resolved')->count(),
            'urgent' => MaintenanceRequest::where('priority', 'urgent')->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
        ];

        // Get all staff for assignment (maintenance, housekeeping, manager, admin, staff, and any other operational roles)
        $maintenanceStaff = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['maintenance', 'housekeeping', 'staff', 'admin', 'manager', 'front_desk']);
        })->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
            ->map(function($user) {
                $roles = $user->roles->pluck('name')->join(', ');
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'roles' => $roles,
                ];
            });

        // Determine which view to render based on route
        $user = auth()->user();
        $isManager = $user && $user->hasRole('manager');
        $viewPath = $isManager ? 'Manager/Rooms/Maintenance' : 'Admin/MaintenanceRequests/Index';

        return Inertia::render($viewPath, [
            'requests' => $requests,
            'stats' => $stats,
            'maintenanceStaff' => $maintenanceStaff,
        ]);
    }

    public function create()
    {
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number']);
        $departments = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $staff = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['maintenance', 'housekeeping', 'staff', 'admin', 'manager', 'front_desk']);
        })->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
            ->map(function($user) {
                $roles = $user->roles->pluck('name')->join(', ');
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'roles' => $roles,
                ];
            });

        $routeName = request()->route()->getName() ?? '';
        $routePrefix = str_starts_with($routeName, 'manager.') ? 'manager' : 'admin';
        return Inertia::render('Admin/MaintenanceRequests/Create', [
            'rooms'        => $rooms,
            'departments'  => $departments,
            'staff'        => $staff,
            'routePrefix'  => $routePrefix,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:plumbing,electrical,hvac,furniture,appliances,security,it,other',
            'priority' => 'required|in:low,normal,high,urgent',
            'location' => 'nullable|string|max:255',
            'location_details' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:5120',
        ]);

        $validated['request_number'] = 'MR' . strtoupper(Str::random(8));
        $validated['reported_by'] = auth()->id() ?? null;
        $validated['status'] = 'open';
        $validated['reported_at'] = now();

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('maintenance/photos', 'public');
                $photoPaths[] = $path;
            }
            $validated['photos'] = $photoPaths;
        }

        MaintenanceRequest::create($validated);

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->back()
                ->with('success', 'Maintenance request created successfully!');
        }

        return redirect()->route('admin.maintenance-requests.index')
            ->with('success', 'Maintenance request created successfully!');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['room', 'reportedBy', 'assignedTo', 'department', 'resolvedBy']);

        $user = auth()->user();
        $isManager = $user && $user->hasRole('manager');

        $requestData = [
            'id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number,
            'room' => $maintenanceRequest->room,
            'reported_by' => $maintenanceRequest->reportedBy ? [
                'id' => $maintenanceRequest->reportedBy->id,
                'name' => $maintenanceRequest->reportedBy->full_name,
            ] : null,
            'assigned_to' => $maintenanceRequest->assignedTo ? [
                'id' => $maintenanceRequest->assignedTo->id,
                'name' => $maintenanceRequest->assignedTo->full_name,
            ] : null,
            'department' => $maintenanceRequest->department,
            'title' => $maintenanceRequest->title,
            'description' => $maintenanceRequest->description,
            'category' => $maintenanceRequest->category,
            'priority' => $maintenanceRequest->priority,
            'status' => $maintenanceRequest->status,
            'location' => $maintenanceRequest->location,
            'location_details' => $maintenanceRequest->location_details,
            'photos' => $maintenanceRequest->photos ? array_map(function($photo) {
                return Storage::url($photo);
            }, $maintenanceRequest->photos) : [],
            'reported_at' => $maintenanceRequest->reported_at->format('Y-m-d H:i:s'),
            'assigned_at' => $maintenanceRequest->assigned_at?->format('Y-m-d H:i:s'),
            'started_at' => $maintenanceRequest->started_at?->format('Y-m-d H:i:s'),
            'resolved_at' => $maintenanceRequest->resolved_at?->format('Y-m-d H:i:s'),
            'scheduled_date' => $maintenanceRequest->scheduled_date?->format('Y-m-d'),
            'scheduled_time' => $maintenanceRequest->scheduled_time ? (is_string($maintenanceRequest->scheduled_time) ? substr($maintenanceRequest->scheduled_time, 0, 5) : $maintenanceRequest->scheduled_time->format('H:i')) : null,
            'resolution_notes' => $maintenanceRequest->resolution_notes,
            'work_performed' => $maintenanceRequest->work_performed,
            'cost' => $maintenanceRequest->cost,
            'resolved_by' => $maintenanceRequest->resolvedBy ? [
                'id' => $maintenanceRequest->resolvedBy->id,
                'name' => $maintenanceRequest->resolvedBy->full_name,
            ] : null,
            'requires_follow_up' => $maintenanceRequest->requires_follow_up,
            'follow_up_notes' => $maintenanceRequest->follow_up_notes,
        ];

        // Determine which view to render based on user role
        if ($isManager) {
            return Inertia::render('Manager/MaintenanceRequests/Show', [
                'user' => $user->load('roles'),
                'request' => $requestData,
            ]);
        }

        return Inertia::render('Admin/MaintenanceRequests/Show', [
            'user' => $user->load('roles'),
            'request' => $requestData,
        ]);
    }

    public function assign(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
        ]);

        $validated['status'] = 'assigned';
        $validated['assigned_at'] = now();

        $maintenanceRequest->update($validated);

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->back()->with('success', 'Request assigned successfully!');
        }

        return redirect()->back()->with('success', 'Request assigned successfully!');
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,assigned,in_progress,on_hold,resolved,closed,cancelled',
            'resolution_notes' => 'nullable|string',
            'work_performed' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        if ($validated['status'] === 'in_progress' && !$maintenanceRequest->started_at) {
            $validated['started_at'] = now();
        }

        if ($validated['status'] === 'resolved' && !$maintenanceRequest->resolved_at) {
            $validated['resolved_at'] = now();
            $validated['resolved_by'] = auth()->id() ?? null;
        }

        if ($validated['status'] === 'closed' && !$maintenanceRequest->closed_at) {
            $validated['closed_at'] = now();
        }

        $maintenanceRequest->update($validated);

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->back()->with('success', 'Request status updated successfully!');
        }

        return redirect()->back()->with('success', 'Request status updated successfully!');
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        // Delete photos
        if ($maintenanceRequest->photos) {
            foreach ($maintenanceRequest->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $maintenanceRequest->delete();

        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.maintenance-requests.index')
                ->with('success', 'Maintenance request deleted successfully!');
        }

        return redirect()->route('admin.maintenance-requests.index')
            ->with('success', 'Maintenance request deleted successfully!');
    }
}
