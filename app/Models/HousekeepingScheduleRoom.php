<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingScheduleRoom extends Model
{
    use HasFactory;

    protected $table = 'housekeeping_schedule_rooms';

    protected $fillable = [
        'housekeeping_schedule_id',
        'room_id',
        'task_type',
        'priority',
        'status',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the schedule this room belongs to.
     */
    public function schedule()
    {
        return $this->belongsTo(HousekeepingSchedule::class, 'housekeeping_schedule_id');
    }

    /**
     * Get the room.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by task type.
     */
    public function scopeByTaskType($query, $taskType)
    {
        return $query->where('task_type', $taskType);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}
