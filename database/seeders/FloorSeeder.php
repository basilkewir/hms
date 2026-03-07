<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $floors = [
            ['floor_number' => 0, 'name' => 'Ground Floor', 'description' => 'Ground level floor', 'is_active' => true, 'sort_order' => 0],
            ['floor_number' => 1, 'name' => 'First Floor', 'description' => 'First level above ground', 'is_active' => true, 'sort_order' => 1],
            ['floor_number' => 2, 'name' => 'Second Floor', 'description' => 'Second level', 'is_active' => true, 'sort_order' => 2],
            ['floor_number' => 3, 'name' => 'Third Floor', 'description' => 'Third level', 'is_active' => true, 'sort_order' => 3],
            ['floor_number' => 4, 'name' => 'Fourth Floor', 'description' => 'Fourth level', 'is_active' => true, 'sort_order' => 4],
            ['floor_number' => 5, 'name' => 'Fifth Floor', 'description' => 'Fifth level', 'is_active' => true, 'sort_order' => 5],
            ['floor_number' => 6, 'name' => 'Sixth Floor', 'description' => 'Sixth level', 'is_active' => true, 'sort_order' => 6],
            ['floor_number' => 7, 'name' => 'Seventh Floor', 'description' => 'Seventh level', 'is_active' => true, 'sort_order' => 7],
            ['floor_number' => 8, 'name' => 'Eighth Floor', 'description' => 'Eighth level', 'is_active' => true, 'sort_order' => 8],
            ['floor_number' => 9, 'name' => 'Ninth Floor', 'description' => 'Ninth level', 'is_active' => true, 'sort_order' => 9],
            ['floor_number' => 10, 'name' => 'Tenth Floor', 'description' => 'Tenth level', 'is_active' => true, 'sort_order' => 10],
        ];

        foreach ($floors as $floor) {
            \App\Models\Floor::updateOrCreate(
                ['floor_number' => $floor['floor_number']],
                $floor
            );
        }
    }
}
