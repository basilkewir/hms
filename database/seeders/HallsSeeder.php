<?php

namespace Database\Seeders;

use App\Models\Hall;
use Illuminate\Database\Seeder;

class HallsSeeder extends Seeder
{
    public function run()
    {
        $halls = [
            [
                'name' => 'Grand Ballroom',
                'code' => 'GB-001',
                'description' => 'Our largest ballroom perfect for grand events and weddings',
                'capacity' => 500,
                'base_price' => 5000.00,
                'size' => 'large',
                'type' => 'banquet',
                'is_active' => true
            ],
            [
                'name' => 'Conference Room A',
                'code' => 'CRA-001',
                'description' => 'Modern conference room with AV equipment',
                'capacity' => 50,
                'base_price' => 1500.00,
                'size' => 'medium',
                'type' => 'conference',
                'is_active' => true
            ],
            [
                'name' => 'Meeting Room B',
                'code' => 'MRB-001',
                'description' => 'Intimate meeting room for small groups',
                'capacity' => 15,
                'base_price' => 800.00,
                'size' => 'small',
                'type' => 'meeting',
                'is_active' => true
            ],
            [
                'name' => 'Executive Boardroom',
                'code' => 'EB-001',
                'description' => 'Premium boardroom with executive amenities',
                'capacity' => 20,
                'base_price' => 2000.00,
                'size' => 'small',
                'type' => 'meeting',
                'is_active' => true
            ],
            [
                'name' => 'Garden Terrace',
                'code' => 'GT-001',
                'description' => 'Outdoor terrace with beautiful views',
                'capacity' => 100,
                'base_price' => 3000.00,
                'size' => 'medium',
                'type' => 'banquet',
                'is_active' => true
            ],
        ];

        foreach ($halls as $hall) {
            Hall::create($hall);
        }
    }
}
