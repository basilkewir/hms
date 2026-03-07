<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Find or create the admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access and management',
                'is_active' => true
            ]
        );

        // Find or create all necessary POS permissions
        $posPermissions = [
            'access_pos',
            'process_sales',
            'manage_cash_drawer',
            'manage_inventory',
            'manage_purchases',
            'manage_suppliers',
            'view_pos_reports',
            'manage_expenses'
        ];

        // Find or create budget permissions
        $budgetPermissions = [
            'manage_budgets',
            'create_budgets',
            'edit_budgets',
            'delete_budgets',
            'view_budgets',
            'approve_budgets',
            'view_budget_reports'
        ];

        // Find or create refund permissions
        $refundPermissions = [
            'process_refunds',
            'view_refunds',
            'manage_refunds'
        ];

        foreach ($refundPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'financial',
                    'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
                ]
            );

            // Assign permission to admin role if not already assigned
            if (!$adminRole->hasPermission($permission)) {
                $adminRole->givePermission($permission);
                $this->command->info("Assigned permission '{$permissionName}' to admin role");
            } else {
                $this->command->info("Permission '{$permissionName}' already assigned to admin role");
            }
        }

        foreach ($posPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'pos',
                    'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
                ]
            );

            // Assign permission to admin role if not already assigned
            if (!$adminRole->hasPermission($permission)) {
                $adminRole->givePermission($permission);
                $this->command->info("Assigned permission '{$permissionName}' to admin role");
            } else {
                $this->command->info("Permission '{$permissionName}' already assigned to admin role");
            }
        }

        foreach ($budgetPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'budget',
                    'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
                ]
            );

            // Assign permission to admin role if not already assigned
            if (!$adminRole->hasPermission($permission)) {
                $adminRole->givePermission($permission);
                $this->command->info("Assigned permission '{$permissionName}' to admin role");
            } else {
                $this->command->info("Permission '{$permissionName}' already assigned to admin role");
            }
        }

        // Setup Accountant Role
        $accountantRole = Role::firstOrCreate(
            ['name' => 'accountant'],
            [
                'display_name' => 'Accountant',
                'description' => 'Financial management and reporting',
                'is_active' => true
            ]
        );

        // Find or create accountant permissions
        $accountantPermissions = [
            'manage_financials',
            'view_reports',
            'manage_expenses',
            'manage_payroll',
            'view_reservations',
            'process_payments',
            'view_pos_reports',
            'manage_purchases',
            'process_refunds',
            'view_refunds',
            'manage_refunds',
            'view_budgets',
            'approve_budgets',
            'monitor_budgets',
            'view_budget_analytics',
            'export_budget_data',
            'view_budget_reports'
        ];

        foreach ($accountantPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'financial',
                    'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
                ]
            );

            // Assign permission to accountant role if not already assigned
            if (!$accountantRole->hasPermission($permission)) {
                $accountantRole->givePermission($permission);
                $this->command->info("Assigned permission '{$permissionName}' to accountant role");
            } else {
                $this->command->info("Permission '{$permissionName}' already assigned to accountant role");
            }
        }

        $this->command->info('Admin permissions setup completed successfully!');
    }
}
