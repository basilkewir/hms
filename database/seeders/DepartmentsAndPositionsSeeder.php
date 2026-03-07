<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;

class DepartmentsAndPositionsSeeder extends Seeder
{
    public function run()
    {
        // Create departments
        $management = Department::create([
            'name' => 'Management',
            'description' => 'Hotel management and administration',
            'is_active' => true
        ]);

        $frontDesk = Department::create([
            'name' => 'Front Desk',
            'description' => 'Guest services and reservations',
            'is_active' => true
        ]);

        $housekeeping = Department::create([
            'name' => 'Housekeeping',
            'description' => 'Room cleaning and maintenance',
            'is_active' => true
        ]);

        $maintenance = Department::create([
            'name' => 'Maintenance',
            'description' => 'Hotel maintenance and repairs',
            'is_active' => true
        ]);

        $accounting = Department::create([
            'name' => 'Accounting',
            'description' => 'Financial management and reporting',
            'is_active' => true
        ]);

        $restaurant = Department::create([
            'name' => 'Restaurant',
            'description' => 'Food and beverage services',
            'is_active' => true
        ]);

        $bar = Department::create([
            'name' => 'Bar',
            'description' => 'Bar services and drink preparation',
            'is_active' => true
        ]);

        // Create positions for each department
        $managementPositions = [
            ['name' => 'General Manager', 'description' => 'Overall hotel management'],
            ['name' => 'Assistant Manager', 'description' => 'Assists general manager'],
            ['name' => 'Department Head', 'description' => 'Manages specific departments']
        ];

        $frontDeskPositions = [
            ['name' => 'Front Desk Manager', 'description' => 'Manages front desk operations'],
            ['name' => 'Receptionist', 'description' => 'Handles guest check-in/check-out'],
            ['name' => 'Concierge', 'description' => 'Provides guest services and information']
        ];

        $housekeepingPositions = [
            ['name' => 'Housekeeping Manager', 'description' => 'Manages housekeeping staff'],
            ['name' => 'Room Attendant', 'description' => 'Cleans and maintains guest rooms'],
            ['name' => 'Public Area Attendant', 'description' => 'Maintains common areas']
        ];

        $maintenancePositions = [
            ['name' => 'Maintenance Manager', 'description' => 'Manages maintenance operations'],
            ['name' => 'Maintenance Technician', 'description' => 'Performs repairs and maintenance'],
            ['name' => 'HVAC Specialist', 'description' => 'Handles heating and cooling systems']
        ];

        $accountingPositions = [
            ['name' => 'Financial Controller', 'description' => 'Manages financial operations'],
            ['name' => 'Accountant', 'description' => 'Handles accounting and bookkeeping'],
            ['name' => 'Payroll Specialist', 'description' => 'Manages payroll processing']
        ];

        $restaurantPositions = [
            ['name' => 'Restaurant Manager', 'description' => 'Manages restaurant operations'],
            ['name' => 'Head Chef', 'description' => 'Manages kitchen and food preparation'],
            ['name' => 'Server', 'description' => 'Serves food and beverages to guests'],
            ['name' => 'Host/Hostess', 'description' => 'Greets guests and manages reservations']
        ];

        $barPositions = [
            ['name' => 'Bar Manager', 'description' => 'Manages bar operations'],
            ['name' => 'Head Bartender', 'description' => 'Manages bar staff and inventory'],
            ['name' => 'Bartender', 'description' => 'Prepares and serves drinks'],
            ['name' => 'Barback', 'description' => 'Assists bartenders and maintains bar']
        ];

        // Create positions and associate with departments
        $this->createPositionsForDepartment($management, $managementPositions);
        $this->createPositionsForDepartment($frontDesk, $frontDeskPositions);
        $this->createPositionsForDepartment($housekeeping, $housekeepingPositions);
        $this->createPositionsForDepartment($maintenance, $maintenancePositions);
        $this->createPositionsForDepartment($accounting, $accountingPositions);
        $this->createPositionsForDepartment($restaurant, $restaurantPositions);
        $this->createPositionsForDepartment($bar, $barPositions);

        // Update existing roles with position relationships
        $this->updateRolesWithPositions();
    }

    protected function createPositionsForDepartment($department, $positions)
    {
        foreach ($positions as $positionData) {
            $department->positions()->create([
                'name' => $positionData['name'],
                'description' => $positionData['description'],
                'is_active' => true
            ]);
        }
    }

    protected function updateRolesWithPositions()
    {
        // Get all roles and positions
        $roles = Role::all();
        $positions = Position::all();

        // Map roles to positions based on naming conventions
        foreach ($roles as $role) {
            $position = $positions->where('name', 'like', '%' . ucfirst($role->name) . '%')->first();

            if ($position) {
                $role->update(['position_id' => $position->id]);
            }
        }
    }
}
