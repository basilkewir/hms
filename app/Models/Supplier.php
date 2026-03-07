<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'credit_limit',
        'current_balance',
        'is_active'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount') ?? 0;
    }

    public function getTotalPendingAttribute()
    {
        $totalPurchases = $this->purchaseOrders()->sum('total_amount') ?? 0;
        $totalPaid = $this->payments()->sum('amount') ?? 0;
        return max(0, $totalPurchases - $totalPaid);
    }

    public function getCurrentBalanceAttribute($value)
    {
        // Always calculate from actual purchase orders and payments
        return $this->getTotalPendingAttribute();
    }
}