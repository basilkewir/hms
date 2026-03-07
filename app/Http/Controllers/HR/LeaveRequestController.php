<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'leave_type' => ['required', 'string', 'max:100'],
            'reason' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $daysRequested = $start->diffInDays($end) + 1; // inclusive

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_requested' => $daysRequested,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'approval_notes' => ['nullable', 'string'],
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $leaveRequest->status = $validated['status'];

        if ($validated['status'] === 'approved') {
            $leaveRequest->approved_by = $request->user()->id;
            $leaveRequest->approved_at = now();
            $leaveRequest->approval_notes = $validated['approval_notes'] ?? null;
            $leaveRequest->rejection_reason = null;
        } elseif ($validated['status'] === 'rejected') {
            $leaveRequest->approved_by = null;
            $leaveRequest->approved_at = null;
            $leaveRequest->approval_notes = null;
            $leaveRequest->rejection_reason = $validated['rejection_reason'] ?? null;
        } else { // pending
            $leaveRequest->approved_by = null;
            $leaveRequest->approved_at = null;
            $leaveRequest->approval_notes = null;
            $leaveRequest->rejection_reason = null;
        }

        $leaveRequest->save();

        return back()->with('success', 'Leave request updated.');
    }
}
