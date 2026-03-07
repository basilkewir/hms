<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolioCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_folio_id',
        'charge_code',
        'description',
        'charge_date',
        'charge_time',
        'quantity',
        'unit_price',
        'total_amount',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'net_amount',
        'reference_type',
        'reference_id',
        'department',
        'posted_by',
        'posted_at',
        'is_voided',
        'voided_by',
        'voided_at',
        'void_reason'
    ];

    protected $casts = [
        'charge_date' => 'date',
        'charge_time' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    public function folio(): BelongsTo
    {
        return $this->belongsTo(GuestFolio::class, 'guest_folio_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('charge_date', [$startDate, $endDate]);
    }

    public function scopeByChargeCode($query, $code)
    {
        return $query->where('charge_code', $code);
    }

    public function scopeNotVoided($query)
    {
        return $query->where('is_voided', false);
    }

    public function getFormattedNetAmountAttribute()
    {
        return \App\Helpers\CurrencyHelper::format($this->net_amount);
    }
}
