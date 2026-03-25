<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'user_id',
        'customer_id',
        'room_id',
        'reservation_id',
        'guest_id',
        'is_walk_in',
        'is_charged_to_room',
        'customer_name',
        'customer_phone',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'tip_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        'sale_date'
    ];

    protected $casts = [
        'is_walk_in' => 'boolean',
        'is_charged_to_room' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sale_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function generateSaleNumber()
    {
        $prefix = 'POS';
        $date = now()->format('Ymd');
        $lastSale = static::whereDate('created_at', today())->latest()->first();
        $sequence = $lastSale ? (int)substr($lastSale->sale_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total profit for this sale
     */
    public function getTotalProfitAttribute()
    {
        return $this->items->sum(function ($item) {
            $cost = ($item->unit_cost ?? 0) * $item->quantity;
            $revenue = $item->total_price - ($item->discount_amount ?? 0);
            return $revenue - $cost;
        });
    }

    /**
     * Calculate total cost for this sale
     */
    public function getTotalCostAttribute()
    {
        return $this->items->sum(function ($item) {
            return ($item->unit_cost ?? 0) * $item->quantity;
        });
    }

    /**
     * Calculate profit margin percentage for this sale
     */
    public function getProfitMarginAttribute()
    {
        $revenue = $this->total_amount;
        if ($revenue <= 0) return 0;
        
        $cost = $this->total_cost;
        if ($cost <= 0) return 100;
        
        return (($revenue - $cost) / $revenue) * 100;
    }
}