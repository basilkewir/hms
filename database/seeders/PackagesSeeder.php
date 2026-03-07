<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackagesSeeder extends Seeder
{
    public function run()
    {
        $packages = [
            [
                'name' => 'Basic Conference Package',
                'code' => 'BCP-001',
                'description' => 'Essential conference package with basic amenities',
                'price' => 2000.00,
                'included_features' => [
                    'room_rental' => true,
                    'basic_audio_visual' => true,
                    'notepads_and_pens' => true,
                    'water_bottles' => true,
                    'coffee_break' => false,
                    'lunch' => false,
                    'wifi' => true
                ],
                'optional_features' => [
                    'coffee_break' => 15.00,
                    'lunch' => 25.00,
                    'extended_hours' => 500.00,
                    'premium_audio_visual' => 300.00
                ],
                'is_active' => true
            ],
            [
                'name' => 'Premium Event Package',
                'code' => 'PEP-001',
                'description' => 'Comprehensive event package with premium services',
                'price' => 8000.00,
                'included_features' => [
                    'room_rental' => true,
                    'premium_audio_visual' => true,
                    'decor_setup' => true,
                    'catering' => true,
                    'dedicated_staff' => true,
                    'parking' => true,
                    'wifi' => true
                ],
                'optional_features' => [
                    'extended_hours' => 1000.00,
                    'live_streaming' => 500.00,
                    'photography' => 800.00,
                    'custom_decor' => 1500.00
                ],
                'is_active' => true
            ],
            [
                'name' => 'Wedding Package',
                'code' => 'WP-001',
                'description' => 'Complete wedding package with all essentials',
                'price' => 12000.00,
                'included_features' => [
                    'venue_rental' => true,
                    'ceremony_setup' => true,
                    'reception_setup' => true,
                    'basic_decor' => true,
                    'catering' => true,
                    'wedding_planner' => true,
                    'bridal_suite' => true,
                    'music_system' => true
                ],
                'optional_features' => [
                    'premium_decor' => 3000.00,
                    'live_band' => 2000.00,
                    'photography' => 2500.00,
                    'extended_hours' => 2000.00
                ],
                'is_active' => true
            ],
            [
                'name' => 'Corporate Meeting Package',
                'code' => 'CMP-001',
                'description' => 'Professional meeting package for corporate events',
                'price' => 3500.00,
                'included_features' => [
                    'meeting_room' => true,
                    'projector' => true,
                    'whiteboard' => true,
                    'notepads_and_pens' => true,
                    'water_bottles' => true,
                    'coffee_break' => true,
                    'wifi' => true
                ],
                'optional_features' => [
                    'catering' => 20.00,
                    'extended_hours' => 800.00,
                    'video_conferencing' => 500.00,
                    'dedicated_assistant' => 300.00
                ],
                'is_active' => true
            ],
            [
                'name' => 'Birthday Celebration Package',
                'code' => 'BCP-002',
                'description' => 'Fun birthday celebration package',
                'price' => 4500.00,
                'included_features' => [
                    'party_room' => true,
                    'basic_decor' => true,
                    'music_system' => true,
                    'cake_cutting_area' => true,
                    'basic_catering' => true,
                    'party_host' => true,
                    'photography' => true
                ],
                'optional_features' => [
                    'themed_decor' => 1000.00,
                    'live_entertainment' => 1500.00,
                    'extended_hours' => 1000.00,
                    'custom_cake' => 500.00
                ],
                'is_active' => true
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
