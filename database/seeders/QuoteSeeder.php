<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user to assign as creator
        $user = User::first() ?? User::factory()->create();

        // Get some reservations if they exist, otherwise just create guest quotes
        $reservations = Reservation::limit(2)->get();

        // Create guest quotes from reservations
        $reservations->each(function ($reservation) use ($user) {
            $quote = Quote::create([
                'quote_number' => Quote::generateQuoteNumber(),
                'quote_type' => 'guest',
                'reservation_id' => $reservation->id,
                'customer_name' => $reservation->guest ? $reservation->guest->first_name . ' ' . $reservation->guest->last_name : 'Guest ' . $reservation->id,
                'customer_email' => $reservation->guest?->email ?? 'guest' . $reservation->id . '@hotel.local',
                'customer_phone' => $reservation->guest?->phone ?? '+1234567890',
                'total_amount' => rand(500, 5000),
                'valid_until' => now()->addDays(rand(7, 30)),
                'status' => collect(['draft', 'sent', 'accepted'])->random(),
                'notes' => 'Quote for room reservation',
                'created_by' => $user->id,
                'issue_date' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Add items to quote
            for ($i = 0; $i < rand(1, 3); $i++) {
                $quote->items()->create([
                    'description' => collect([
                        'Room Charges',
                        'Additional Services',
                        'Food & Beverage',
                        'Laundry Service',
                        'Room Service',
                    ])->random(),
                    'quantity' => rand(1, 5),
                    'unit_price' => rand(50, 500),
                ]);
            }
        });

        // Create outsider quotes (non-guest quotes)
        $outsiderNames = ['ABC Corporation', 'XYZ Limited', 'Tech Solutions Inc', 'Global Enterprises', 'Innovation Labs'];

        collect($outsiderNames)->each(function ($name) use ($user) {
            $quote = Quote::create([
                'quote_number' => Quote::generateQuoteNumber(),
                'quote_type' => 'outsider',
                'reservation_id' => null,
                'customer_name' => $name,
                'customer_email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'customer_phone' => '+' . rand(10000000000, 99999999999),
                'total_amount' => rand(1000, 10000),
                'valid_until' => now()->addDays(rand(7, 30)),
                'status' => collect(['draft', 'sent', 'accepted', 'rejected', 'expired'])->random(),
                'notes' => 'Quote for ' . $name,
                'created_by' => $user->id,
                'issue_date' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Add items to quote
            for ($i = 0; $i < rand(1, 4); $i++) {
                $quote->items()->create([
                    'description' => collect([
                        'Venue Rental',
                        'Catering Services',
                        'Event Equipment',
                        'Staff Services',
                        'Technical Support',
                        'Security Services',
                    ])->random(),
                    'quantity' => rand(1, 10),
                    'unit_price' => rand(100, 1000),
                ]);
            }
        });
    }
}
