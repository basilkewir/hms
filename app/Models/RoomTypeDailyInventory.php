<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeDailyInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'inventory_date',
        'total_inventory',
        'reserved_count',
        'hold_count',
        'overbooking_allowance',
        'available_count',
    ];

    protected $casts = [
        'inventory_date' => 'date',
        'total_inventory' => 'integer',
        'reserved_count' => 'integer',
        'hold_count' => 'integer',
        'overbooking_allowance' => 'integer',
        'available_count' => 'integer',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
