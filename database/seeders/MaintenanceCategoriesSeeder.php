<?php

namespace Database\Seeders;

use App\Models\MaintenanceCategory;
use Illuminate\Database\Seeder;

class MaintenanceCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = MaintenanceCategory::getDefaultCategories();

        foreach ($categories as $category) {
            MaintenanceCategory::firstOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        echo "Created " . count($categories) . " maintenance categories\n";
    }
}
