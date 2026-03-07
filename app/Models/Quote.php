<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quote_number',
        'quote_type',
        'reservation_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'valid_until',
        'status',
        'notes',
        'created_by',
        'issue_date',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'issue_date' => 'date',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'created_at_formatted',
        'valid_until_formatted',
    ];

    /**
     * Relationship with Reservation
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Relationship with Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with Quote Items
     */
    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * Get the user who created the quote
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Scope: Search by quote number, customer name, or email
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('quote_number', 'LIKE', "%{$search}%")
                ->orWhere('customer_name', 'LIKE', "%{$search}%")
                ->orWhere('customer_email', 'LIKE', "%{$search}%");
        }
        return $query;
    }

    /**
     * Generate quote number
     */
    public static function generateQuoteNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastQuote = self::where('quote_number', 'LIKE', "QT-{$year}-{$month}-%")
            ->orderBy('quote_number', 'desc')
            ->first();

        if ($lastQuote) {
            $lastNumber = intval(substr($lastQuote->quote_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "QT-{$year}-{$month}-{$newNumber}";
    }

    /**
     * Format created_at for display
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y') : '';
    }

    /**
     * Format valid_until for display
     */
    public function getValidUntilFormattedAttribute()
    {
        return $this->valid_until ? $this->valid_until->format('M d, Y') : '';
    }

    /**
     * Check if quote is expired
     */
    public function isExpired()
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    /**
     * Update status based on valid_until date
     */
    public function updateStatusIfExpired()
    {
        if ($this->isExpired() && $this->status !== 'accepted') {
            $this->status = 'expired';
            $this->save();
        }
        return $this;
    }
}
