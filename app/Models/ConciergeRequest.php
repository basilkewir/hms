<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConciergeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'guest_name',
        'room_number',
        'service_type',
        'status',
        'requested_at',
        'details',
        'created_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
