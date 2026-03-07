<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Role as RoleContract;

class Role extends Model implements RoleContract
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
        'position_id',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withTimestamps()
                    ->withPivot(['assigned_at', 'assigned_by']);
    }

    /**
     * Get the position that this role belongs to.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the permissions for this role.
     * Uses the role_has_permissions table (Spatie-compatible).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions')
                    ->withTimestamps();
    }

    // Methods
    public function hasPermission($permission)
    {
        // Load permissions if not already loaded
        if (!$this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        // Check if permissions relationship is loaded and not null
        if (!$this->permissions) {
            return false;
        }

        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }

        return $this->permissions->contains($permission);
    }

    public function givePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission && !$this->hasPermission($permission)) {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }

    public function syncPermissions($permissions)
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                $perm = Permission::where('name', $permission)->first();
                return $perm ? $perm->id : null;
            }
            return is_object($permission) ? $permission->id : $permission;
        })->filter()->toArray();

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Spatie Role Interface Methods
    public static function findByName(string $name, ?string $guardName = null): self
    {
        return static::where('name', $name)->firstOrFail();
    }

    public static function findById(int|string $id, ?string $guardName = null): self
    {
        return static::where('id', $id)->firstOrFail();
    }

    public static function findOrCreate(string $name, ?string $guardName = null): self
    {
        $role = static::where('name', $name)->first();

        if (!$role) {
            return static::create(['name' => $name, 'guard_name' => $guardName ?? 'web']);
        }

        return $role;
    }

    public function getGuardName(): string
    {
        return $this->guard_name ?? 'web';
    }

    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        return $this->hasPermission($permission);
    }

    // Static Methods
    public static function getDefaultRoles()
    {
        return [
            'admin' => [
                'display_name' => 'Administrator',
                'description' => 'Full system access and management',
                'permissions' => [
                    'manage_users', 'manage_roles', 'manage_permissions',
                    'manage_reservations', 'manage_guests', 'manage_rooms',
                    'manage_financials', 'view_reports', 'manage_iptv',
                    'manage_staff', 'manage_payroll', 'manage_settings',
                    'access_pos', 'process_sales', 'manage_cash_drawer',
                    'manage_inventory', 'manage_purchases', 'manage_suppliers',
                    'view_pos_reports', 'manage_expenses',
                    'manage_budgets', 'create_budgets', 'edit_budgets', 'delete_budgets',
                    'view_budgets', 'approve_budgets', 'submit_budgets_for_approval',
                    'reject_budgets', 'archive_budgets', 'monitor_budgets',
                    'create_budget_alerts', 'view_budget_analytics', 'export_budget_data',
                    'manage_budget_categories', 'manage_budget_departments'
                ]
            ],
            'manager' => [
                'display_name' => 'Manager',
                'description' => 'Hotel operations management',
                'permissions' => [
                    'manage_reservations', 'manage_guests', 'manage_rooms',
                    'view_financials', 'view_reports', 'manage_staff',
                    'approve_expenses', 'manage_iptv', 'access_pos',
                    'process_sales', 'manage_cash_drawer', 'manage_inventory',
                    'manage_purchases', 'manage_suppliers', 'view_pos_reports',
                    'view_budgets', 'create_budgets', 'edit_budgets', 'submit_budgets_for_approval',
                    'monitor_budgets', 'view_budget_analytics'
                ]
            ],
            'accountant' => [
                'display_name' => 'Accountant',
                'description' => 'Financial management and reporting',
                'permissions' => [
                    'manage_financials', 'view_reports', 'manage_expenses',
                    'manage_payroll', 'view_reservations', 'process_payments',
                    'view_pos_reports', 'manage_purchases', 'process_refunds',
                    'view_refunds', 'manage_refunds',
            'view_budgets', 'approve_budgets', 'monitor_budgets',
            'view_budget_analytics', 'export_budget_data', 'view_budget_reports'
                ]
            ],
            'front_desk' => [
                'display_name' => 'Front Desk',
                'description' => 'Guest services and reservations',
                'permissions' => [
                    'manage_reservations', 'manage_guests', 'check_in_out',
                    'view_rooms', 'process_payments', 'view_iptv',
                    'access_pos', 'process_sales', 'manage_cash_drawer'
                ]
            ],
            'bartender' => [
                'display_name' => 'Bartender',
                'description' => 'Bar operations and drink preparation',
                'permissions' => [
                    'access_pos', 'process_sales', 'manage_cash_drawer',
                    'clock_in_out', 'view_schedule', 'view_profile',
                    'manage_bar_inventory', 'create_drink_orders'
                ]
            ],
            'chef' => [
                'display_name' => 'Chef',
                'description' => 'Kitchen operations and food preparation',
                'permissions' => [
                    'access_pos', 'view_orders', 'update_order_status',
                    'clock_in_out', 'view_schedule', 'view_profile',
                    'manage_kitchen_inventory', 'create_food_orders'
                ]
            ],
            'server' => [
                'display_name' => 'Server',
                'description' => 'Restaurant service and customer orders',
                'permissions' => [
                    'access_pos', 'process_sales', 'manage_cash_drawer',
                    'take_orders', 'update_order_status', 'view_menu',
                    'clock_in_out', 'view_schedule', 'view_profile'
                ]
            ],
            'bar_staff' => [
                'display_name' => 'Bar Staff',
                'description' => 'Bar and restaurant operations',
                'permissions' => [
                    'access_pos', 'process_sales', 'manage_cash_drawer',
                    'clock_in_out', 'view_schedule', 'view_profile'
                ]
            ],
            'restaurant_staff' => [
                'display_name' => 'Restaurant Staff',
                'description' => 'Restaurant operations',
                'permissions' => [
                    'access_pos', 'process_sales', 'manage_cash_drawer',
                    'clock_in_out', 'view_schedule', 'view_profile'
                ]
            ],
            'housekeeping' => [
                'display_name' => 'Housekeeping',
                'description' => 'Room cleaning and maintenance',
                'permissions' => [
                    'view_rooms', 'update_room_status', 'view_reservations',
                    'clock_in_out'
                ]
            ],
            'maintenance' => [
                'display_name' => 'Maintenance',
                'description' => 'Hotel maintenance and repairs',
                'permissions' => [
                    'view_rooms', 'update_room_status', 'manage_maintenance',
                    'manage_iptv_devices', 'clock_in_out'
                ]
            ],
            'staff' => [
                'display_name' => 'Staff',
                'description' => 'General hotel staff',
                'permissions' => [
                    'view_profile', 'clock_in_out', 'request_leave',
                    'view_schedule'
                ]
            ]
        ];
    }
}
