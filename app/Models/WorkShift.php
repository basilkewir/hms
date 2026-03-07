<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'hours',
        'break_minutes',
        'is_overnight',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }
}
