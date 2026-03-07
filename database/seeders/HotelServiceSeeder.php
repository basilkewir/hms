<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HotelService;

class HotelServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            // Laundry Services
            [
                'name' => 'Laundry Service - Standard',
                'category' => 'laundry',
                'description' => 'Standard laundry service (wash, dry, fold)',
                'price' => 15.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Laundry Service - Express',
                'category' => 'laundry',
                'description' => 'Express laundry service (same day)',
                'price' => 25.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Dry Cleaning',
                'category' => 'laundry',
                'description' => 'Professional dry cleaning service',
                'price' => 20.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 3,
            ],

            // Car Wash Services
            [
                'name' => 'Car Wash - Basic',
                'category' => 'car_wash',
                'description' => 'Basic exterior car wash',
                'price' => 20.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Car Wash - Premium',
                'category' => 'car_wash',
                'description' => 'Premium car wash with interior cleaning',
                'price' => 40.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Car Wash - Full Detail',
                'category' => 'car_wash',
                'description' => 'Full car detailing service',
                'price' => 80.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 12,
            ],

            // Spa Services
            [
                'name' => 'Spa Treatment - Massage',
                'category' => 'spa',
                'description' => 'Relaxing massage therapy',
                'price' => 80.00,
                'is_free' => false,
                'pricing_type' => 'per_person',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 12,
                'sort_order' => 20,
            ],
            [
                'name' => 'Spa Treatment - Facial',
                'category' => 'spa',
                'description' => 'Rejuvenating facial treatment',
                'price' => 60.00,
                'is_free' => false,
                'pricing_type' => 'per_person',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 12,
                'sort_order' => 21,
            ],

            // Transport Services
            [
                'name' => 'Airport Transfer',
                'category' => 'transport',
                'description' => 'Airport pickup or drop-off service',
                'price' => 50.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 24,
                'sort_order' => 30,
            ],
            [
                'name' => 'City Tour',
                'category' => 'transport',
                'description' => 'Guided city tour',
                'price' => 75.00,
                'is_free' => false,
                'pricing_type' => 'per_person',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 24,
                'sort_order' => 31,
            ],

            // Room Services
            [
                'name' => 'Extra Bed',
                'category' => 'room',
                'description' => 'Additional bed in room',
                'price' => 25.00,
                'is_free' => false,
                'pricing_type' => 'per_night',
                'is_active' => true,
                'available_online' => true,
                'requires_advance_booking' => true,
                'advance_hours' => 24,
                'sort_order' => 40,
            ],
            [
                'name' => 'Late Checkout',
                'category' => 'room',
                'description' => 'Extended checkout time (until 2 PM)',
                'price' => 30.00,
                'is_free' => false,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => false,
                'sort_order' => 41,
            ],

            // Free Services
            [
                'name' => 'WiFi Access',
                'category' => 'other',
                'description' => 'Complimentary WiFi internet access',
                'price' => 0,
                'is_free' => true,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => true,
                'sort_order' => 50,
            ],
            [
                'name' => 'Newspaper Delivery',
                'category' => 'other',
                'description' => 'Daily newspaper delivery to room',
                'price' => 0,
                'is_free' => true,
                'pricing_type' => 'per_service',
                'is_active' => true,
                'available_online' => false,
                'sort_order' => 51,
            ],
        ];

        foreach ($services as $service) {
            HotelService::create($service);
        }

        $this->command->info('Created ' . count($services) . ' hotel services');
    }
}
