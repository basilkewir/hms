<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'user_id',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'order_date',
        'expected_date',
        'received_date',
        'delivery_time_days',
        'purchase_conditions',
        'notes',
        'location_id',
        'purchase_type',
        'expense_category'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'delivery_time_days' => 'integer'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function purchaseDocuments()
    {
        return $this->hasMany(PurchaseDocument::class);
    }

    public function deliveryDocuments()
    {
        return $this->hasMany(DeliveryDocument::class);
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->remaining_amount = max(0, $this->total_amount - $totalPaid);
        $this->save();
    }

    public function isFullyPaid()
    {
        $totalPaid = $this->payments()->sum('amount');
        return $totalPaid >= $this->total_amount;
    }

    public function getPaidAmountAttribute($value)
    {
        // Always calculate from payments to ensure accuracy
        return $this->payments()->sum('amount') ?? 0;
    }

    public function getRemainingAmountAttribute($value)
    {
        // Always calculate from total and paid amounts
        $totalPaid = $this->payments()->sum('amount') ?? 0;
        return max(0, $this->total_amount - $totalPaid);
    }

    public function generatePoNumber()
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');
        $lastPo = static::whereDate('created_at', today())->latest()->first();
        $sequence = $lastPo ? (int)substr($lastPo->po_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}