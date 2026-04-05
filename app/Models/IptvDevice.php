<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'package',
        'last_activity',
        'last_seen',
        'last_heartbeat',
        'user_agent',
        'app_version',
        'android_version',
        'device_info',
        'pushed_settings',
        'settings_version',
        'notes',
        'registration_token',
        'registered_at',
        'is_active',
    ];

    protected $casts = [
        'last_activity'    => 'datetime',
        'last_seen'        => 'datetime',
        'last_heartbeat'   => 'datetime',
        'registered_at'    => 'datetime',
        'device_info'      => 'array',
        'pushed_settings'  => 'array',
        'is_active'        => 'boolean',
        'settings_version' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class, 'iptv_device_id');
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(DeviceHeartbeat::class, 'iptv_device_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isOnline(): bool
    {
        return $this->last_heartbeat && $this->last_heartbeat->gt(now()->subMinutes(3));
    }

    public function computedStatus(): string
    {
        if (!$this->last_heartbeat) return 'offline';
        $mins = $this->last_heartbeat->diffInMinutes(now());
        if ($mins <= 2)  return 'online';
        if ($mins <= 10) return 'idle';
        return 'offline';
    }

    public function generateRegistrationToken(): string
    {
        $token = Str::random(32);
        $this->update(['registration_token' => $token]);
        return $token;
    }

    public function pendingCommands()
    {
        return $this->commands()->where('status', 'pending')->orderBy('created_at')->get();
    }

    public function dispatchCommand(string $type, array $payload = [], ?string $by = null): DeviceCommand
    {
        return $this->commands()->create([
            'type'          => $type,
            'payload'       => $payload,
            'status'        => 'pending',
            'dispatched_at' => now(),
            'dispatched_by' => $by,
        ]);
    }
}
