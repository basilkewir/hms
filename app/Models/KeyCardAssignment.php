<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyCardAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_card_id',
        'guest_id',
        'room_id',
        'reservation_id',
        'assigned_by',
        'assigned_at',
        'returned_to',
        'returned_at',
        'action',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    /**
     * Get the key card that was assigned.
     */
    public function keyCard(): BelongsTo
    {
        return $this->belongsTo(KeyCard::class);
    }

    /**
     * Get the guest who received the key card.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the room associated with the key card assignment.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the reservation associated with the key card assignment.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the user who assigned the key card.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who received the returned key card.
     */
    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    /**
     * Scope to get assignments by action type.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get current assignments (not returned).
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('returned_at');
    }

    /**
     * Scope to get returned assignments.
     */
    public function scopeReturned($query)
    {
        return $query->whereNotNull('returned_at');
    }

    /**
     * Check if this is a current assignment.
     */
    public function isCurrent(): bool
    {
        return is_null($this->returned_at);
    }

    /**
     * Check if this assignment has been returned.
     */
    public function isReturned(): bool
    {
        return !is_null($this->returned_at);
    }

    /**
     * Get the duration of the assignment.
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->assigned_at) {
            return null;
        }

        $end = $this->returned_at ?: now();
        $duration = $this->assigned_at->diff($end);

        if ($duration->days > 0) {
            return $duration->days . ' day' . ($duration->days > 1 ? 's' : '');
        } elseif ($duration->h > 0) {
            return $duration->h . ' hour' . ($duration->h > 1 ? 's' : '');
        } else {
            return $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
        }
    }
}
