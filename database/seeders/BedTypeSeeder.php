<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BedTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bedTypes = [
            ['name' => 'Single', 'code' => 'SGL', 'description' => 'Single bed (39" x 75")', 'width_inches' => 39, 'length_inches' => 75, 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Double', 'code' => 'DBL', 'description' => 'Double bed (54" x 75")', 'width_inches' => 54, 'length_inches' => 75, 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Twin', 'code' => 'TWN', 'description' => 'Twin bed (39" x 75")', 'width_inches' => 39, 'length_inches' => 75, 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Queen', 'code' => 'QN', 'description' => 'Queen bed (60" x 80")', 'width_inches' => 60, 'length_inches' => 80, 'is_active' => true, 'sort_order' => 4],
            ['name' => 'King', 'code' => 'KG', 'description' => 'King bed (76" x 80")', 'width_inches' => 76, 'length_inches' => 80, 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($bedTypes as $bedType) {
            \App\Models\BedType::updateOrCreate(
                ['code' => $bedType['code']],
                $bedType
            );
        }
    }
}
