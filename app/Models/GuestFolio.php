<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuestFolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio_number',
        'reservation_id',
        'guest_id',
        'room_id',
        'status',
        'folio_date',
        'room_charges',
        'service_charges',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'closed_at',
        'closed_by',
        'notes',
        'customer_name',
        'customer_email',
        'customer_phone',
    ];

    protected $casts = [
        'folio_date' => 'date',
        'room_charges' => 'decimal:2',
        'service_charges' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(FolioCharge::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('folio_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getFormattedTotalAmountAttribute()
    {
        return \App\Helpers\CurrencyHelper::format($this->total_amount);
    }

    public function getRevenueAmountAttribute()
    {
        return $this->room_charges + $this->service_charges;
    }
}
