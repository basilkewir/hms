<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_to',
        'room_numbers',
        'start_date',
        'end_date',
        'preferred_start_time',
        'preferred_end_time',
        'status',
        'notes',
        'instructions',
        'created_by',
    ];

    protected $casts = [
        'room_numbers' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'preferred_start_time' => 'string',
        'preferred_end_time' => 'string',
    ];

    /**
     * Get the user assigned to this schedule.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this schedule.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the rooms assigned to this schedule.
     */
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'housekeeping_schedule_rooms', 'housekeeping_schedule_id', 'room_id')
            ->withPivot(['task_type', 'priority', 'status', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get the schedule rooms (pivot records).
     */
    public function scheduleRooms()
    {
        return $this->hasMany(HousekeepingScheduleRoom::class, 'housekeeping_schedule_id');
    }

    /**
     * Get the room count for this schedule.
     */
    public function getRoomCountAttribute()
    {
        return is_array($this->room_numbers) ? count($this->room_numbers) : 0;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
                     ->where('end_date', '>=', $startDate);
    }

    /**
     * Scope to filter by assigned user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
