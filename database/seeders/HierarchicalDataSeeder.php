<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class HierarchicalDataSeeder extends Seeder
{
    public function run()
    {
        // Delete in correct order due to foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Delete all roles first (they reference positions)
        Role::truncate();
        
        // Delete all positions (they reference departments)
        Position::truncate();
        
        // Delete all departments
        Department::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "Cleared all departments, positions, and roles\n";

        // Ensure permissions exist first
        $permissions = Permission::getDefaultPermissions();
        foreach ($permissions as $name => $data) {
            Permission::firstOrCreate(['name' => $name], $data);
        }

        // Create Departments
        $departments = [
            'Management' => [
                'description' => 'Hotel management and administration',
                'positions' => [
                    'General Manager' => [
                        'description' => 'Overall hotel management and operations',
                        'roles' => [
                            'manager' => [
                                'display_name' => 'Manager',
                                'description' => 'Hotel operations management',
                                'permissions' => [
                                    'manage_reservations', 'manage_guests', 'manage_rooms',
                                    'view_financials', 'view_reports', 'manage_staff',
                                    'approve_expenses', 'manage_iptv', 'access_pos',
                                    'process_sales', 'manage_cash_drawer', 'manage_inventory',
                                    'manage_purchases', 'view_pos_reports'
                                ]
                            ]
                        ]
                    ],
                    'Assistant Manager' => [
                        'description' => 'Assists general manager in daily operations',
                        'roles' => [
                            'assistant_manager' => [
                                'display_name' => 'Assistant Manager',
                                'description' => 'Assists in hotel operations management',
                                'permissions' => [
                                    'manage_reservations', 'manage_guests', 'view_rooms',
                                    'view_reports', 'manage_staff', 'access_pos',
                                    'process_sales', 'view_pos_reports'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Front Desk' => [
                'description' => 'Guest services and reservations',
                'positions' => [
                    'Front Desk Manager' => [
                        'description' => 'Manages front desk operations',
                        'roles' => [
                            'front_desk_manager' => [
                                'display_name' => 'Front Desk Manager',
                                'description' => 'Manages front desk operations',
                                'permissions' => [
                                    'manage_reservations', 'manage_guests', 'check_in_out',
                                    'view_rooms', 'process_payments', 'view_iptv',
                                    'access_pos', 'process_sales', 'manage_cash_drawer',
                                    'manage_staff'
                                ]
                            ]
                        ]
                    ],
                    'Receptionist' => [
                        'description' => 'Handles guest check-in/check-out',
                        'roles' => [
                            'front_desk' => [
                                'display_name' => 'Front Desk',
                                'description' => 'Guest services and reservations',
                                'permissions' => [
                                    'manage_reservations', 'manage_guests', 'check_in_out',
                                    'view_rooms', 'process_payments', 'view_iptv',
                                    'access_pos', 'process_sales', 'manage_cash_drawer'
                                ]
                            ]
                        ]
                    ],
                    'Concierge' => [
                        'description' => 'Provides guest services and information',
                        'roles' => [
                            'concierge' => [
                                'display_name' => 'Concierge',
                                'description' => 'Guest services and concierge',
                                'permissions' => [
                                    'manage_guests', 'view_reservations', 'view_rooms',
                                    'access_pos', 'process_sales'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Housekeeping' => [
                'description' => 'Room cleaning and maintenance',
                'positions' => [
                    'Housekeeping Manager' => [
                        'description' => 'Manages housekeeping staff',
                        'roles' => [
                            'housekeeping_manager' => [
                                'display_name' => 'Housekeeping Manager',
                                'description' => 'Manages housekeeping operations',
                                'permissions' => [
                                    'view_rooms', 'update_room_status', 'view_reservations',
                                    'manage_staff', 'clock_in_out'
                                ]
                            ]
                        ]
                    ],
                    'Room Attendant' => [
                        'description' => 'Cleans and maintains guest rooms',
                        'roles' => [
                            'housekeeping' => [
                                'display_name' => 'Housekeeping',
                                'description' => 'Room cleaning and maintenance',
                                'permissions' => [
                                    'view_rooms', 'update_room_status', 'view_reservations',
                                    'clock_in_out'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Maintenance' => [
                'description' => 'Hotel maintenance and repairs',
                'positions' => [
                    'Maintenance Manager' => [
                        'description' => 'Manages maintenance operations',
                        'roles' => [
                            'maintenance_manager' => [
                                'display_name' => 'Maintenance Manager',
                                'description' => 'Manages maintenance operations',
                                'permissions' => [
                                    'view_rooms', 'update_room_status', 'manage_maintenance',
                                    'manage_iptv_devices', 'manage_staff', 'clock_in_out'
                                ]
                            ]
                        ]
                    ],
                    'Maintenance Technician' => [
                        'description' => 'Performs repairs and maintenance',
                        'roles' => [
                            'maintenance' => [
                                'display_name' => 'Maintenance',
                                'description' => 'Hotel maintenance and repairs',
                                'permissions' => [
                                    'view_rooms', 'update_room_status', 'manage_maintenance',
                                    'manage_iptv_devices', 'clock_in_out'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Accounting' => [
                'description' => 'Financial management and reporting',
                'positions' => [
                    'Financial Controller' => [
                        'description' => 'Manages financial operations',
                        'roles' => [
                            'accountant' => [
                                'display_name' => 'Accountant',
                                'description' => 'Financial management and reporting',
                                'permissions' => [
                                    'manage_financials', 'view_reports', 'manage_expenses',
                                    'manage_payroll', 'view_reservations', 'process_payments',
                                    'view_pos_reports', 'manage_purchases'
                                ]
                            ]
                        ]
                    ],
                    'Accountant' => [
                        'description' => 'Handles accounting and bookkeeping',
                        'roles' => [
                            'junior_accountant' => [
                                'display_name' => 'Junior Accountant',
                                'description' => 'Assists with accounting tasks',
                                'permissions' => [
                                    'view_financials', 'view_reports', 'view_expenses',
                                    'view_reservations', 'view_pos_reports'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Restaurant' => [
                'description' => 'Food and beverage services',
                'positions' => [
                    'Head Chef' => [
                        'description' => 'Manages kitchen and food preparation',
                        'roles' => [
                            'chef' => [
                                'display_name' => 'Chef',
                                'description' => 'Kitchen operations and food preparation',
                                'permissions' => [
                                    'access_pos', 'view_orders', 'update_order_status',
                                    'clock_in_out', 'view_schedule', 'view_profile',
                                    'manage_kitchen_inventory', 'create_food_orders'
                                ]
                            ]
                        ]
                    ],
                    'Server' => [
                        'description' => 'Serves food and beverages to guests',
                        'roles' => [
                            'server' => [
                                'display_name' => 'Server',
                                'description' => 'Restaurant service and customer orders',
                                'permissions' => [
                                    'access_pos', 'process_sales', 'manage_cash_drawer',
                                    'take_orders', 'update_order_status', 'view_menu',
                                    'clock_in_out', 'view_schedule', 'view_profile'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Bar' => [
                'description' => 'Bar services and drink preparation',
                'positions' => [
                    'Head Bartender' => [
                        'description' => 'Manages bar staff and inventory',
                        'roles' => [
                            'bartender' => [
                                'display_name' => 'Bartender',
                                'description' => 'Bar operations and drink preparation',
                                'permissions' => [
                                    'access_pos', 'process_sales', 'manage_cash_drawer',
                                    'clock_in_out', 'view_schedule', 'view_profile',
                                    'manage_bar_inventory', 'create_drink_orders'
                                ]
                            ]
                        ]
                    ],
                    'Bartender' => [
                        'description' => 'Prepares and serves drinks',
                        'roles' => [
                            'bar_staff' => [
                                'display_name' => 'Bar Staff',
                                'description' => 'Bar and restaurant operations',
                                'permissions' => [
                                    'access_pos', 'process_sales', 'manage_cash_drawer',
                                    'clock_in_out', 'view_schedule', 'view_profile'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Administration' => [
                'description' => 'System administration and IT',
                'positions' => [
                    'System Administrator' => [
                        'description' => 'Manages system and IT infrastructure',
                        'roles' => [
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
                                    'view_pos_reports', 'manage_expenses'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Create departments, positions, and roles
        foreach ($departments as $deptName => $deptData) {
            $department = Department::create([
                'name' => $deptName,
                'description' => $deptData['description'],
                'is_active' => true
            ]);

            echo "Created department: {$deptName}\n";

            foreach ($deptData['positions'] as $posName => $posData) {
                $position = Position::create([
                    'department_id' => $department->id,
                    'name' => $posName,
                    'description' => $posData['description'],
                    'is_active' => true
                ]);

                echo "  Created position: {$posName}\n";

                foreach ($posData['roles'] as $roleName => $roleData) {
                    $role = Role::create([
                        'name' => $roleName,
                        'display_name' => $roleData['display_name'],
                        'description' => $roleData['description'],
                        'position_id' => $position->id,
                        'is_active' => true
                    ]);

                    // Assign permissions to role
                    if (isset($roleData['permissions'])) {
                        $permissionModels = Permission::whereIn('name', $roleData['permissions'])->get();
                        $role->permissions()->sync($permissionModels->pluck('id'));
                    }

                    echo "    Created role: {$roleData['display_name']}\n";
                }
            }
        }

        // Ensure core users have correct primary roles
        $this->assignCoreUserRoles();

        echo "\nHierarchical data seeded successfully!\n";
        echo "Structure: Department -> Position -> Role\n";
    }

    /**
     * Ensure important system users (like admin and manager) have the right roles.
     */
    protected function assignCoreUserRoles(): void
    {
        // Admin user
        $adminUser = User::where('email', 'admin@hotel.com')->first();
        if ($adminUser) {
            $adminUser->syncRoles(['admin']);
            echo "Assigned 'admin' role to admin@hotel.com\n";
        }

        // Manager user
        $managerUser = User::where('email', 'manager@hotel.com')->first();
        if ($managerUser) {
            $managerUser->syncRoles(['manager']);
            echo "Assigned 'manager' role to manager@hotel.com\n";
        }

        // Accountant user
        $accountant = User::where('email', 'accountant@hotel.com')->first();
        if ($accountant) {
            $accountant->syncRoles(['accountant']);
            echo "Assigned 'accountant' role to accountant@hotel.com\n";
        }

        // Front desk user
        $frontDesk = User::where('email', 'frontdesk@hotel.com')->first();
        if ($frontDesk) {
            $frontDesk->syncRoles(['front_desk']);
            echo "Assigned 'front_desk' role to frontdesk@hotel.com\n";
        }

        // Housekeeping / staff user
        $housekeeping = User::where('email', 'staff@hotel.com')->first();
        if ($housekeeping) {
            $housekeeping->syncRoles(['housekeeping']);
            echo "Assigned 'housekeeping' role to staff@hotel.com\n";
        }

        // Bartender user
        $bartender = User::where('email', 'bartender@hotel.com')->first();
        if ($bartender) {
            $bartender->syncRoles(['bartender']);
            echo "Assigned 'bartender' role to bartender@hotel.com\n";
        }

        // Chef user
        $chef = User::where('email', 'chef@hotel.com')->first();
        if ($chef) {
            $chef->syncRoles(['chef']);
            echo "Assigned 'chef' role to chef@hotel.com\n";
        }

        // Server user
        $server = User::where('email', 'server@hotel.com')->first();
        if ($server) {
            $server->syncRoles(['server']);
            echo "Assigned 'server' role to server@hotel.com\n";
        }
    }
}
