<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'included_features',
        'optional_features',
        'is_active',
        'max_bookings',
        'min_guests',
        'max_guests',
        'duration_hours',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'included_features' => 'array',
        'optional_features' => 'array',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function halls()
    {
        return $this->belongsToMany(Hall::class, 'package_hall');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'package_id');
    }

    public function groupBookings()
    {
        return $this->belongsToMany(GroupBooking::class, 'group_booking_package');
    }

    public function calculateTotalPrice($optionalFeatures = [])
    {
        $total = $this->price;

        foreach ($optionalFeatures as $feature) {
            if (isset($this->optional_features[$feature])) {
                $total += $this->optional_features[$feature];
            }
        }

        return $total;
    }
}
