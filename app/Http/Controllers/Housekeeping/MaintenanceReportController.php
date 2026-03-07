<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MaintenanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with('room')
            ->where('reported_by', $request->user()->id)
            ->orderByDesc('reported_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $reports = $query->limit(25)->get()->map(function ($report) {
            return [
                'id' => $report->id,
                'roomNumber' => $report->room?->room_number ?? $report->location ?? 'N/A',
                'description' => $report->description,
                'category' => $report->category,
                'priority' => $report->priority,
                'status' => $report->status,
                'date' => $report->reported_at?->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('Housekeeping/Maintenance/Report', [
            'user' => $request->user()->load('roles'),
            'myReports' => $reports,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'roomNumber' => 'required|exists:rooms,room_number',
            'category' => 'required|in:plumbing,electrical,hvac,furniture,appliances,iptv,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'affectsGuest' => 'required|in:no,minor,major,room_unusable',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
        ]);

        $room = Room::where('room_number', $validated['roomNumber'])->first();
        $impactNote = 'Guest impact: ' . str_replace('_', ' ', $validated['affectsGuest']);

        MaintenanceRequest::create([
            'request_number' => 'MR' . strtoupper(Str::random(8)),
            'room_id' => $room?->id,
            'reported_by' => $request->user()->id,
            'title' => 'Housekeeping Report - Room ' . $validated['roomNumber'],
            'description' => trim($validated['description'] . "\n\n" . $impactNote),
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'location' => $validated['roomNumber'],
            'location_details' => $validated['location'] ?? null,
            'reported_at' => now(),
        ]);

        return redirect()->route('housekeeping.maintenance.report')
            ->with('success', 'Maintenance report submitted successfully.');
    }
}
