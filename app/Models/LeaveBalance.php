<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'vacation_days_allocated',
        'vacation_days_used',
        'vacation_days_remaining',
        'sick_days_allocated',
        'sick_days_used',
        'sick_days_remaining',
        'personal_days_allocated',
        'personal_days_used',
        'personal_days_remaining',
    ];

    protected $casts = [
        'vacation_days_allocated' => 'decimal:1',
        'vacation_days_used' => 'decimal:1',
        'vacation_days_remaining' => 'decimal:1',
        'sick_days_allocated' => 'decimal:1',
        'sick_days_used' => 'decimal:1',
        'sick_days_remaining' => 'decimal:1',
        'personal_days_allocated' => 'decimal:1',
        'personal_days_used' => 'decimal:1',
        'personal_days_remaining' => 'decimal:1',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
