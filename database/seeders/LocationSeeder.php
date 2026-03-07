<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Main Kitchen',       'type' => 'kitchen',    'description' => 'Main hotel kitchen'],
            ['name' => 'Restaurant',         'type' => 'restaurant', 'description' => 'Hotel restaurant floor'],
            ['name' => 'Bar & Lounge',       'type' => 'bar',        'description' => 'Hotel bar and lounge area'],
            ['name' => 'Front Desk',         'type' => 'frontdesk',  'description' => 'Reception and front desk'],
            ['name' => 'Housekeeping Store', 'type' => 'other',      'description' => 'Housekeeping and laundry supplies'],
            ['name' => 'Maintenance Store',  'type' => 'other',      'description' => 'Maintenance and engineering supplies'],
            ['name' => 'Main Warehouse',     'type' => 'warehouse',  'description' => 'Central storage warehouse'],
        ];

        foreach ($locations as $data) {
            Location::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
