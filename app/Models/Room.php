<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_type_id',
        'floor',
        'floor_id',
        'building',
        'wing',
        'building_wing_id',
        'bed_type_id',
        'status',
        'iptv_active',
        'housekeeping_status',
        'features',
        'special_features',
        'notes',
        'is_active',
        'last_cleaned_at',
        'last_cleaned_by',
    ];

    protected $casts = [
        'iptv_active'    => 'boolean',
        'is_active'      => 'boolean',
        'features'       => 'array',
        'last_cleaned_at' => 'datetime',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function iptvSettings()
    {
        return $this->hasOne(RoomIptvSetting::class);
    }

    public function iptvDevices()
    {
        return $this->hasMany(IptvDevice::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function currentReservation()
    {
        return $this->hasOne(Reservation::class)
            ->where('status', 'checked_in')
            ->whereNull('actual_check_out')
            ->latest('check_in_date');
    }

    public function pendingReservations()
    {
        return $this->hasMany(Reservation::class)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '<=', now());
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function floorRelation()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function buildingWing()
    {
        return $this->belongsTo(BuildingWing::class);
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }

    public function lastCleanedBy()
    {
        return $this->belongsTo(User::class, 'last_cleaned_by');
    }
}
