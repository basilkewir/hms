<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Generic', 'code' => 'GEN', 'description' => 'Generic brand products', 'is_active' => true],
            ['name' => 'Premium', 'code' => 'PRE', 'description' => 'Premium quality products', 'is_active' => true],
            ['name' => 'Economy', 'code' => 'ECO', 'description' => 'Economy budget products', 'is_active' => true],
            ['name' => 'Organic', 'code' => 'ORG', 'description' => 'Organic and natural products', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            \App\Models\Brand::create($brand);
        }
    }
}
