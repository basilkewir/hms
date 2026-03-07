<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingInventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'housekeeping_inventory_id',
        'movement_type',
        'quantity',
        'description',
        'user_id',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    public function inventory()
    {
        return $this->belongsTo(HousekeepingInventory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMovementTypeLabelAttribute()
    {
        $types = [
            'receipt' => 'Stock Receipt',
            'issue' => 'Stock Issue',
            'adjustment' => 'Stock Adjustment',
            'transfer' => 'Stock Transfer',
            'waste' => 'Stock Waste',
            'theft' => 'Stock Theft',
            'damage' => 'Stock Damage',
            'return' => 'Stock Return',
        ];

        return $types[$this->movement_type] ?? ucfirst($this->movement_type);
    }

    public function getQuantityFormattedAttribute()
    {
        return $this->quantity > 0 ? "+{$this->quantity}" : $this->quantity;
    }

    public function getMovementColorAttribute()
    {
        $colors = [
            'receipt' => 'text-green-600',
            'issue' => 'text-red-600',
            'adjustment' => 'text-blue-600',
            'transfer' => 'text-yellow-600',
            'waste' => 'text-gray-600',
            'theft' => 'text-red-600',
            'damage' => 'text-orange-600',
            'return' => 'text-purple-600',
        ];

        return $colors[$this->movement_type] ?? 'text-gray-600';
    }

    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeByInventory($query, $inventoryId)
    {
        return $query->where('housekeeping_inventory_id', $inventoryId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
