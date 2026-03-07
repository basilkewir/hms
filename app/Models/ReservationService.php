<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationService extends Model
{
    protected $fillable = [
        'reservation_id',
        'hotel_service_id',
        'breakfast_menu_id',
        'quantity',
        'unit_price',
        'total_price',
        'service_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'service_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function hotelService()
    {
        return $this->belongsTo(HotelService::class);
    }

    public function breakfastMenu()
    {
        return $this->belongsTo(BreakfastMenu::class);
    }
}
