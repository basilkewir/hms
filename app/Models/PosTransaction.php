<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_drawer_session_id',
        'sale_id',
        'type',
        'amount',
        'payment_method',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function cashDrawerSession()
    {
        return $this->belongsTo(CashDrawerSession::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}