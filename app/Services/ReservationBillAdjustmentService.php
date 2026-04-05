<?php

namespace App\Services;

use App\Models\FolioCharge;
use App\Models\GuestBillAdjustmentRequest;
use App\Models\GuestFolio;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Str;

class ReservationBillAdjustmentService
{
    public function ensureAdjustableFolio(Reservation $reservation): GuestFolio
    {
        $folio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->first();

        if ($folio) {
            return $folio;
        }

        $latestFolio = GuestFolio::where('reservation_id', $reservation->id)
            ->where('status', '!=', 'voided')
            ->latest('id')
            ->first();

        if ($latestFolio) {
            $updates = [];

            if ($latestFolio->status !== 'open') {
                $updates['status'] = 'open';
                $updates['closed_at'] = null;
                $updates['closed_by'] = null;
            }

            if (!$latestFolio->room_id && $reservation->room_id) {
                $updates['room_id'] = $reservation->room_id;
            }

            if ($updates !== []) {
                $latestFolio->update($updates);
            }

            return $latestFolio->fresh();
        }

        return GuestFolio::create([
            'folio_number' => $this->generateFolioNumber(),
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $reservation->room_id,
            'status' => 'open',
            'folio_date' => now()->toDateString(),
            'room_charges' => $reservation->total_room_charges ?? 0,
            'service_charges' => $reservation->service_charges ?? 0,
            'tax_amount' => $reservation->taxes ?? 0,
            'discount_amount' => $reservation->discount_amount ?? 0,
            'total_amount' => $reservation->total_amount ?? 0,
            'paid_amount' => $reservation->paid_amount ?? 0,
            'balance_amount' => $reservation->balance_amount ?? 0,
        ]);
    }

    public function approve(GuestBillAdjustmentRequest $adjustmentRequest, User $reviewer, ?string $reviewNotes = null): GuestBillAdjustmentRequest
    {
        $reservation = $adjustmentRequest->reservation()->firstOrFail();
        $folio = $this->ensureAdjustableFolio($reservation);
        $signedAmount = $adjustmentRequest->adjustment_type === 'decrease'
            ? -1 * (float) $adjustmentRequest->amount
            : (float) $adjustmentRequest->amount;

        $charge = FolioCharge::create([
            'guest_folio_id' => $folio->id,
            'charge_code' => 'ADJUSTMENT',
            'charge_type' => 'other',
            'is_revenue' => $signedAmount >= 0,
            'description' => $this->buildChargeDescription($adjustmentRequest),
            'charge_date' => now()->toDateString(),
            'charge_time' => now()->format('H:i:s'),
            'quantity' => 1,
            'unit_price' => $signedAmount,
            'total_amount' => $signedAmount,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'net_amount' => $signedAmount,
            'reference_type' => 'bill_adjustment_request',
            'reference_id' => $adjustmentRequest->id,
            'department' => 'Front Desk',
            'posted_by' => $reviewer->id,
            'posted_at' => now(),
        ]);

        $this->recalculateTotals($folio);

        $adjustmentRequest->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'guest_folio_id' => $folio->id,
            'folio_charge_id' => $charge->id,
            'reviewed_at' => now(),
            'review_notes' => $reviewNotes,
        ]);

        return $adjustmentRequest->fresh(['requester', 'reviewer', 'folioCharge']);
    }

    public function reject(GuestBillAdjustmentRequest $adjustmentRequest, User $reviewer, ?string $reviewNotes = null): GuestBillAdjustmentRequest
    {
        $adjustmentRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $reviewNotes,
        ]);

        return $adjustmentRequest->fresh(['requester', 'reviewer']);
    }

    public function recalculateTotals(GuestFolio $folio): GuestFolio
    {
        $folio->loadMissing('reservation');

        $charges = FolioCharge::where('guest_folio_id', $folio->id)
            ->where('is_voided', false)
            ->get();

        $serviceCharges = (float) $charges->where('charge_code', 'SERVICE')->sum('net_amount');
        $posCharges = (float) $charges->where('charge_code', 'POS')->sum('net_amount');
        $adjustmentCharges = (float) $charges->where('charge_code', 'ADJUSTMENT')->sum('net_amount');
        $taxAmount = (float) $charges->sum('tax_amount');
        $roomCharges = (float) ($folio->room_charges ?? 0);
        $discountAmount = (float) ($folio->discount_amount ?? 0);
        $paidAmount = (float) ($folio->paid_amount ?? 0);
        $totalAmount = $roomCharges + $serviceCharges + $posCharges + $adjustmentCharges + $taxAmount - $discountAmount;
        $balanceAmount = $totalAmount - $paidAmount;

        $folio->update([
            'service_charges' => $serviceCharges + $posCharges,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $balanceAmount,
        ]);

        if ($folio->reservation) {
            $folio->reservation->update([
                'service_charges' => $serviceCharges,
                'taxes' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'updated_by' => auth()->id() ?? $folio->reservation->updated_by,
            ]);
        }

        return $folio->fresh();
    }

    private function generateFolioNumber(): string
    {
        do {
            $folioNumber = 'FOL-' . strtoupper(Str::random(8));
        } while (GuestFolio::where('folio_number', $folioNumber)->exists());

        return $folioNumber;
    }

    private function buildChargeDescription(GuestBillAdjustmentRequest $adjustmentRequest): string
    {
        $prefix = $adjustmentRequest->adjustment_type === 'decrease'
            ? 'Approved bill reduction'
            : 'Approved bill increase';

        return $prefix . ': ' . $adjustmentRequest->reason;
    }
}