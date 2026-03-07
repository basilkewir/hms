<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomAmenity;

class RoomAmenitiesSeeder extends Seeder
{
    public function run()
    {
        $amenities = [
            [
                'name' => 'WiFi',
                'icon' => 'wifi',
                'description' => 'High-speed wireless internet access',
                'is_active' => true,
            ],
            [
                'name' => 'Air Conditioning',
                'icon' => 'air-conditioning',
                'description' => 'Individual climate control',
                'is_active' => true,
            ],
            [
                'name' => 'Television',
                'icon' => 'tv',
                'description' => 'Flat-screen TV with cable channels',
                'is_active' => true,
            ],
            [
                'name' => 'Private Bathroom',
                'icon' => 'bathroom',
                'description' => 'Ensuite bathroom with shower',
                'is_active' => true,
            ],
            [
                'name' => 'Mini Fridge',
                'icon' => 'fridge',
                'description' => 'Small refrigerator for personal use',
                'is_active' => true,
            ],
            [
                'name' => 'Coffee Maker',
                'icon' => 'coffee',
                'description' => 'In-room coffee and tea making facilities',
                'is_active' => true,
            ],
            [
                'name' => 'Safe',
                'icon' => 'safe',
                'description' => 'In-room safe for valuables',
                'is_active' => true,
            ],
            [
                'name' => 'Work Desk',
                'icon' => 'desk',
                'description' => 'Dedicated workspace with chair',
                'is_active' => true,
            ],
            [
                'name' => 'Hair Dryer',
                'icon' => 'hair-dryer',
                'description' => 'Complimentary hair dryer',
                'is_active' => true,
            ],
            [
                'name' => 'Iron',
                'icon' => 'iron',
                'description' => 'Iron and ironing board',
                'is_active' => true,
            ],
        ];

        foreach ($amenities as $amenityData) {
            RoomAmenity::firstOrCreate(
                ['name' => $amenityData['name']],
                $amenityData
            );
        }

        echo "Created " . count($amenities) . " room amenities\n";
    }
}
