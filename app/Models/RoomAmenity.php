<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class RoomAmenity extends Model
{
    protected $fillable = ['name', 'icon', 'description', 'is_active', 'room_id', 'amenity_name', 'amenity_type', 'condition', 'last_checked'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_type_amenity');
    }

    protected static function booted()
    {
        static::creating(function ($amenity) {
            // Set default values for legacy columns if they exist
            if (Schema::hasColumn('room_amenities', 'room_id') && !isset($amenity->room_id)) {
                $amenity->room_id = null;
            }
            if (Schema::hasColumn('room_amenities', 'amenity_name') && !isset($amenity->amenity_name)) {
                $amenity->amenity_name = $amenity->name;
            }
            if (Schema::hasColumn('room_amenities', 'amenity_type') && !isset($amenity->amenity_type)) {
                $amenity->amenity_type = 'general';
            }
            if (Schema::hasColumn('room_amenities', 'condition') && !isset($amenity->condition)) {
                $amenity->condition = 'good';
            }
        });
    }
}
