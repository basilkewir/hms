<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class ManagerPermissionsSeeder extends Seeder
{
    public function run()
    {
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description'  => 'Hotel manager with full operational access',
                'is_active'    => true,
            ]
        );

        $permissions = [
            // Operations
            'manage_reservations'   => ['category' => 'operations',  'display' => 'Manage Reservations'],
            'manage_guests'         => ['category' => 'operations',  'display' => 'Manage Guests'],
            'check_in_out'          => ['category' => 'operations',  'display' => 'Check In / Check Out'],
            // Property
            'manage_rooms'          => ['category' => 'property',    'display' => 'Manage Rooms'],
            'view_rooms'            => ['category' => 'property',    'display' => 'View Rooms'],
            // Housekeeping
            'manage_housekeeping'   => ['category' => 'housekeeping','display' => 'Manage Housekeeping'],
            // Maintenance
            'manage_maintenance'    => ['category' => 'maintenance', 'display' => 'Manage Maintenance'],
            // Staff
            'manage_staff'          => ['category' => 'staff',       'display' => 'Manage Staff'],
            'view_schedule'         => ['category' => 'staff',       'display' => 'View Schedule'],
            // Financial
            'manage_financials'     => ['category' => 'financial',   'display' => 'Manage Financials'],
            'manage_expenses'       => ['category' => 'financial',   'display' => 'Manage Expenses'],
            'approve_expenses'      => ['category' => 'financial',   'display' => 'Approve Expenses'],
            'view_budgets'          => ['category' => 'financial',   'display' => 'View Budgets'],
            'manage_budgets'        => ['category' => 'financial',   'display' => 'Manage Budgets'],
            'view_reports'          => ['category' => 'financial',   'display' => 'View Reports'],
            // POS
            'access_pos'            => ['category' => 'pos',         'display' => 'Access POS'],
            'process_sales'         => ['category' => 'pos',         'display' => 'Process Sales'],
            'view_pos_reports'      => ['category' => 'pos',         'display' => 'View POS Reports'],
            'manage_inventory'      => ['category' => 'pos',         'display' => 'Manage Inventory'],
            'manage_purchases'      => ['category' => 'pos',         'display' => 'Manage Purchases'],
            'manage_suppliers'      => ['category' => 'pos',         'display' => 'Manage Suppliers'],
            // Services
            'manage_services'       => ['category' => 'services',    'display' => 'Manage Services'],
        ];

        foreach ($permissions as $name => $meta) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $meta['display'],
                    'category'     => $meta['category'],
                    'description'  => 'Permission to ' . strtolower($meta['display']),
                ]
            );

            $alreadyAssigned = \DB::table('role_has_permissions')
                ->where('role_id', $managerRole->id)
                ->where('permission_id', $permission->id)
                ->exists();

            if (!$alreadyAssigned) {
                \DB::table('role_has_permissions')->insert([
                    'role_id'       => $managerRole->id,
                    'permission_id' => $permission->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $this->command->info("Assigned '{$name}' to manager role.");
            } else {
                $this->command->info("'{$name}' already assigned to manager role.");
            }
        }

        $this->command->info('Manager permissions seeder completed.');
    }
}
