<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestBillAdjustmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'requested_by',
        'reviewed_by',
        'guest_folio_id',
        'folio_charge_id',
        'adjustment_type',
        'amount',
        'reason',
        'request_notes',
        'review_notes',
        'status',
        'requested_at',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(GuestFolio::class, 'guest_folio_id');
    }

    public function folioCharge(): BelongsTo
    {
        return $this->belongsTo(FolioCharge::class, 'folio_charge_id');
    }
}