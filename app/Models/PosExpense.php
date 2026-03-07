<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'category_id',
        'user_id',
        'description',
        'amount',
        'payment_method',
        'expense_date',
        'receipt_number',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date'
    ];

    public function category()
    {
        return $this->belongsTo(PosExpenseCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(ExpenseDocument::class, 'expense_id');
    }

    public function generateExpenseNumber()
    {
        $prefix = 'EXP';
        $date = now()->format('Ymd');
        $lastExpense = static::whereDate('created_at', today())->latest()->first();
        $sequence = $lastExpense ? (int)substr($lastExpense->expense_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}