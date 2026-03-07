<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserAccountsSeeder extends Seeder
{
    public function run()
    {
        // Ensure roles exist first
        $roles = Role::getDefaultRoles();

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'is_active' => true,
                ]
            );

            // Assign permissions to role
            if (isset($roleData['permissions'])) {
                $permissions = \App\Models\Permission::whereIn('name', $roleData['permissions'])->get();
                $role->permissions()->sync($permissions->pluck('id'));
            }
        }

        // Create users for each role
        $users = [
            [
                'employee_id' => 'ADM001',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => 'admin@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Administration',
                'position' => 'System Administrator',
                'hire_date' => now()->subYears(2),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'admin'
            ],
            [
                'employee_id' => 'MGR001',
                'first_name' => 'Hotel',
                'last_name' => 'Manager',
                'email' => 'manager@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Management',
                'position' => 'General Manager',
                'hire_date' => now()->subYear(),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'manager'
            ],
            [
                'employee_id' => 'ACC001',
                'first_name' => 'Finance',
                'last_name' => 'Manager',
                'email' => 'accountant@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Finance',
                'position' => 'Accountant',
                'hire_date' => now()->subMonths(8),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'accountant'
            ],
            [
                'employee_id' => 'FDT001',
                'first_name' => 'Front',
                'last_name' => 'Desk',
                'email' => 'frontdesk@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Front Office',
                'position' => 'Front Desk Agent',
                'hire_date' => now()->subMonths(6),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'front_desk'
            ],
            [
                'employee_id' => 'HSK001',
                'first_name' => 'Hotel',
                'last_name' => 'Staff',
                'email' => 'staff@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Housekeeping',
                'position' => 'Housekeeper',
                'hire_date' => now()->subMonths(3),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'housekeeping'
            ],
            [
                'employee_id' => 'BAR001',
                'first_name' => 'Alex',
                'last_name' => 'Bartender',
                'email' => 'bartender@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Food & Beverage',
                'position' => 'Head Bartender',
                'hire_date' => now()->subMonths(4),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'bartender'
            ],
            [
                'employee_id' => 'CHF001',
                'first_name' => 'Carlos',
                'last_name' => 'Chef',
                'email' => 'chef@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Food & Beverage',
                'position' => 'Head Chef',
                'hire_date' => now()->subMonths(5),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'chef'
            ],
            [
                'employee_id' => 'SRV001',
                'first_name' => 'Maria',
                'last_name' => 'Server',
                'email' => 'server@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Food & Beverage',
                'position' => 'Senior Server',
                'hire_date' => now()->subMonths(2),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'server'
            ],
            [
                'employee_id' => 'BST001',
                'first_name' => 'John',
                'last_name' => 'Barstaff',
                'email' => 'barstaff@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Food & Beverage',
                'position' => 'Bar Staff',
                'hire_date' => now()->subMonths(3),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'bar_staff'
            ],
            [
                'employee_id' => 'RST001',
                'first_name' => 'Sarah',
                'last_name' => 'Restaurant',
                'email' => 'restaurant@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Food & Beverage',
                'position' => 'Restaurant Staff',
                'hire_date' => now()->subMonths(3),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'restaurant_staff'
            ],
            [
                'employee_id' => 'MNT001',
                'first_name' => 'Maintenance',
                'last_name' => 'Staff',
                'email' => 'maintenance@hotel.com',
                'password' => Hash::make('password'),
                'department' => 'Maintenance',
                'position' => 'Maintenance Technician',
                'hire_date' => now()->subMonths(4),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'maintenance'
            ]
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            // Check if user already exists
            $existingUser = User::where('email', $userData['email'])->first();

            if ($existingUser) {
                $this->command->info("User {$userData['email']} already exists, updating...");
                $existingUser->update($userData);
                $user = $existingUser;
            } else {
                $this->command->info("Creating user: {$userData['email']}");
                $user = User::create($userData);
            }

            // Assign role
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->syncRoles([$role->name]);
                $this->command->info("Assigned role '{$roleName}' to user {$userData['email']}");
            } else {
                $this->command->error("Role '{$roleName}' not found for user {$userData['email']}");
            }
        }

        $this->command->info('User accounts seeded successfully!');
        $this->command->info('You can now login with:');
        $this->command->info('Email: any-role@hotel.com (e.g., admin@hotel.com, bartender@hotel.com, etc.)');
        $this->command->info('Password: password');
    }
}
