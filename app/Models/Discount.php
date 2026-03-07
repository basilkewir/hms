<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'code',
        'type',
        'value',
        'applicable_to',
        'auto_apply',
        'is_global',
        'starts_at',
        'ends_at',
        'max_uses',
        'per_guest_limit',
        'used_count',
        'requires_code',
        'is_stackable',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'auto_apply' => 'boolean',
            'is_global' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'is_stackable' => 'boolean',
            'requires_code' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->starts_at && $today < $this->starts_at->toDateString()) {
            return false;
        }

        if ($this->ends_at && $today > $this->ends_at->toDateString()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}

