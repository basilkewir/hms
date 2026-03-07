<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class BudgetPermissionsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Setting up budget permissions...');

        // Define all budget permissions
        $permissions = [
            // Core budget management
            'manage_budgets' => [
                'display_name' => 'Manage Budgets',
                'description' => 'Full access to manage all budget features',
                'category' => 'budget'
            ],
            'view_budgets' => [
                'display_name' => 'View Budgets',
                'description' => 'Permission to view budgets',
                'category' => 'budget'
            ],
            'create_budgets' => [
                'display_name' => 'Create Budgets',
                'description' => 'Permission to create new budgets',
                'category' => 'budget'
            ],
            'edit_budgets' => [
                'display_name' => 'Edit Budgets',
                'description' => 'Permission to edit existing budgets',
                'category' => 'budget'
            ],
            'delete_budgets' => [
                'display_name' => 'Delete Budgets',
                'description' => 'Permission to delete budgets',
                'category' => 'budget'
            ],
            'approve_budgets' => [
                'display_name' => 'Approve Budgets',
                'description' => 'Permission to approve pending budgets',
                'category' => 'budget'
            ],
            'submit_budgets_for_approval' => [
                'display_name' => 'Submit Budgets for Approval',
                'description' => 'Permission to submit budgets for approval',
                'category' => 'budget'
            ],
            'archive_budgets' => [
                'display_name' => 'Archive Budgets',
                'description' => 'Permission to archive approved budgets',
                'category' => 'budget'
            ],

            // Budget reports and analytics
            'view_budget_reports' => [
                'display_name' => 'View Budget Reports',
                'description' => 'Permission to view budget reports and analysis',
                'category' => 'budget'
            ],
            'export_budget_data' => [
                'display_name' => 'Export Budget Data',
                'description' => 'Permission to export budget data',
                'category' => 'budget'
            ],
            'view_budget_analytics' => [
                'display_name' => 'View Budget Analytics',
                'description' => 'Permission to view budget analytics',
                'category' => 'budget'
            ],
            'view_budget_alerts' => [
                'display_name' => 'View Budget Alerts',
                'description' => 'Permission to view budget alerts',
                'category' => 'budget'
            ],
        ];

        // Create or update all permissions
        foreach ($permissions as $name => $data) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data['display_name'],
                    'category' => $data['category'],
                    'description' => $data['description']
                ]
            );
            $this->command->info("Created permission: {$name}");
        }

        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $accountantRole = Role::where('name', 'accountant')->first();

        // Assign all budget permissions to admin
        if ($adminRole) {
            $this->command->info('Assigning all budget permissions to admin role...');
            $adminPermissionIds = [];
            foreach (array_keys($permissions) as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $adminPermissionIds[] = $permission->id;
                }
            }
            $adminRole->permissions()->syncWithoutDetaching($adminPermissionIds);
        }

        // Assign budget permissions to accountant
        if ($accountantRole) {
            $this->command->info('Assigning budget permissions to accountant role...');
            $accountantPermissions = [
                'view_budgets',
                'view_budget_reports',
                'view_budget_analytics',
                'export_budget_data',
            ];

            $accountantPermissionIds = [];
            foreach ($accountantPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $accountantPermissionIds[] = $permission->id;
                }
            }
            $accountantRole->permissions()->syncWithoutDetaching($accountantPermissionIds);
        }

        // Assign limited budget permissions to manager
        if ($managerRole) {
            $this->command->info('Assigning budget permissions to manager role...');
            $managerPermissions = [
                'view_budgets',
                'view_budget_reports',
                'create_budgets',
                'edit_budgets',
            ];

            $managerPermissionIds = [];
            foreach ($managerPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $managerPermissionIds[] = $permission->id;
                }
            }
            $managerRole->permissions()->syncWithoutDetaching($managerPermissionIds);
        }

        $this->command->info('Budget permissions setup completed successfully!');
    }
}
