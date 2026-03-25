<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'requested_by',
        'approved_by',
        'status',
        'request_type',
        'reason',
        'rejection_reason',
        'items',
        'processed_at',
    ];

    protected $casts = [
        'items' => 'array',
        'processed_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
