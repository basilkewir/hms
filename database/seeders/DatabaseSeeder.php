<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\WorkShift;
use App\Models\ExpenseCategory;
use App\Models\IptvChannel;
use App\Models\IptvPackage;
use App\Models\HotelService;
use App\Models\BreakfastMenu;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create permissions first
        $this->createPermissions();

        // Create roles with permissions
        $this->createRoles();

        // Seed budget permissions
        $this->call(BudgetPermissionsSeeder::class);

        // Seed sample budget data
        $this->call(BudgetsSeeder::class);

        // Create default users
        $this->createUsers();

        // Seed sample budget expenses (depends on budgets + users)
        $this->call(BudgetExpensesSeeder::class);

        // Seed floors, building wings, bed types, room types, and guest types FIRST
        // (These need to exist before creating rooms and guests)
        $this->call(FloorSeeder::class);
        $this->call(BuildingWingSeeder::class);
        $this->call(BedTypeSeeder::class);
        $this->call(RoomTypeSeeder::class);
        $this->call(GuestTypeSeeder::class);
        $this->call(KeyCardSeeder::class);
        $this->call(HotelServiceSeeder::class);

        // Create room types and rooms (after floors/wings/bed types are seeded)
        $this->createRoomsAndTypes();

        // Create work shifts
        $this->createWorkShifts();

        // Create expense categories
        $this->createExpenseCategories();

        // Create IPTV channels and packages
        $this->createIptvData();

        // Create hotel services and breakfast menus
        $this->createServicesAndBreakfast();

        // Create sample guests and reservations
        $this->createSampleReservations();

        // Seed POS data
        $this->call(POSSeeder::class);
        $this->call(POSExtendedSeeder::class);

        // Seed Front Desk Services data (Housekeeping, Maintenance, Concierge)
        $this->call(ServicesSeeder::class);

        // Seed OTA Reservations for Channel Manager testing
        $this->call(OTAReservationsSeeder::class);

        echo "Database seeded successfully!\n";
        echo "Default login credentials:\n";
        echo "Admin: admin@hotel.com / password\n";
        echo "Manager: manager@hotel.com / password\n";
        echo "Accountant: accountant@hotel.com / password\n";
        echo "Front Desk: frontdesk@hotel.com / password\n";
        echo "Staff: staff@hotel.com / password\n";
        echo "Bartender: bartender@hotel.com / password\n";
        echo "Chef: chef@hotel.com / password\n";
        echo "Server: server@hotel.com / password\n";
    }

    private function createPermissions()
    {
        $permissions = Permission::getDefaultPermissions();

        foreach ($permissions as $name => $data) {
            Permission::firstOrCreate(
                ['name' => $name],
                $data
            );
        }

        echo "Created " . count($permissions) . " permissions\n";
    }

    private function createRoles()
    {
        $roles = Role::getDefaultRoles();

        foreach ($roles as $name => $data) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );

            // Assign permissions to role
            $permissions = Permission::whereIn('name', $data['permissions'])->get();
            $role->permissions()->sync($permissions->pluck('id'));
        }

        echo "Created " . count($roles) . " roles\n";
    }

    private function createUsers()
    {
        $users = [
            [
                'employee_id' => 'EMP001',
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
                'employee_id' => 'EMP002',
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
                'employee_id' => 'EMP003',
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
                'employee_id' => 'EMP004',
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
                'employee_id' => 'EMP005',
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
                'employee_id' => 'EMP006',
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
                'employee_id' => 'EMP007',
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
                'employee_id' => 'EMP008',
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
            ]
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }

        echo "Created " . count($users) . " users\n";
    }

    private function createRoomsAndTypes()
    {
        $roomTypes = [
            [
                'name' => 'Standard Room',
                'code' => 'STD',
                'description' => 'Comfortable standard room with basic amenities',
                'max_occupancy' => 2,
                'max_adults' => 2,
                'max_children' => 1,
                'base_price' => 99.99,
                'extra_adult_charge' => 25.00,
                'extra_child_charge' => 15.00,
                'amenities' => ['WiFi', 'AC', 'TV', 'Private Bathroom'],
                'iptv_package' => 'basic',
                'room_size_sqft' => 250,
                'bed_type' => 'Queen',
                'bed_count' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe Room',
                'code' => 'DLX',
                'description' => 'Spacious deluxe room with premium amenities',
                'max_occupancy' => 3,
                'max_adults' => 2,
                'max_children' => 2,
                'base_price' => 149.99,
                'extra_adult_charge' => 30.00,
                'extra_child_charge' => 20.00,
                'amenities' => ['WiFi', 'AC', 'Smart TV', 'Private Bathroom', 'Mini Fridge', 'Coffee Maker'],
                'iptv_package' => 'premium',
                'room_size_sqft' => 350,
                'bed_type' => 'King',
                'bed_count' => 1,
                'has_balcony' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Suite',
                'code' => 'STE',
                'description' => 'Luxury suite with separate living area',
                'max_occupancy' => 4,
                'max_adults' => 3,
                'max_children' => 2,
                'base_price' => 299.99,
                'extra_adult_charge' => 50.00,
                'extra_child_charge' => 25.00,
                'amenities' => ['WiFi', 'AC', 'Smart TV', 'Private Bathroom', 'Mini Fridge', 'Coffee Maker', 'Sofa', 'Work Desk'],
                'iptv_package' => 'vip',
                'room_size_sqft' => 600,
                'bed_type' => 'King',
                'bed_count' => 1,
                'has_balcony' => true,
                'has_living_room' => true,
                'view_type' => 'City',
                'is_active' => true,
            ]
        ];

        foreach ($roomTypes as $typeData) {
            RoomType::firstOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        // Create rooms
        $roomTypes = RoomType::all();
        $roomNumber = 101;

        // Check if new columns exist (after migration)
        $hasFloorId = Schema::hasColumn('rooms', 'floor_id');
        $hasBuildingWingId = Schema::hasColumn('rooms', 'building_wing_id');
        $hasBedTypeId = Schema::hasColumn('rooms', 'bed_type_id');

        // Get floors if the new structure exists
        $floors = [];
        if ($hasFloorId) {
            $floors = \App\Models\Floor::all()->keyBy('floor_number');
        }

        // Get default building wing if exists
        $defaultWing = null;
        if ($hasBuildingWingId) {
            $defaultWing = \App\Models\BuildingWing::where('code', 'MAIN')->first();
        }

        foreach ($roomTypes as $roomType) {
            for ($i = 1; $i <= 10; $i++) {
                $floorNumber = (int)($roomNumber / 100);
                $roomData = [
                    'room_type_id' => $roomType->id,
                    'status' => 'available',
                    'iptv_active' => true,
                    'housekeeping_status' => 'clean',
                    'is_active' => true,
                ];

                // Use new structure if columns exist
                if ($hasFloorId && isset($floors[$floorNumber])) {
                    $roomData['floor_id'] = $floors[$floorNumber]->id;
                } elseif (!$hasFloorId) {
                    // Fallback to old structure if migration hasn't run
                    $roomData['floor'] = $floorNumber;
                }

                if ($hasBuildingWingId && $defaultWing) {
                    $roomData['building_wing_id'] = $defaultWing->id;
                }

                Room::firstOrCreate(
                    ['room_number' => $roomNumber],
                    $roomData
                );
                $roomNumber++;
            }
        }

        echo "Created room types and 30 rooms\n";
    }

    private function createWorkShifts()
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'hours' => 8,
                'break_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'hours' => 8,
                'break_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'hours' => 8,
                'break_minutes' => 30,
                'is_overnight' => true,
                'is_active' => true,
            ]
        ];

        foreach ($shifts as $shiftData) {
            WorkShift::firstOrCreate(
                ['name' => $shiftData['name']],
                $shiftData
            );
        }

        echo "Created 3 work shifts\n";
    }

    private function createExpenseCategories()
    {
        $categories = [
            ['name' => 'Utilities', 'code' => 'UTIL', 'description' => 'Electricity, water, gas, internet'],
            ['name' => 'Maintenance', 'code' => 'MAINT', 'description' => 'Room and facility maintenance'],
            ['name' => 'Supplies', 'code' => 'SUPP', 'description' => 'Cleaning supplies, amenities, office supplies'],
            ['name' => 'Food & Beverage', 'code' => 'FB', 'description' => 'Restaurant and bar supplies'],
            ['name' => 'Marketing', 'code' => 'MARK', 'description' => 'Advertising and promotional expenses'],
            ['name' => 'Insurance', 'code' => 'INS', 'description' => 'Property and liability insurance'],
            ['name' => 'Technology', 'code' => 'TECH', 'description' => 'Software, hardware, IPTV systems'],
        ];

        foreach ($categories as $categoryData) {
            ExpenseCategory::firstOrCreate(
                ['code' => $categoryData['code']],
                $categoryData
            );
        }

        echo "Created expense categories\n";
    }

    private function createIptvData()
    {
        // Create IPTV packages (updated for Xtream Codes)
        $packages = [
            [
                'name' => 'Basic Package',
                'code' => 'basic',
                'description' => 'Essential channels for all guests',
                'monthly_price' => 0,
                'includes_adult_content' => false,
                'includes_premium_channels' => false,
                'includes_international_channels' => false,
                'xtream_categories' => [1, 2, 3], // News, Entertainment, Sports category IDs
                'xtream_channel_groups' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Package',
                'code' => 'premium',
                'description' => 'Extended channel selection with premium content',
                'monthly_price' => 15.99,
                'includes_adult_content' => false,
                'includes_premium_channels' => true,
                'includes_international_channels' => true,
                'xtream_categories' => [1, 2, 3, 4, 5], // News, Entertainment, Sports, Movies, International
                'xtream_channel_groups' => null,
                'is_active' => true,
            ],
            [
                'name' => 'VIP Package',
                'code' => 'vip',
                'description' => 'Complete channel package with all content',
                'monthly_price' => 29.99,
                'includes_adult_content' => true,
                'includes_premium_channels' => true,
                'includes_international_channels' => true,
                'xtream_categories' => null, // All categories allowed
                'xtream_channel_groups' => null,
                'is_active' => true,
            ]
        ];

        foreach ($packages as $packageData) {
            IptvPackage::firstOrCreate(
                ['code' => $packageData['code']],
                $packageData
            );
        }

        echo "Created IPTV packages and sample channels\n";
    }

    private function createServicesAndBreakfast()
    {
        // Create Hotel Services
        $services = [
            [
                'name' => 'Airport Transfer',
                'category' => 'transport',
                'description' => 'Private airport pickup and drop-off service',
                'price' => 50.00,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 24,
                'icon' => 'car',
                'sort_order' => 1,
            ],
            [
                'name' => 'Laundry Service',
                'category' => 'laundry',
                'description' => 'Professional laundry and dry cleaning',
                'price' => 15.00,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => false,
                'icon' => 'washing-machine',
                'sort_order' => 2,
            ],
            [
                'name' => 'Spa Treatment',
                'category' => 'spa',
                'description' => 'Relaxing massage and spa services',
                'price' => 80.00,
                'pricing_type' => 'per_person',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 12,
                'icon' => 'spa',
                'sort_order' => 3,
            ],
            [
                'name' => 'Extra Bed',
                'category' => 'room',
                'description' => 'Additional bed in room',
                'price' => 25.00,
                'pricing_type' => 'per_night',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 24,
                'icon' => 'bed',
                'sort_order' => 4,
            ],
            [
                'name' => 'Late Checkout',
                'category' => 'room',
                'description' => 'Checkout after 12:00 PM (until 6:00 PM)',
                'price' => 30.00,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => false,
                'icon' => 'clock',
                'sort_order' => 5,
            ],
        ];

        foreach ($services as $serviceData) {
            HotelService::firstOrCreate(
                ['name' => $serviceData['name']],
                $serviceData
            );
        }

        // Create Breakfast Menus
        $breakfastMenus = [
            [
                'name' => 'Continental Breakfast',
                'type' => 'continental',
                'description' => 'Light breakfast with pastries, fruits, and beverages',
                'price' => 12.00,
                'items' => [
                    'Fresh croissants and pastries',
                    'Seasonal fruits',
                    'Yogurt and cereals',
                    'Coffee, tea, and juice',
                ],
                'serving_time_start' => '07:00',
                'serving_time_end' => '10:00',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'American Breakfast',
                'type' => 'american',
                'description' => 'Hearty breakfast with eggs, bacon, and more',
                'price' => 18.00,
                'items' => [
                    'Eggs (scrambled, fried, or omelet)',
                    'Bacon and sausages',
                    'Hash browns',
                    'Toast and butter',
                    'Coffee, tea, and juice',
                ],
                'serving_time_start' => '07:00',
                'serving_time_end' => '10:30',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Breakfast Buffet',
                'type' => 'buffet',
                'description' => 'All-you-can-eat breakfast buffet with international cuisine',
                'price' => 25.00,
                'items' => [
                    'Hot dishes station',
                    'Cold cuts and cheese',
                    'Fresh salads',
                    'Pastries and bread',
                    'Fruits and desserts',
                    'Unlimited beverages',
                ],
                'serving_time_start' => '06:30',
                'serving_time_end' => '11:00',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Healthy Start',
                'type' => 'a_la_carte',
                'description' => 'Nutritious breakfast for health-conscious guests',
                'price' => 15.00,
                'items' => [
                    'Fresh fruit bowl',
                    'Greek yogurt with granola',
                    'Whole grain toast',
                    'Avocado',
                    'Green smoothie',
                ],
                'serving_time_start' => '07:00',
                'serving_time_end' => '10:00',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($breakfastMenus as $menuData) {
            BreakfastMenu::firstOrCreate(
                ['name' => $menuData['name']],
                $menuData
            );
        }

        echo "Created hotel services and breakfast menus\n";
    }

    private function createSampleReservations()
    {
        $guests = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1234567890',
                'nationality' => 'USA',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane.doe@example.com',
                'phone' => '+1234567891',
                'nationality' => 'UK',
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Wilson',
                'email' => 'bob.wilson@example.com',
                'phone' => '+1234567892',
                'nationality' => 'Canada',
            ],
        ];

        $rooms = Room::take(3)->get();
        $roomTypes = RoomType::all();

        // Helper function to generate unique reservation number
        $generateReservationNumber = function() {
            $year = date('Y');
            $lastReservation = \App\Models\Reservation::where('reservation_number', 'like', 'RES' . $year . '%')
                ->orderBy('reservation_number', 'desc')
                ->first();

            if ($lastReservation) {
                $lastNumber = (int) substr($lastReservation->reservation_number, -6);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return 'RES' . $year . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        };

        foreach ($guests as $index => $guestData) {
            // Check if guest already exists
            $guest = \App\Models\Guest::where('email', $guestData['email'])->first();

            if (!$guest) {
                $guestData['created_by'] = 1; // Admin user
                $guest = \App\Models\Guest::create($guestData);
            }

            if (isset($rooms[$index])) {
                $room = $rooms[$index];
                $roomType = $roomTypes->find($room->room_type_id);

                // Check if reservation already exists for this guest and room
                $existingReservation = \App\Models\Reservation::where('guest_id', $guest->id)
                    ->where('room_id', $room->id)
                    ->where('status', 'checked_in')
                    ->first();

                if (!$existingReservation) {
                    $reservationNumber = $generateReservationNumber();

                    // Ensure reservation number is unique
                    while (\App\Models\Reservation::where('reservation_number', $reservationNumber)->exists()) {
                        $reservationNumber = $generateReservationNumber();
                    }

                    \App\Models\Reservation::create([
                        'reservation_number' => $reservationNumber,
                        'guest_id' => $guest->id,
                        'room_id' => $room->id,
                        'room_type_id' => $room->room_type_id,
                        'check_in_date' => now()->subDays(1),
                        'check_out_date' => now()->addDays(2),
                        'nights' => 3,
                        'adults' => 2,
                        'children' => 0,
                        'status' => 'checked_in',
                        'room_rate' => $roomType->base_price,
                        'total_room_charges' => $roomType->base_price * 3,
                        'taxes' => 0,
                        'service_charges' => 0,
                        'total_amount' => $roomType->base_price * 3,
                        'paid_amount' => 0,
                        'balance_amount' => $roomType->base_price * 3,
                        'actual_check_in' => now()->subDays(1),
                        'created_by' => 1,
                    ]);

                    $room->update(['status' => 'occupied']);
                }
            }
        }

        echo "Created/verified 3 sample guests with checked-in reservations\n";
    }
}
