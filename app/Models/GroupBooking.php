<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_number',
        'group_name',
        'primary_guest_id',
        'contact_person_id',
        'check_in_date',
        'check_out_date',
        'total_rooms',
        'total_guests',
        'total_adults',
        'total_children',
        'group_discount_percentage',
        'group_discount_amount',
        'total_group_amount',
        'paid_amount',
        'balance_amount',
        'billing_type',
        'billing_instructions',
        'status',
        'special_requests',
        'group_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'group_discount_percentage' => 'decimal:2',
        'group_discount_amount' => 'decimal:2',
        'total_group_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function halls()
    {
        return $this->belongsToMany(Hall::class, 'group_booking_hall');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'group_booking_package');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'group_booking_id');
    }

    public function getBalanceAttribute()
    {
        return $this->total_group_amount - $this->paid_amount;
    }

    public function getIsPaidAttribute()
    {
        return $this->paid_amount >= $this->total_group_amount;
    }
}
