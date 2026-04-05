<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceHeartbeat extends Model
{
    protected $table = 'device_heartbeats';

    protected $fillable = [
        'iptv_device_id',
        'status',
        'current_channel',
        'app_version',
        'ip_address',
        'settings_version',
        'extra',
        'recorded_at',
    ];

    protected $casts = [
        'extra'       => 'array',
        'recorded_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IptvDevice::class, 'iptv_device_id');
    }
}
