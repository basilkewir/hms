<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationHold extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_token',
        'room_type_id',
        'check_in_date',
        'check_out_date',
        'quantity',
        'status',
        'expires_at',
        'consumed_at',
        'created_from_ip',
        'metadata',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
