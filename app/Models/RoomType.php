<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_occupancy',
        'max_adults',
        'max_children',
        'base_price',
        'extra_adult_charge',
        'extra_child_charge',
        'amenities',
        'iptv_package',
        'room_size_sqft',
        'bed_type',
        'bed_count',
        'has_balcony',
        'has_living_room',
        'view_type',
        'is_active',
    ];

    protected $casts = [
        'amenities' => 'array',
        'base_price' => 'decimal:2',
        'extra_adult_charge' => 'decimal:2',
        'extra_child_charge' => 'decimal:2',
        'has_balcony' => 'boolean',
        'has_living_room' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(RoomAmenity::class, 'room_type_amenity');
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class, 'bed_type_id');
    }
}
