<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class HousekeepingPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Find or create the housekeeping role
        $housekeepingRole = Role::firstOrCreate(
            ['name' => 'housekeeping'],
            [
                'display_name' => 'Housekeeping Staff',
                'description' => 'Housekeeping operations and room management',
                'is_active' => true
            ]
        );

        // Find or create housekeeping permissions
        $housekeepingPermissions = [
            'access_housekeeping',
            'view_housekeeping_tasks',
            'update_housekeeping_tasks',
            'create_housekeeping_tasks',
            'manage_housekeeping_schedule',
            'view_housekeeping_dashboard',
            'update_room_status',
            'manage_housekeeping_inventory',
            'view_housekeeping_reports',
            'manage_housekeeping_notifications',
            'upload_housekeeping_photos',
            'submit_housekeeping_checklists',
            'manage_housekeeping_supplies',
            'view_room_status',
            'manage_room_cleaning',
            'manage_room_inspection',
            'manage_room_maintenance',
            'manage_housekeeping_assignments',
            'view_housekeeping_analytics',
            'manage_housekeeping_performance'
        ];

        foreach ($housekeepingPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'housekeeping',
                    'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
                ]
            );

            // Assign permission to housekeeping role if not already assigned
            if (!$housekeepingRole->hasPermission($permission)) {
                $housekeepingRole->givePermission($permission);
                $this->command->info("Assigned permission '{$permissionName}' to housekeeping role");
            } else {
                $this->command->info("Permission '{$permissionName}' already assigned to housekeeping role");
            }
        }

        // Setup Housekeeping Manager Role
        $housekeepingManagerRole = Role::firstOrCreate(
            ['name' => 'housekeeping_manager'],
            [
                'display_name' => 'Housekeeping Manager',
                'description' => 'Manage housekeeping operations and staff',
                'is_active' => true
            ]
        );

        // Find or create housekeeping manager permissions
        $housekeepingManagerPermissions = [
            'access_housekeeping',
            'view_housekeeping_tasks',
            'update_housekeeping_tasks',
            'create_housekeeping_tasks',
            'manage_housekeeping_schedule',
            'view_housekeeping_dashboard',
            'update_room_status',
            'manage_housekeeping_inventory',
            'view_housekeeping_reports',
            'manage_housekeeping_notifications',
            'upload_housekeeping_photos',
            'submit_housekeeping_checklists',
            'manage_housekeeping_supplies',
            'view_room_status',
            'manage_room_cleaning',
            'manage_room_inspection',
            'manage_room_maintenance',
            'manage_housekeeping_assignments',
            'view_housekeeping_analytics',
            'manage_housekeeping_performance',
            'approve_housekeeping_tasks',
            'manage_housekeeping_staff',
            'view_housekeeping_metrics',
            'generate_housekeeping_reports',
            'manage_housekeeping_priorities',
            'override_housekeeping_status',
            'manage_housekeeping_alerts',
            'view_housekeeping_logs',
            'manage_housekeeping_checklists',
            'manage_housekeeping_photos',
            'manage_housekeeping_inspection',
            'manage_housekeeping_maintenance'
        ];

        foreach ($housekeepingManagerPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'housekeeping',
                    'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
                ]
            );

            // Assign permission to housekeeping manager role if not already assigned
            if (!$housekeepingManagerRole->hasPermission($permission)) {
                $housekeepingManagerRole->givePermission($permission);
                $this->command->info("Assigned permission '{$permissionName}' to housekeeping manager role");
            } else {
                $this->command->info("Permission '{$permissionName}' already assigned to housekeeping manager role");
            }
        }

        // Setup Manager Role with housekeeping permissions
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description' => 'Hotel management and operations',
                'is_active' => true
            ]
        );

        // Find or create manager housekeeping permissions
        $managerHousekeepingPermissions = [
            'access_housekeeping',
            'view_housekeeping_tasks',
            'update_housekeeping_tasks',
            'create_housekeeping_tasks',
            'manage_housekeeping_schedule',
            'view_housekeeping_dashboard',
            'update_room_status',
            'manage_housekeeping_inventory',
            'view_housekeeping_reports',
            'manage_housekeeping_notifications',
            'upload_housekeeping_photos',
            'submit_housekeeping_checklists',
            'manage_housekeeping_supplies',
            'view_room_status',
            'manage_room_cleaning',
            'manage_room_inspection',
            'manage_room_maintenance',
            'manage_housekeeping_assignments',
            'view_housekeeping_analytics',
            'manage_housekeeping_performance',
            'approve_housekeeping_tasks',
            'manage_housekeeping_staff',
            'view_housekeeping_metrics',
            'generate_housekeeping_reports',
            'manage_housekeeping_priorities',
            'override_housekeeping_status',
            'manage_housekeeping_alerts',
            'view_housekeeping_logs',
            'manage_housekeeping_checklists',
            'manage_housekeeping_photos',
            'manage_housekeeping_inspection',
            'manage_housekeeping_maintenance'
        ];

        foreach ($managerHousekeepingPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'housekeeping',
                    'description' => 'Permission to ' . str_replace('_', ' ', $permissionName)
                ]
            );

            // Assign permission to manager role if not already assigned
            if (!$managerRole->hasPermission($permission)) {
                $managerRole->givePermission($permission);
                $this->command->info("Assigned permission '{$permissionName}' to manager role");
            } else {
                $this->command->info("Permission '{$permissionName}' already assigned to manager role");
            }
        }

        // Setup Admin Role with all housekeeping permissions
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access and management',
                'is_active' => true
            ]
        );

        foreach ($housekeepingManagerPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                [
                    'display_name' => ucfirst(str_replace('_', ' ', $permissionName)),
                    'category' => 'housekeeping',
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

        $this->command->info('Housekeeping permissions setup completed successfully!');
    }
}
