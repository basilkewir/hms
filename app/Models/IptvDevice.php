<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IptvDevice extends Model
{
    use HasFactory;

    protected $table = 'iptv_devices';

    protected $fillable = [
        'room_id',
        'device_id',
        'device_name',
        'device_type',
        'mac_address',
        'ip_address',
        'status',
        'last_activity',
        'user_agent',
        'app_version',
        'is_active',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
