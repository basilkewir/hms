<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelService extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'is_free',
        'pricing_type',
        'is_active',
        'available_online',
        'requires_advance_booking',
        'advance_hours',
        'availability_schedule',
        'max_quantity',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'available_online' => 'boolean',
        'requires_advance_booking' => 'boolean',
        'availability_schedule' => 'array',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_services')
            ->withPivot('quantity', 'unit_price', 'total_price', 'service_date', 'status', 'notes')
            ->withTimestamps();
    }
}
