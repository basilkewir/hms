<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'room_id',
        'reported_by',
        'assigned_to',
        'department_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'location',
        'location_details',
        'photos',
        'documents',
        'reported_at',
        'assigned_at',
        'started_at',
        'resolved_at',
        'closed_at',
        'scheduled_date',
        'scheduled_time',
        'resolution_notes',
        'work_performed',
        'cost',
        'resolved_by',
        'requires_follow_up',
        'follow_up_notes',
    ];

    protected $casts = [
        'photos' => 'array',
        'documents' => 'array',
        'reported_at' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'scheduled_date' => 'date',
        'scheduled_time' => 'string', // Store as time string (HH:MM:SS format)
        'cost' => 'decimal:2',
        'requires_follow_up' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
