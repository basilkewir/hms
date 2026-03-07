<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'room_type_id',
        'requested_check_in',
        'requested_check_out',
        'requested_nights',
        'number_of_adults',
        'number_of_children',
        'priority',
        'status',
        'contact_email',
        'contact_phone',
        'special_requests',
        'notified_at',
        'converted_at',
        'converted_to_reservation_id',
        'expires_at',
    ];

    protected $casts = [
        'requested_check_in' => 'date',
        'requested_check_out' => 'date',
        'notified_at' => 'datetime',
        'converted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function convertedToReservation()
    {
        return $this->belongsTo(Reservation::class, 'converted_to_reservation_id');
    }
}
