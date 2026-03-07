<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\{
    HasFactory
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo
};

class ReservationRoom extends Model
{
    use HasFactory;

    protected $table = 'reservation_room';

    protected $fillable = [
        'reservation_id',
        'room_id',
        'is_primary',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'room_rate',
        'total_room_charges',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'room_rate' => 'decimal:2',
            'total_room_charges' => 'decimal:2',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}

