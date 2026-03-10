<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\License;
use Carbon\Carbon;

class LicenseSeeder extends Seeder
{
    /**
     * Seed the Hotel Donzebe license into the database.
     * This allows the system to work without needing external license validation.
     */
    public function run(): void
    {
        // Create Hotel Donzebe HD license
        License::updateOrCreate(
            ['license_key' => 'E7503BB1-99D9EBED-42568D93-E249B472'],
            [
                'license_type' => 'perpetual',
                'product_name' => 'Hotel Management System',
                'customer_name' => 'Hotel Donzebe HD',
                'customer_email' => 'admin@donzebe.com',
                'organization' => 'Donzebe Hotel',
                'max_devices' => 80,
                'max_rooms' => 100,
                'max_channels' => 500,
                'vod_enabled' => true,
                'premium_features' => true,
                'allowed_features' => [
                    'iptv_streaming' => true,
                    'vod_management' => true,
                    'room_management' => true,
                    'guest_management' => true,
                    'reservation_system' => true,
                    'billing_system' => true,
                    'staff_management' => true,
                    'reporting_analytics' => true,
                    'api_access' => true,
                    'unlimited_users' => true,
                ],
                'issued_at' => Carbon::now(),
                'expires_at' => null, // PERPETUAL - never expires
                'activated_at' => Carbon::now(),
                'last_validated_at' => Carbon::now(),
                'status' => 'active',
                'activation_count' => 1,
                'max_activations' => 1,
                'notes' => 'Hotel Donzebe HD - Perpetual License (Seeded)',
                'license_data' => [
                    'license_key' => 'E7503BB1-99D9EBED-42568D93-E249B472',
                    'hotel_name' => 'Hotel Donzebe HD',
                    'license_type' => 'PERPETUAL',
                    'status' => 'ACTIVE',
                    'expires_at' => 'Never Expires',
                    'created_at' => Carbon::now()->toDateString(),
                    'features' => [
                        'tv_streaming' => 0,
                        'smart_devices' => 0,
                        'api_access' => 0,
                    ],
                    'device_allocation' => [
                        'tv' => ['current' => 0, 'maximum' => 80],
                        'smart' => ['current' => 0, 'maximum' => 80],
                        'api' => ['current' => 0, 'maximum' => 1],
                    ],
                    'total_used' => 0,
                    'total_limit' => 161, // 80 + 80 + 1
                    'validated_at' => Carbon::now()->toIso8601String(),
                    'is_valid' => true,
                ],
            ]
        );

        $this->command->info('✓ Hotel Donzebe HD license seeded successfully!');
    }
}
