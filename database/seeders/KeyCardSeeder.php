<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KeyCard;

class KeyCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create standard key cards (100 cards)
        for ($i = 1; $i <= 100; $i++) {
            KeyCard::create([
                'card_number' => 'KC' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'card_type' => 'standard',
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        // Create master key cards (5 cards)
        for ($i = 1; $i <= 5; $i++) {
            KeyCard::create([
                'card_number' => 'MASTER' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'card_type' => 'master',
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        // Create staff key cards (10 cards)
        for ($i = 1; $i <= 10; $i++) {
            KeyCard::create([
                'card_number' => 'STAFF' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'card_type' => 'staff',
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        // Create maintenance key cards (3 cards)
        for ($i = 1; $i <= 3; $i++) {
            KeyCard::create([
                'card_number' => 'MAINT' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'card_type' => 'maintenance',
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        $this->command->info('Created 118 key cards (100 standard, 5 master, 10 staff, 3 maintenance)');
    }
}
