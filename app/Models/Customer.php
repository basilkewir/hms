<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'customer_group_id',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'full_name',
    ];

    // Relationships
    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByGroup($query, $groupId)
    {
        return $query->where('customer_group_id', $groupId);
    }

    // Methods
    public function generateCustomerCode()
    {
        $prefix = 'CUST';
        $year = date('Y');
        $lastCustomer = self::where('customer_code', 'like', $prefix . $year . '%')
                           ->orderBy('customer_code', 'desc')
                           ->first();
        
        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->customer_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getDiscountPercentage()
    {
        if ($this->customerGroup && $this->customerGroup->is_active) {
            return $this->customerGroup->discount_percentage;
        }
        return 0;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($customer) {
            if (!$customer->customer_code) {
                $customer->customer_code = $customer->generateCustomerCode();
            }
            if (!$customer->created_by) {
                $customer->created_by = auth()->id();
            }
        });

        static::updating(function ($customer) {
            if (!$customer->updated_by) {
                $customer->updated_by = auth()->id();
            }
        });
    }
}
