<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IptvPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'monthly_price',
        'includes_adult_content',
        'includes_premium_channels',
        'includes_international_channels',
        'xtream_categories',
        'xtream_channel_groups',
        'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'includes_adult_content' => 'boolean',
        'includes_premium_channels' => 'boolean',
        'includes_international_channels' => 'boolean',
        'xtream_categories' => 'array',
        'xtream_channel_groups' => 'array',
        'is_active' => 'boolean',
    ];
}
