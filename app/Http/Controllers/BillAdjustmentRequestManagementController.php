<?php

namespace App\Http\Controllers;

use App\Models\GuestBillAdjustmentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillAdjustmentRequestManagementController extends Controller
{
    public function adminIndex(Request $request): Response
    {
        return $this->renderIndex($request, 'admin');
    }

    public function managerIndex(Request $request): Response
    {
        return $this->renderIndex($request, 'manager');
    }

    private function renderIndex(Request $request, string $routePrefix): Response
    {
        $user = $request->user()?->load('roles');
        $role = $routePrefix === 'admin' ? 'admin' : 'manager';
        $search = trim((string) $request->string('search'));

        $query = GuestBillAdjustmentRequest::query()
            ->with(['reservation.guest', 'requester', 'reviewer', 'folioCharge'])
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($nested) use ($search) {
                    $nested->where('reason', 'like', "%{$search}%")
                        ->orWhere('request_notes', 'like', "%{$search}%")
                        ->orWhere('review_notes', 'like', "%{$search}%")
                        ->orWhereHas('reservation', function ($reservationQuery) use ($search) {
                            $reservationQuery->where('reservation_number', 'like', "%{$search}%")
                                ->orWhereHas('guest', function ($guestQuery) use ($search) {
                                    $guestQuery->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                                });
                        });
                });
            })
            ->when($request->filled('status'), fn($builder) => $builder->where('status', $request->string('status')))
            ->when($request->filled('adjustment_type'), fn($builder) => $builder->where('adjustment_type', $request->string('adjustment_type')))
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($builder) use ($request) {
                $builder->whereBetween('requested_at', [
                    $request->string('start_date')->toString() . ' 00:00:00',
                    $request->string('end_date')->toString() . ' 23:59:59',
                ]);
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('requested_at')
            ->orderByDesc('id');

        $requests = $query
            ->paginate(20)
            ->withQueryString()
            ->through(function (GuestBillAdjustmentRequest $adjustmentRequest) {
                $reservation = $adjustmentRequest->reservation;
                $guest = $reservation?->guest;
                $requester = $adjustmentRequest->requester;
                $reviewer = $adjustmentRequest->reviewer;
                $folioCharge = $adjustmentRequest->folioCharge;

                return [
                    'id' => $adjustmentRequest->id,
                    'reservation_id' => $adjustmentRequest->reservation_id,
                    'reservation_number' => $reservation?->reservation_number,
                    'guest_name' => $guest
                        ? trim(($guest->first_name ?? '') . ' ' . ($guest->last_name ?? ''))
                        : 'Guest',
                    'adjustment_type' => $adjustmentRequest->adjustment_type,
                    'amount' => (float) $adjustmentRequest->amount,
                    'signed_amount' => $adjustmentRequest->adjustment_type === 'decrease'
                        ? -1 * (float) $adjustmentRequest->amount
                        : (float) $adjustmentRequest->amount,
                    'folio_charge_amount' => $folioCharge ? (float) $folioCharge->net_amount : null,
                    'reason' => $adjustmentRequest->reason,
                    'request_notes' => $adjustmentRequest->request_notes,
                    'review_notes' => $adjustmentRequest->review_notes,
                    'status' => $adjustmentRequest->status,
                    'requested_at' => optional($adjustmentRequest->requested_at ?? $adjustmentRequest->created_at)?->format('Y-m-d H:i:s'),
                    'reviewed_at' => optional($adjustmentRequest->reviewed_at)?->format('Y-m-d H:i:s'),
                    'requested_by_name' => $requester
                        ? trim(($requester->first_name ?? '') . ' ' . ($requester->last_name ?? ''))
                        : 'N/A',
                    'reviewed_by_name' => $reviewer
                        ? trim(($reviewer->first_name ?? '') . ' ' . ($reviewer->last_name ?? ''))
                        : null,
                ];
            });

        $approvedImpact = (float) GuestBillAdjustmentRequest::query()
            ->join('folio_charges', 'guest_bill_adjustment_requests.folio_charge_id', '=', 'folio_charges.id')
            ->where('guest_bill_adjustment_requests.status', 'approved')
            ->where('folio_charges.charge_code', 'ADJUSTMENT')
            ->where(function ($query) {
                $query->where('folio_charges.is_voided', false)
                    ->orWhereNull('folio_charges.is_voided');
            })
            ->sum('folio_charges.net_amount');

        return Inertia::render('Admin/Reservations/BillAdjustmentRequests/Index', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => $routePrefix,
            'requests' => $requests,
            'stats' => [
                'total' => GuestBillAdjustmentRequest::count(),
                'pending' => GuestBillAdjustmentRequest::where('status', 'pending')->count(),
                'approved' => GuestBillAdjustmentRequest::where('status', 'approved')->count(),
                'rejected' => GuestBillAdjustmentRequest::where('status', 'rejected')->count(),
                'approved_net_impact' => $approvedImpact,
            ],
            'filters' => $request->only(['search', 'status', 'adjustment_type', 'start_date', 'end_date']),
        ]);
    }
}