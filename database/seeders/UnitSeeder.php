<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Pieces', 'code' => 'PCS', 'description' => 'Individual pieces', 'is_active' => true],
            ['name' => 'Kilograms', 'code' => 'KG', 'description' => 'Weight in kilograms', 'is_active' => true],
            ['name' => 'Liters', 'code' => 'L', 'description' => 'Volume in liters', 'is_active' => true],
            ['name' => 'Meters', 'code' => 'M', 'description' => 'Length in meters', 'is_active' => true],
            ['name' => 'Boxes', 'code' => 'BOX', 'description' => 'Packaged boxes', 'is_active' => true],
            ['name' => 'Bottles', 'code' => 'BOT', 'description' => 'Bottled items', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            \App\Models\Unit::create($unit);
        }
    }
}
