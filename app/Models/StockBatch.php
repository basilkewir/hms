<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'purchase_order_id',
        'batch_number',
        'quantity',
        'unit_cost',
        'manufacture_date',
        'expiry_date',
        'received_date',
        'notes',
        'user_id',
        'sale_price',
        'location_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function getTotalCostAttribute()
    {
        return $this->quantity * $this->unit_cost;
    }

    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date < now();
    }

    public function isExpiringSoon($days = 30)
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date <= now()->addDays($days) && $this->expiry_date > now();
    }

    public static function generateBatchNumber()
    {
        $prefix = 'BATCH';
        $date = now()->format('Ymd');
        $lastBatch = static::whereDate('created_at', today())->latest()->first();
        $sequence = $lastBatch ? (int)substr($lastBatch->batch_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
