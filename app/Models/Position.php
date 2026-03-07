<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'name', 'description', 'is_active'];

    /**
     * Get the department that this position belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the roles that belong to this position.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}
