<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description',
        'user_id'
    ];

    protected $casts = [
        'file_size' => 'integer'
    ];

    public function expense()
    {
        return $this->belongsTo(PosExpense::class, 'expense_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
