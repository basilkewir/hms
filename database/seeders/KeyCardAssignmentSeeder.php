<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KeyCardAssignment;
use App\Models\KeyCard;
use App\Models\User;

class KeyCardAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some sample key cards and users
        $keyCards = KeyCard::whereIn('status', ['assigned', 'available'])->limit(10)->get();
        $users = User::whereHas('roles', function($query) {
            $query->where('name', 'front_desk');
        })->limit(5)->get();

        foreach ($keyCards as $keyCard) {
            // Create sample assignment history for assigned cards
            if ($keyCard->status === 'assigned' && $keyCard->guest_id && $keyCard->reservation_id) {
                KeyCardAssignment::create([
                    'key_card_id' => $keyCard->id,
                    'guest_id' => $keyCard->guest_id,
                    'room_id' => $keyCard->room_id,
                    'reservation_id' => $keyCard->reservation_id,
                    'assigned_by' => $users->random()->id,
                    'assigned_at' => $keyCard->issued_at ?? now()->subHours(rand(1, 24)),
                    'action' => 'assigned',
                    'notes' => 'Key card assigned during check-in',
                ]);
            }

            // Create some historical assignments for available cards
            if ($keyCard->status === 'available' && rand(0, 1)) {
                KeyCardAssignment::create([
                    'key_card_id' => $keyCard->id,
                    'guest_id' => $keyCard->guest_id,
                    'room_id' => $keyCard->room_id,
                    'reservation_id' => $keyCard->reservation_id,
                    'assigned_by' => $users->random()->id,
                    'assigned_at' => now()->subDays(rand(1, 7)),
                    'returned_to' => $users->random()->id,
                    'returned_at' => now()->subDays(rand(0, 6)),
                    'action' => 'returned',
                    'notes' => 'Key card returned during check-out',
                ]);
            }
        }
    }
}
