<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'category',
        'description',
        'guard_name',
    ];

    // Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions')
                    ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions')
                    ->withTimestamps()
                    ->withPivot(['granted', 'granted_by', 'granted_at']);
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Static Methods
    public static function getDefaultPermissions()
    {
        return [
            // User Management
            'manage_users' => [
                'display_name' => 'Manage Users',
                'category' => 'User Management',
                'description' => 'Create, edit, and delete user accounts'
            ],
            'manage_roles' => [
                'display_name' => 'Manage Roles',
                'category' => 'User Management',
                'description' => 'Create and manage user roles'
            ],
            'manage_permissions' => [
                'display_name' => 'Manage Permissions',
                'category' => 'User Management',
                'description' => 'Assign and revoke permissions'
            ],
            'view_profile' => [
                'display_name' => 'View Profile',
                'category' => 'User Management',
                'description' => 'View own profile information'
            ],

            // Reservation Management
            'manage_reservations' => [
                'display_name' => 'Manage Reservations',
                'category' => 'Reservations',
                'description' => 'Create, edit, and cancel reservations'
            ],
            'view_reservations' => [
                'display_name' => 'View Reservations',
                'category' => 'Reservations',
                'description' => 'View reservation information'
            ],
            'check_in_out' => [
                'display_name' => 'Check In/Out',
                'category' => 'Reservations',
                'description' => 'Process guest check-in and check-out'
            ],

            // Guest Management
            'manage_guests' => [
                'display_name' => 'Manage Guests',
                'category' => 'Guest Management',
                'description' => 'Create and edit guest profiles'
            ],
            'view_guests' => [
                'display_name' => 'View Guests',
                'category' => 'Guest Management',
                'description' => 'View guest information'
            ],
            'verify_police_details' => [
                'display_name' => 'Verify Police Details',
                'category' => 'Guest Management',
                'description' => 'Verify and manage police verification details'
            ],

            // Room Management
            'manage_rooms' => [
                'display_name' => 'Manage Rooms',
                'category' => 'Room Management',
                'description' => 'Create, edit, and configure rooms'
            ],
            'view_rooms' => [
                'display_name' => 'View Rooms',
                'category' => 'Room Management',
                'description' => 'View room information and status'
            ],
            'update_room_status' => [
                'display_name' => 'Update Room Status',
                'category' => 'Room Management',
                'description' => 'Update room cleaning and maintenance status'
            ],
            'manage_maintenance' => [
                'display_name' => 'Manage Maintenance',
                'category' => 'Room Management',
                'description' => 'Schedule and track room maintenance'
            ],

            // Financial Management
            'manage_financials' => [
                'display_name' => 'Manage Financials',
                'category' => 'Financial',
                'description' => 'Full financial management access'
            ],
            'view_financials' => [
                'display_name' => 'View Financials',
                'category' => 'Financial',
                'description' => 'View financial reports and data'
            ],
            'process_payments' => [
                'display_name' => 'Process Payments',
                'category' => 'Financial',
                'description' => 'Process guest payments and refunds'
            ],
            'process_refunds' => [
                'display_name' => 'Process Refunds',
                'category' => 'Financial',
                'description' => 'Process payment refunds'
            ],
            'view_refunds' => [
                'display_name' => 'View Refunds',
                'category' => 'Financial',
                'description' => 'View refund records'
            ],
            'manage_refunds' => [
                'display_name' => 'Manage Refunds',
                'category' => 'Financial',
                'description' => 'Manage refund operations'
            ],
            'manage_expenses' => [
                'display_name' => 'Manage Expenses',
                'category' => 'Financial',
                'description' => 'Create and manage hotel expenses'
            ],
            'approve_expenses' => [
                'display_name' => 'Approve Expenses',
                'category' => 'Financial',
                'description' => 'Approve expense requests'
            ],
            'manage_payroll' => [
                'display_name' => 'Manage Payroll',
                'category' => 'Financial',
                'description' => 'Process employee payroll'
            ],

            // IPTV Management
            'manage_iptv' => [
                'display_name' => 'Manage IPTV',
                'category' => 'IPTV',
                'description' => 'Full IPTV system management'
            ],
            'view_iptv' => [
                'display_name' => 'View IPTV',
                'category' => 'IPTV',
                'description' => 'View IPTV status and usage'
            ],
            'manage_iptv_devices' => [
                'display_name' => 'Manage IPTV Devices',
                'category' => 'IPTV',
                'description' => 'Manage IPTV device configuration'
            ],
            'manage_channels' => [
                'display_name' => 'Manage Channels',
                'category' => 'IPTV',
                'description' => 'Manage IPTV channels and packages'
            ],

            // Staff Management
            'manage_staff' => [
                'display_name' => 'Manage Staff',
                'category' => 'Staff Management',
                'description' => 'Manage employee information and schedules'
            ],
            'view_staff' => [
                'display_name' => 'View Staff',
                'category' => 'Staff Management',
                'description' => 'View employee information'
            ],
            'manage_schedules' => [
                'display_name' => 'Manage Schedules',
                'category' => 'Staff Management',
                'description' => 'Create and manage work schedules'
            ],
            'view_schedule' => [
                'display_name' => 'View Schedule',
                'category' => 'Staff Management',
                'description' => 'View own work schedule'
            ],

            // Time Tracking
            'clock_in_out' => [
                'display_name' => 'Clock In/Out',
                'category' => 'Time Tracking',
                'description' => 'Clock in and out for work shifts'
            ],
            'manage_time_entries' => [
                'display_name' => 'Manage Time Entries',
                'category' => 'Time Tracking',
                'description' => 'Edit and approve time entries'
            ],
            'request_leave' => [
                'display_name' => 'Request Leave',
                'category' => 'Time Tracking',
                'description' => 'Submit leave requests'
            ],
            'approve_leave' => [
                'display_name' => 'Approve Leave',
                'category' => 'Time Tracking',
                'description' => 'Approve or reject leave requests'
            ],

            // Reports
            'view_reports' => [
                'display_name' => 'View Reports',
                'category' => 'Reports',
                'description' => 'Access to system reports'
            ],
            'export_reports' => [
                'display_name' => 'Export Reports',
                'category' => 'Reports',
                'description' => 'Export reports to various formats'
            ],

            // System Settings
            'manage_settings' => [
                'display_name' => 'Manage Settings',
                'category' => 'System',
                'description' => 'Configure system settings'
            ],
            'view_logs' => [
                'display_name' => 'View Logs',
                'category' => 'System',
                'description' => 'View system logs and audit trails'
            ],

            // POS System
            'access_pos' => [
                'display_name' => 'Access POS',
                'category' => 'POS',
                'description' => 'Access point of sale system'
            ],
            'process_sales' => [
                'display_name' => 'Process Sales',
                'category' => 'POS',
                'description' => 'Process sales transactions'
            ],
            'manage_cash_drawer' => [
                'display_name' => 'Manage Cash Drawer',
                'category' => 'POS',
                'description' => 'Open and close cash drawer sessions'
            ],
            'manage_inventory' => [
                'display_name' => 'Manage Inventory',
                'category' => 'POS',
                'description' => 'Manage product inventory and stock levels'
            ],
            'manage_purchases' => [
                'display_name' => 'Manage Purchases',
                'category' => 'POS',
                'description' => 'Create and manage purchase orders'
            ],
            'manage_suppliers' => [
                'display_name' => 'Manage Suppliers',
                'category' => 'POS',
                'description' => 'Manage supplier information'
            ],
            'view_pos_reports' => [
                'display_name' => 'View POS Reports',
                'category' => 'POS',
                'description' => 'View POS sales and inventory reports'
            ],

            // Budget Management
            'manage_budgets' => [
                'display_name' => 'Manage Budgets',
                'category' => 'Budget',
                'description' => 'Full budget management access'
            ],
            'create_budgets' => [
                'display_name' => 'Create Budgets',
                'category' => 'Budget',
                'description' => 'Create new budgets'
            ],
            'edit_budgets' => [
                'display_name' => 'Edit Budgets',
                'category' => 'Budget',
                'description' => 'Edit existing budgets'
            ],
            'delete_budgets' => [
                'display_name' => 'Delete Budgets',
                'category' => 'Budget',
                'description' => 'Delete budgets'
            ],
            'view_budgets' => [
                'display_name' => 'View Budgets',
                'category' => 'Budget',
                'description' => 'View budget information and reports'
            ],
            'approve_budgets' => [
                'display_name' => 'Approve Budgets',
                'category' => 'Budget',
                'description' => 'Approve budgets for implementation'
            ],
            'submit_budgets_for_approval' => [
                'display_name' => 'Submit Budgets for Approval',
                'category' => 'Budget',
                'description' => 'Submit budgets for approval workflow'
            ],
            'reject_budgets' => [
                'display_name' => 'Reject Budgets',
                'category' => 'Budget',
                'description' => 'Reject submitted budgets'
            ],
            'archive_budgets' => [
                'display_name' => 'Archive Budgets',
                'category' => 'Budget',
                'description' => 'Archive completed or expired budgets'
            ],
            'monitor_budgets' => [
                'display_name' => 'Monitor Budgets',
                'category' => 'Budget',
                'description' => 'Monitor budget utilization and performance'
            ],
            'create_budget_alerts' => [
                'display_name' => 'Create Budget Alerts',
                'category' => 'Budget',
                'description' => 'Set up budget alerts and notifications'
            ],
            'view_budget_analytics' => [
                'display_name' => 'View Budget Analytics',
                'category' => 'Budget',
                'description' => 'Access advanced budget analytics and insights'
            ],
            'export_budget_data' => [
                'display_name' => 'Export Budget Data',
                'category' => 'Budget',
                'description' => 'Export budget data to various formats'
            ],
            'manage_budget_categories' => [
                'display_name' => 'Manage Budget Categories',
                'category' => 'Budget',
                'description' => 'Manage budget categories and classifications'
            ],
            'manage_budget_departments' => [
                'display_name' => 'Manage Budget Departments',
                'category' => 'Budget',
                'description' => 'Manage department-specific budgets'
            ],
        ];
    }

    public static function getPermissionsByCategory()
    {
        $permissions = self::getDefaultPermissions();
        $grouped = [];

        foreach ($permissions as $name => $permission) {
            $category = $permission['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$name] = $permission;
        }

        return $grouped;
    }

    // Spatie PermissionContract Interface Methods
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
        $permission = static::where('name', $name)->first();

        if (!$permission) {
            return static::create(['name' => $name, 'guard_name' => $guardName ?? 'web']);
        }

        return $permission;
    }

    public function getGuardName(): string
    {
        return $this->guard_name ?? 'web';
    }
}
