<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category_id',
        'description',
        'price',
        'cost_price',
        'stock_quantity',
        'min_stock_level',
        'unit',
        'barcode',
        'emoji',
        'image',
        'is_active',
        'is_service',
        'tax_rate',
        'brand_id',
        'unit_id',
        'margin_percentage'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'margin_percentage' => 'decimal:4',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_service' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
                    ->withPivot('quantity', 'min_stock_level')
                    ->withTimestamps();
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function getTotalStockFromBatchesAttribute()
    {
        return $this->stockBatches()->sum('quantity');
    }

    public function isLowStock()
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function getTotalValueAttribute()
    {
        return $this->stock_quantity * $this->cost_price;
    }
}