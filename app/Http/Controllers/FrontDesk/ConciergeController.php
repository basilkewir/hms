<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\ConciergeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ConciergeController extends Controller
{
    public function index(Request $request)
    {
        $requests = ConciergeRequest::orderByDesc('requested_at')
            ->paginate(20)
            ->through(function ($request) {
                return [
                    'id' => $request->id,
                    'request_number' => $request->request_number,
                    'guest_name' => $request->guest_name,
                    'room_number' => $request->room_number,
                    'service_type' => $request->service_type,
                    'status' => $request->status,
                    'requested_at' => $request->requested_at?->format('Y-m-d H:i:s'),
                    'details' => $request->details,
                ];
            });

        $stats = [
            'pending' => ConciergeRequest::where('status', 'pending')->count(),
            'in_progress' => ConciergeRequest::where('status', 'in_progress')->count(),
            'completed' => ConciergeRequest::where('status', 'completed')->count(),
            'total' => ConciergeRequest::whereDate('requested_at', today())->count(),
        ];

        return Inertia::render('FrontDesk/Services/Concierge', [
            'user' => $request->user()->load('roles'),
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'service_type' => 'required|string|max:255',
            'details' => 'nullable|string',
        ]);

        ConciergeRequest::create([
            'request_number' => 'CR' . strtoupper(Str::random(8)),
            'guest_name' => $validated['guest_name'],
            'room_number' => $validated['room_number'],
            'service_type' => $validated['service_type'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('front-desk.services.concierge')
            ->with('success', 'Concierge request created successfully.');
    }

    public function updateStatus(Request $request, ConciergeRequest $conciergeRequest)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);

        $nextStatus = $validated['status'] ?? match ($conciergeRequest->status) {
            'pending' => 'in_progress',
            'in_progress' => 'completed',
            default => 'completed',
        };

        $conciergeRequest->update([
            'status' => $nextStatus,
        ]);

        return redirect()->back();
    }
}
