<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'type',
        'quantity_change',
        'new_balance',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'new_balance' => 'integer',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
