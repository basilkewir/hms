<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'guest_id', 'room_id', 'user_id',
        'status', 'priority', 'pickup_date', 'delivery_date',
        'pickup_time', 'delivery_time', 'subtotal', 'express_fee',
        'total_amount', 'payment_status', 'special_instructions', 'notes',
    ];

    protected $casts = [
        'pickup_date'   => 'date',
        'delivery_date' => 'date',
        'subtotal'      => 'decimal:2',
        'express_fee'   => 'decimal:2',
        'total_amount'  => 'decimal:2',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(LaundryItem::class);
    }

    public function recalculateTotals()
    {
        $subtotal = $this->items()->sum('total_price');
        $expressFee = $this->priority === 'express' ? $subtotal * 0.5 : ($this->priority === 'overnight' ? $subtotal * 0.25 : 0);
        $this->update([
            'subtotal'     => $subtotal,
            'express_fee'  => $expressFee,
            'total_amount' => $subtotal + $expressFee,
        ]);
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'LDR-' . date('Ymd') . '-';
        $last = static::where('order_number', 'like', $prefix . '%')->orderByDesc('id')->first();
        $seq = $last ? ((int) substr($last->order_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
