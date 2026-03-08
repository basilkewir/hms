<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Hotel::updateOrCreate(
            ['code' => 'KOTEL'],
            [
                'name' => 'Kotel Management System Hotel',
                'legal_name' => 'Kotel Hotel Management Ltd',
                'brand_name' => 'Kotel Hotels',
                'email' => 'info@kotelhotel.com',
                'phone' => '+1234567890',
                'alternate_phone' => '+1234567891',
                'website' => 'https://kotelhotel.com',
                'address_line1' => '123 Hotel Street',
                'address_line2' => 'Building A',
                'city' => 'City',
                'state' => 'State',
                'postal_code' => '12345',
                'country' => 'US',
                'timezone' => 'UTC',
                'currency' => 'USD',
                'settings' => json_encode([
                    'star_rating' => 4,
                    'total_rooms' => 100,
                    'check_in_time' => '14:00:00',
                    'check_out_time' => '11:00:00',
                    'tax_rate' => 10.00,
                    'service_charge_rate' => 5.00,
                ]),
                'is_active' => true,
            ]
        );
    }
}
