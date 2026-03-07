<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'item_code',
        'category',
        'description',
        'unit_of_measure',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'reorder_point',
        'unit_cost',
        'total_value',
        'supplier_id',
        'location',
        'status',
        'last_count_date',
        'last_count_by',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
        'reorder_point' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
        'last_count_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements()
    {
        return $this->hasMany(HousekeepingInventoryMovement::class);
    }

    public function getIsLowStockAttribute()
    {
        return $this->current_stock <= $this->reorder_point;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->current_stock <= 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        } elseif ($this->is_low_stock) {
            return 'low_stock';
        } elseif ($this->current_stock >= $this->maximum_stock) {
            return 'over_stocked';
        } else {
            return 'normal';
        }
    }

    public function updateStock($quantity, $type, $description = '', $user_id = null)
    {
        // Create movement record
        $this->movements()->create([
            'movement_type' => $type,
            'quantity' => $quantity,
            'description' => $description,
            'user_id' => $user_id,
            'previous_stock' => $this->current_stock,
            'new_stock' => $this->current_stock + $quantity,
        ]);

        // Update current stock
        $this->current_stock += $quantity;
        $this->total_value = $this->current_stock * $this->unit_cost;
        $this->save();

        return $this;
    }

    public function scopeLowStock($query)
    {
        return $query->where('current_stock', '<=', 'reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
