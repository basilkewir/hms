<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'guest_folio_id',
        'reservation_id',
        'payment_method',
        'amount',
        'currency',
        'exchange_rate',
        'local_amount',
        'card_type',
        'card_last_four',
        'authorization_code',
        'transaction_id',
        'processor_response',
        'check_number',
        'bank_name',
        'status',
        'processed_at',
        'processed_by',
        'refunded_amount',
        'refunded_at',
        'refunded_by',
        'refund_reason',
        'notes',
        'revenue_center'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'local_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function folio(): BelongsTo
    {
        return $this->belongsTo(GuestFolio::class, 'guest_folio_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('processed_at', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function getFormattedAmountAttribute()
    {
        return \App\Helpers\CurrencyHelper::format($this->local_amount);
    }

    public function getNetAmountAttribute()
    {
        return $this->local_amount - $this->refunded_amount;
    }
}
