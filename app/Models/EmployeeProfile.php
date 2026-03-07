<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hotel_id',
        'employee_code',
        'hire_date',
        'termination_date',
        'job_title',
        'department_id',
        'position_id',
        'employment_type',
        'base_salary',
        'hourly_rate',
        'eligible_for_overtime',
        'pay_frequency',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'contact_person',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'termination_date' => 'date',
            'base_salary' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'eligible_for_overtime' => 'boolean',
            'contact_person' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}

