<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuildingWingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wings = [
            ['name' => 'Main Building', 'code' => 'MAIN', 'description' => 'Main building structure', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'North Wing', 'code' => 'NORTH', 'description' => 'North wing of the building', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'South Wing', 'code' => 'SOUTH', 'description' => 'South wing of the building', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'East Wing', 'code' => 'EAST', 'description' => 'East wing of the building', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'West Wing', 'code' => 'WEST', 'description' => 'West wing of the building', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($wings as $wing) {
            \App\Models\BuildingWing::updateOrCreate(
                ['code' => $wing['code']],
                $wing
            );
        }
    }
}
