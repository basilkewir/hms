<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
        'location_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public static function recordMovement($productId, $type, $quantity, $referenceType = null, $referenceId = null, $notes = null, $locationId = null)
    {
        $product = Product::find($productId);
        $previousStock = $product->stock_quantity;
        
        if ($type === 'in') {
            $newStock = $previousStock + $quantity;
        } else {
            $newStock = $previousStock - $quantity;
        }

        $product->update(['stock_quantity' => $newStock]);

        return static::create([
            'product_id'     => $productId,
            'type'           => $type,
            'quantity'       => $quantity,
            'previous_stock' => $previousStock,
            'new_stock'      => $newStock,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'notes'          => $notes,
            'user_id'        => auth()->id(),
            'location_id'    => $locationId,
        ]);
    }
}