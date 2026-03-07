<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderList($request, 'all');
    }

    public function open(Request $request)
    {
        return $this->renderList($request, 'open');
    }

    public function inProgress(Request $request)
    {
        return $this->renderList($request, 'in_progress');
    }

    public function completed(Request $request)
    {
        return $this->renderList($request, 'completed');
    }

    private function renderList(Request $request, string $status)
    {
        $statusMap = [
            'open' => ['open', 'assigned'],
            'in_progress' => ['in_progress'],
            'completed' => ['resolved', 'closed'],
        ];

        $query = MaintenanceRequest::with(['room', 'reportedBy', 'assignedTo'])
            ->orderByDesc('reported_at');

        if ($status !== 'all') {
            $query->whereIn('status', $statusMap[$status] ?? []);
        }

        $requests = $query->paginate(20)->through(function ($request) {
            return [
                'id' => $request->id,
                'request_number' => $request->request_number,
                'title' => $request->title,
                'room' => $request->room?->room_number,
                'location' => $request->location,
                'priority' => $request->priority,
                'status' => $request->status,
                'reported_at' => $request->reported_at?->format('Y-m-d H:i:s'),
                'assigned_to' => $request->assignedTo?->full_name,
            ];
        });

        $statusLabels = [
            'all' => 'All Orders',
            'open' => 'Open Orders',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
        ];

        return Inertia::render('Maintenance/WorkOrders/Index', [
            'user' => $request->user()->load('roles'),
            'requests' => $requests,
            'statusFilter' => $status,
            'statusLabel' => $statusLabels[$status] ?? 'All Orders',
        ]);
    }
}
