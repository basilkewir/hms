<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimpleAdminPosPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Check if admin role exists
        $adminRole = DB::table('roles')->where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found!');
            return;
        }

        // POS permissions to assign
        $posPermissions = [
            ['name' => 'access_pos', 'display_name' => 'Access POS', 'category' => 'pos', 'description' => 'Access point of sale system'],
            ['name' => 'process_sales', 'display_name' => 'Process Sales', 'category' => 'pos', 'description' => 'Process sales transactions'],
            ['name' => 'manage_cash_drawer', 'display_name' => 'Manage Cash Drawer', 'category' => 'pos', 'description' => 'Open and close cash drawer'],
            ['name' => 'manage_inventory', 'display_name' => 'Manage Inventory', 'category' => 'pos', 'description' => 'Manage product inventory'],
            ['name' => 'manage_purchases', 'display_name' => 'Manage Purchases', 'category' => 'pos', 'description' => 'Manage purchase orders'],
            ['name' => 'manage_suppliers', 'display_name' => 'Manage Suppliers', 'category' => 'pos', 'description' => 'Manage suppliers'],
            ['name' => 'view_pos_reports', 'display_name' => 'View POS Reports', 'category' => 'pos', 'description' => 'View point of sale reports'],
            ['name' => 'manage_expenses', 'display_name' => 'Manage Expenses', 'category' => 'pos', 'description' => 'Manage expenses']
        ];

        // Refund permissions to assign
        $refundPermissions = [
            ['name' => 'process_refunds', 'display_name' => 'Process Refunds', 'category' => 'financial', 'description' => 'Process payment refunds'],
            ['name' => 'view_refunds', 'display_name' => 'View Refunds', 'category' => 'financial', 'description' => 'View refund records'],
            ['name' => 'manage_refunds', 'display_name' => 'Manage Refunds', 'category' => 'financial', 'description' => 'Manage refund operations']
        ];

        foreach ($posPermissions as $permissionData) {
            // Check if permission exists, if not create it
            $permission = DB::table('permissions')->where('name', $permissionData['name'])->first();

            if (!$permission) {
                $permissionId = DB::table('permissions')->insertGetId($permissionData);
                $this->command->info("Created permission: {$permissionData['name']}");
            } else {
                $permissionId = $permission->id;
                $this->command->info("Permission already exists: {$permissionData['name']}");
            }

            // Check if role already has this permission
            $existingAssignment = DB::table('role_has_permissions')
                ->where('role_id', $adminRole->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$existingAssignment) {
                DB::table('role_has_permissions')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->command->info("Assigned permission '{$permissionData['name']}' to admin role");
            } else {
                $this->command->info("Permission '{$permissionData['name']}' already assigned to admin role");
            }
        }

        foreach ($refundPermissions as $permissionData) {
            // Check if permission exists, if not create it
            $permission = DB::table('permissions')->where('name', $permissionData['name'])->first();

            if (!$permission) {
                $permissionId = DB::table('permissions')->insertGetId($permissionData);
                $this->command->info("Created permission: {$permissionData['name']}");
            } else {
                $permissionId = $permission->id;
                $this->command->info("Permission already exists: {$permissionData['name']}");
            }

            // Check if role already has this permission
            $existingAssignment = DB::table('role_has_permissions')
                ->where('role_id', $adminRole->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$existingAssignment) {
                DB::table('role_has_permissions')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->command->info("Assigned permission '{$permissionData['name']}' to admin role");
            } else {
                $this->command->info("Permission '{$permissionData['name']}' already assigned to admin role");
            }
        }

        $this->command->info('Admin POS permissions setup completed successfully!');
    }
}
