<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    protected $fillable = [
        'name',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'hire_date',
        'termination_date',
        'employment_status',
        'department',
        'position',
        'department_id',
        'position_id',
        'hourly_rate',
        'salary',
        'pay_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'national_id',
        'passport_number',
        'passport_expiry',
        'work_permit',
        'work_permit_expiry',
        'notes',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'passport_expiry' => 'date',
        'work_permit_expiry' => 'date',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'salary' => 'decimal:2',
    ];

    protected $appends = [
        'profile_photo_url',
        'full_name',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withTimestamps()
                    ->withPivot(['assigned_at', 'assigned_by']);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                    ->withTimestamps()
                    ->withPivot(['granted', 'granted_by', 'granted_at']);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function createdReservations()
    {
        return $this->hasMany(Reservation::class, 'created_by');
    }

    public function createdGuests()
    {
        return $this->hasMany(Guest::class, 'created_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'user_id');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->full_name . ($this->employee_id ? ' (' . $this->employee_id . ')' : '');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('employment_status', 'active');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    // Helper Methods
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isManager()
    {
        return $this->hasRole('manager');
    }

    public function isAccountant()
    {
        return $this->hasRole('accountant');
    }

    public function isStaff()
    {
        return $this->hasRole('staff');
    }

    public function canManageUsers()
    {
        return $this->hasPermissionTo('manage_users');
    }

    public function canManageReservations()
    {
        return $this->hasPermissionTo('manage_reservations');
    }

    public function canViewFinancials()
    {
        return $this->hasPermissionTo('view_financials');
    }

    public function canManageIPTV()
    {
        return $this->hasPermissionTo('manage_iptv');
    }

    /**
     * Override the can method to allow admin to bypass all permission checks
     */
    public function can($ability, $arguments = [])
    {
        // Admin always has access
        if ($this->hasRole('admin')) {
            return true;
        }

        return parent::can($ability, $arguments);
    }

    /**
     * Check if user has permission (with admin bypass)
     * This works with the custom role/permission system
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Admin always has access
        if ($this->hasRole('admin')) {
            return true;
        }

        // Normalize permission name (handle both string and object)
        $permissionName = is_string($permission) ? $permission : (is_object($permission) ? $permission->name : $permission);

        // Check if user has permission directly
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // Check if any of user's roles have the permission
        $roles = $this->roles()->get();
        foreach ($roles as $role) {
            // First check database permissions
            if ($role->permissions()->where('name', $permissionName)->exists()) {
                return true;
            }
            
            // Fallback: Check role's default permissions from getDefaultRoles()
            $defaultRoles = Role::getDefaultRoles();
            if (isset($defaultRoles[$role->name]['permissions'])) {
                if (in_array($permissionName, $defaultRoles[$role->name]['permissions'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
