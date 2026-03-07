<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomIptvSetting extends Model
{
    use HasFactory;

    protected $table = 'room_iptv_settings';

    protected $fillable = [
        'room_id',
        'iptv_package_id',
        'xtream_custom_categories',
        'xtream_blocked_categories',
        'xtream_blocked_channels',
        'adult_content_enabled',
        'parental_control_pin',
        'volume_limit',
        'quiet_hours_start',
        'quiet_hours_end',
        'language_preferences',
        'auto_power_off',
        'auto_power_off_time',
    ];

    protected $casts = [
        'xtream_custom_categories' => 'array',
        'xtream_blocked_categories' => 'array',
        'xtream_blocked_channels' => 'array',
        'adult_content_enabled' => 'boolean',
        'volume_limit' => 'integer',
        'quiet_hours_start' => 'datetime:H:i:s',
        'quiet_hours_end' => 'datetime:H:i:s',
        'language_preferences' => 'array',
        'auto_power_off' => 'boolean',
        'auto_power_off_time' => 'datetime:H:i:s',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(IptvPackage::class, 'iptv_package_id');
    }
}
