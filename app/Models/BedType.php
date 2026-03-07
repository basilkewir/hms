<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BedType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'width_inches',
        'length_inches',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'width_inches' => 'decimal:2',
        'length_inches' => 'decimal:2',
        'sort_order' => 'integer'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function roomTypes()
    {
        // Note: This relationship requires bed_type_id column in room_types table
        // For now, room_types uses bed_type as string, so this won't work until migration
        return $this->hasMany(RoomType::class, 'bed_type_id');
    }
}
