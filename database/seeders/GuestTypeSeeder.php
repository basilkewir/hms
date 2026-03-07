<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GuestType;

class GuestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guestTypes = [
            [
                'name' => 'Regular',
                'code' => 'REG',
                'description' => 'Standard guest type for regular bookings',
                'color' => '#3B82F6', // Blue
                'discount_percentage' => 0,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'VIP',
                'code' => 'VIP',
                'description' => 'Very Important Person - Premium service and amenities',
                'color' => '#F59E0B', // Amber/Gold
                'discount_percentage' => 0,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Corporate',
                'code' => 'CORP',
                'description' => 'Corporate clients and business travelers',
                'color' => '#10B981', // Green
                'discount_percentage' => 10,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Group',
                'code' => 'GRP',
                'description' => 'Group bookings and tour groups',
                'color' => '#8B5CF6', // Purple
                'discount_percentage' => 15,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Family',
                'code' => 'FAM',
                'description' => 'Family bookings with children',
                'color' => '#EC4899', // Pink
                'discount_percentage' => 5,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Long-term',
                'code' => 'LNG',
                'description' => 'Extended stay guests (30+ days)',
                'color' => '#06B6D4', // Cyan
                'discount_percentage' => 20,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Government',
                'code' => 'GOV',
                'description' => 'Government officials and employees',
                'color' => '#6366F1', // Indigo
                'discount_percentage' => 10,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Diplomatic',
                'code' => 'DIP',
                'description' => 'Diplomatic personnel and embassy staff',
                'color' => '#EF4444', // Red
                'discount_percentage' => 15,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Travel Agent',
                'code' => 'TA',
                'description' => 'Travel agents and tour operators',
                'color' => '#14B8A6', // Teal
                'discount_percentage' => 25,
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Loyalty Member',
                'code' => 'LOY',
                'description' => 'Loyalty program members',
                'color' => '#F97316', // Orange
                'discount_percentage' => 8,
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($guestTypes as $typeData) {
            GuestType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        $this->command->info('Created ' . count($guestTypes) . ' guest types');
    }
}
