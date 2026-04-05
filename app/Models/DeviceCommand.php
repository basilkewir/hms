<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
{
    protected $table = 'device_commands';

    protected $fillable = [
        'iptv_device_id',
        'type',
        'payload',
        'status',
        'dispatched_at',
        'executed_at',
        'dispatched_by',
    ];

    protected $casts = [
        'payload'       => 'array',
        'dispatched_at' => 'datetime',
        'executed_at'   => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IptvDevice::class, 'iptv_device_id');
    }

    public function markDelivered(): void
    {
        $this->update(['status' => 'delivered']);
    }

    public function markExecuted(): void
    {
        $this->update(['status' => 'executed', 'executed_at' => now()]);
    }

    public function markFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}
