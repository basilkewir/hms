<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'hotel_id',
        'reservation_number',
        'guest_id',
        'room_id',
        'room_type_id',
        'check_in_date',
        'check_out_date',
        'nights',
        'number_of_adults',
        'number_of_children',
        'infants',
        'status',
        'room_rate',
        'total_room_charges',
        'taxes',
        'service_charges',
        'discount_amount',
        'discount_reason',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'actual_check_in',
        'actual_check_out',
        'checked_in_by',
        'checked_out_by',
        'booking_source',
        'booking_reference',
        'special_requests',
        'guest_preferences',
        'room_preferences',
        'early_check_in_requested',
        'late_check_out_requested',
        'preferred_check_in_time',
        'preferred_check_out_time',
        'iptv_preferences',
        'iptv_adult_content',
        'iptv_language_preference',
        'airport_pickup',
        'airport_drop',
        'breakfast_included',
        'wifi_included',
        'parking_required',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'cancellation_charges',
        'notes',
        'created_by',
        'updated_by',
        'group_booking_id',
        'is_group_booking',
        'billing_type',
        'payment_status',
        'source',
        'ota_confirmation_number',
        'adults',
        'children',
        'police_report_status',
        'police_reported_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'actual_check_in' => 'datetime',
        'actual_check_out' => 'datetime',
        'cancelled_at' => 'datetime',
        'special_requests' => 'array',
        'guest_preferences' => 'array',
        'room_preferences' => 'array',
        'iptv_preferences' => 'array',
        'police_reported_at' => 'datetime',
        'early_check_in_requested' => 'boolean',
        'late_check_out_requested' => 'boolean',
        'iptv_adult_content' => 'boolean',
        'airport_pickup' => 'boolean',
        'airport_drop' => 'boolean',
        'breakfast_included' => 'boolean',
        'wifi_included' => 'boolean',
        'parking_required' => 'boolean',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function keyCards()
    {
        return $this->hasMany(KeyCard::class);
    }

    public function activeKeyCard()
    {
        return $this->hasOne(KeyCard::class)->where('status', 'assigned');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function hotelServices()
    {
        return $this->belongsToMany(HotelService::class, 'reservation_services')
            ->withPivot('quantity', 'unit_price', 'total_price', 'service_date', 'status', 'notes')
            ->withTimestamps();
    }

    public function breakfastMenus()
    {
        return $this->belongsToMany(BreakfastMenu::class, 'reservation_services')
            ->withPivot('quantity', 'unit_price', 'total_price', 'service_date', 'status', 'notes')
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function groupBooking()
    {
        return $this->belongsTo(GroupBooking::class);
    }

    public function guestFolios()
    {
        return $this->hasMany(GuestFolio::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'reservation_room')
            ->withPivot([
                'is_primary',
                'check_in_date',
                'check_out_date',
                'adults',
                'children',
                'room_rate',
                'total_room_charges',
            ]);
    }

}
