<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakfastMenu extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'price',
        'items',
        'serving_time_start',
        'serving_time_end',
        'is_active',
        'available_online',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'items' => 'array',
        'is_active' => 'boolean',
        'available_online' => 'boolean',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_services')
            ->withPivot('quantity', 'unit_price', 'total_price', 'service_date', 'status', 'notes')
            ->withTimestamps();
    }
}
