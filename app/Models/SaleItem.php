<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'total_price',
        'discount_amount'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate profit for this sale item
     */
    public function getProfitAttribute()
    {
        $cost = $this->unit_cost * $this->quantity;
        $revenue = $this->total_price - ($this->discount_amount ?? 0);
        return $revenue - $cost;
    }

    /**
     * Calculate profit margin percentage
     */
    public function getProfitMarginAttribute()
    {
        $revenue = $this->total_price - ($this->discount_amount ?? 0);
        if ($revenue <= 0) return 0;
        
        $cost = $this->unit_cost * $this->quantity;
        if ($cost <= 0) return 100;
        
        return (($revenue - $cost) / $revenue) * 100;
    }
}