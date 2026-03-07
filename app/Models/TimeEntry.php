<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_shift_id',
        'work_date',
        'clock_in_time',
        'clock_out_time',
        'break_start_time',
        'break_end_time',
        'regular_hours',
        'overtime_hours',
        'break_hours',
        'total_hours',
        'status',
        'is_late',
        'is_early_out',
        'late_minutes',
        'early_out_minutes',
        'clock_in_ip',
        'clock_out_ip',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'approved_by',
        'approved_at',
        'notes',
        'admin_notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'approved_at' => 'datetime',
        'is_late' => 'boolean',
        'is_early_out' => 'boolean',
        'clock_in_latitude' => 'decimal:8',
        'clock_in_longitude' => 'decimal:8',
        'clock_out_latitude' => 'decimal:8',
        'clock_out_longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
