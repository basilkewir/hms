<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quote_id',
        'description',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'total',
    ];

    /**
     * Relationship with Quote
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Get total for this item (quantity * unit_price)
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}
