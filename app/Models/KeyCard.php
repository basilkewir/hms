<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_number',
        'card_type',
        'reservation_id',
        'room_id',
        'guest_id',
        'status',
        'issued_at',
        'returned_at',
        'expires_at',
        'issued_by',
        'returned_to',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function returnedTo()
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function assignToReservation($reservationId, $roomId, $guestId, $issuedBy, $expiresAt = null)
    {
        $this->update([
            'reservation_id' => $reservationId,
            'room_id' => $roomId,
            'guest_id' => $guestId,
            'status' => 'assigned',
            'issued_at' => now(),
            'issued_by' => $issuedBy,
            'expires_at' => $expiresAt,
        ]);
    }

    public function returnCard($returnedTo)
    {
        $this->update([
            'status' => 'available',
            'returned_at' => now(),
            'returned_to' => $returnedTo,
            'reservation_id' => null,
            'room_id' => null,
            'guest_id' => null,
            'expires_at' => null,
        ]);
    }

    public function markAsLost()
    {
        $this->update([
            'status' => 'lost',
            'returned_at' => now(),
        ]);
    }

    public function markAsDamaged()
    {
        $this->update([
            'status' => 'damaged',
            'returned_at' => now(),
        ]);
    }

    public function deactivate()
    {
        $this->update([
            'status' => 'deactivated',
            'is_active' => false,
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }
}
