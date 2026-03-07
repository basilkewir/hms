<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'warehouse_id',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'destination_location_id');
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class, 'location_id');
    }
}
