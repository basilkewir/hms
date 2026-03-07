<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyMembership extends Model
{
    use HasFactory;

    protected $table = 'loyalty_memberships';

    protected $fillable = [
        'name',
        'min_points',
        'discount_percentage',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_points' => 'integer',
        'discount_percentage' => 'float',
    ];
}
