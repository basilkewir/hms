<?php

namespace App\Http\Controllers;

use App\Models\GuestBillAdjustmentRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationBillAdjustmentNotification;
use App\Services\ReservationBillAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ReservationBillAdjustmentController extends Controller
{
    public function __construct(private readonly ReservationBillAdjustmentService $billAdjustmentService)
    {
    }

    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:increase,decrease',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'request_notes' => 'nullable|string|max:1000',
        ]);

        $adjustmentRequest = GuestBillAdjustmentRequest::create([
            'reservation_id' => $reservation->id,
            'requested_by' => auth()->id(),
            'adjustment_type' => $validated['adjustment_type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'request_notes' => $validated['request_notes'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $reviewers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager']);
        })->get()->unique('id')->values();

        if ($reviewers->isNotEmpty()) {
            Notification::send(
                $reviewers,
                new ReservationBillAdjustmentNotification(
                    $reservation,
                    $adjustmentRequest,
                    'requested',
                    trim((auth()->user()?->first_name ?? '') . ' ' . (auth()->user()?->last_name ?? '')) ?: auth()->user()?->email
                )
            );
        }

        return back()->with('success', 'Bill modification request submitted for validation.');
    }

    public function approve(Request $request, Reservation $reservation, GuestBillAdjustmentRequest $billAdjustmentRequest): RedirectResponse
    {
        $this->ensureRequestMatchesReservation($reservation, $billAdjustmentRequest);

        if ($billAdjustmentRequest->status !== 'pending') {
            return back()->with('error', 'This bill modification request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($billAdjustmentRequest, $validated) {
            $this->billAdjustmentService->approve(
                $billAdjustmentRequest,
                auth()->user(),
                $validated['review_notes'] ?? null
            );
        });

        $requester = $billAdjustmentRequest->requester()->first();

        if ($requester) {
            $requester->notify(new ReservationBillAdjustmentNotification(
                $reservation,
                $billAdjustmentRequest->fresh(),
                'approved',
                trim((auth()->user()?->first_name ?? '') . ' ' . (auth()->user()?->last_name ?? '')) ?: auth()->user()?->email
            ));
        }

        return back()->with('success', 'Bill modification request approved and applied successfully.');
    }

    public function reject(Request $request, Reservation $reservation, GuestBillAdjustmentRequest $billAdjustmentRequest): RedirectResponse
    {
        $this->ensureRequestMatchesReservation($reservation, $billAdjustmentRequest);

        if ($billAdjustmentRequest->status !== 'pending') {
            return back()->with('error', 'This bill modification request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $this->billAdjustmentService->reject(
            $billAdjustmentRequest,
            auth()->user(),
            $validated['review_notes'] ?? null
        );

        $requester = $billAdjustmentRequest->requester()->first();

        if ($requester) {
            $requester->notify(new ReservationBillAdjustmentNotification(
                $reservation,
                $billAdjustmentRequest->fresh(),
                'rejected',
                trim((auth()->user()?->first_name ?? '') . ' ' . (auth()->user()?->last_name ?? '')) ?: auth()->user()?->email
            ));
        }

        return back()->with('success', 'Bill modification request rejected.');
    }

    private function ensureRequestMatchesReservation(Reservation $reservation, GuestBillAdjustmentRequest $billAdjustmentRequest): void
    {
        abort_unless((int) $billAdjustmentRequest->reservation_id === (int) $reservation->id, 404);
    }
}