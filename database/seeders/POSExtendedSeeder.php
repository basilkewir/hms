<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\PosExpenseCategory;
use App\Models\User;
use App\Models\Role;

class POSExtendedSeeder extends Seeder
{
    public function run()
    {
        // Create suppliers
        $suppliers = [
            [
                'name' => 'Food & Beverage Distributors Ltd',
                'contact_person' => 'John Smith',
                'email' => 'orders@fbdistributors.com',
                'phone' => '+1234567890',
                'address' => '123 Warehouse Street, Industrial District',
                'credit_limit' => 10000.00,
                'is_active' => true
            ],
            [
                'name' => 'Premium Beverages Co.',
                'contact_person' => 'Sarah Johnson',
                'email' => 'sales@premiumbev.com',
                'phone' => '+1234567891',
                'address' => '456 Commerce Ave, Business Park',
                'credit_limit' => 5000.00,
                'is_active' => true
            ],
            [
                'name' => 'Local Fresh Produce',
                'contact_person' => 'Mike Wilson',
                'email' => 'mike@localfresh.com',
                'phone' => '+1234567892',
                'address' => '789 Market Street, Downtown',
                'credit_limit' => 3000.00,
                'is_active' => true
            ],
            [
                'name' => 'Hotel Supplies Express',
                'contact_person' => 'Lisa Brown',
                'email' => 'info@hotelsupplies.com',
                'phone' => '+1234567893',
                'address' => '321 Supply Chain Blvd, Logistics Center',
                'credit_limit' => 15000.00,
                'is_active' => true
            ]
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate(
                ['name' => $supplierData['name']],
                $supplierData
            );
        }

        // Create POS expense categories
        $expenseCategories = [
            [
                'name' => 'Food Supplies',
                'description' => 'Food ingredients and supplies for restaurant',
                'color' => '#EF4444',
                'is_active' => true
            ],
            [
                'name' => 'Beverage Supplies',
                'description' => 'Alcoholic and non-alcoholic beverages',
                'color' => '#3B82F6',
                'is_active' => true
            ],
            [
                'name' => 'Equipment Maintenance',
                'description' => 'POS equipment and kitchen maintenance',
                'color' => '#F59E0B',
                'is_active' => true
            ],
            [
                'name' => 'Cleaning Supplies',
                'description' => 'Cleaning materials for bar and restaurant',
                'color' => '#10B981',
                'is_active' => true
            ],
            [
                'name' => 'Marketing',
                'description' => 'Promotional materials and advertising',
                'color' => '#8B5CF6',
                'is_active' => true
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, gas for F&B operations',
                'color' => '#6B7280',
                'is_active' => true
            ],
            [
                'name' => 'Staff Expenses',
                'description' => 'Staff meals, uniforms, training',
                'color' => '#EC4899',
                'is_active' => true
            ]
        ];

        foreach ($expenseCategories as $categoryData) {
            PosExpenseCategory::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        // Create bar and restaurant staff users
        $posStaff = [
            [
                'employee_id' => 'EMP006',
                'first_name' => 'Alex',
                'last_name' => 'Bartender',
                'email' => 'bartender@hotel.com',
                'password' => bcrypt('password'),
                'department' => 'Food & Beverage',
                'position' => 'Bartender',
                'hire_date' => now()->subMonths(4),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'bar_staff'
            ],
            [
                'employee_id' => 'EMP007',
                'first_name' => 'Maria',
                'last_name' => 'Server',
                'email' => 'server@hotel.com',
                'password' => bcrypt('password'),
                'department' => 'Food & Beverage',
                'position' => 'Restaurant Server',
                'hire_date' => now()->subMonths(2),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'restaurant_staff'
            ],
            [
                'employee_id' => 'EMP008',
                'first_name' => 'Carlos',
                'last_name' => 'Chef',
                'email' => 'chef@hotel.com',
                'password' => bcrypt('password'),
                'department' => 'Food & Beverage',
                'position' => 'Head Chef',
                'hire_date' => now()->subMonths(6),
                'employment_status' => 'active',
                'is_active' => true,
                'role' => 'restaurant_staff'
            ]
        ];

        foreach ($posStaff as $staffData) {
            $roleName = $staffData['role'];
            unset($staffData['role']);
            
            $user = User::firstOrCreate(
                ['email' => $staffData['email']],
                $staffData
            );
            
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->assignRole($role);
            }
        }

        echo "Created suppliers, expense categories, and POS staff users\n";
    }
}