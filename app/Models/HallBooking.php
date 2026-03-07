<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'hall_id',
        'guest_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'event_date',
        'start_time',
        'end_time',
        'attendees',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'attendees' => 'integer',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
