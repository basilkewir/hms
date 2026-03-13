<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;

class OTAReservationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates sample OTA (Online Travel Agency) reservations
     * for testing the Channel Manager functionality.
     */
    public function run(): void
    {
        // OTA sources
        $otaSources = ['booking_com', 'expedia', 'agoda', 'airbnb', 'tripadvisor'];

        // Statuses with weights for realistic distribution
        $statuses = ['confirmed', 'pending', 'checked_in', 'cancelled'];

        // Get existing guests or create some
        $guests = Guest::all();
        if ($guests->isEmpty()) {
            // Create sample guests
            $guests = collect([
                Guest::create([
                    'first_name' => 'Jean',
                    'last_name' => 'Dupont',
                    'email' => 'jean.dupont@email.com',
                    'phone' => '+33 6 12 34 56 78',
                    'nationality' => 'French',
                    'id_number' => 'FR12345678',
                ]),
                Guest::create([
                    'first_name' => 'Maria',
                    'last_name' => 'Garcia',
                    'email' => 'maria.garcia@email.com',
                    'phone' => '+34 612 345 678',
                    'nationality' => 'Spanish',
                    'id_number' => 'ES87654321',
                ]),
                Guest::create([
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                    'email' => 'john.smith@email.com',
                    'phone' => '+1 555 123 4567',
                    'nationality' => 'American',
                    'id_number' => 'US123456789',
                ]),
                Guest::create([
                    'first_name' => 'Anna',
                    'last_name' => 'Mueller',
                    'email' => 'anna.mueller@email.com',
                    'phone' => '+49 171 234 5678',
                    'nationality' => 'German',
                    'id_number' => 'DE987654321',
                ]),
                Guest::create([
                    'first_name' => 'Yuki',
                    'last_name' => 'Tanaka',
                    'email' => 'yuki.tanaka@email.com',
                    'phone' => '+81 90 1234 5678',
                    'nationality' => 'Japanese',
                    'id_number' => 'JP1234567890',
                ]),
                Guest::create([
                    'first_name' => 'Ahmed',
                    'last_name' => 'Hassan',
                    'email' => 'ahmed.hassan@email.com',
                    'phone' => '+971 50 123 4567',
                    'nationality' => 'Emirati',
                    'id_number' => 'AE123456789',
                ]),
                Guest::create([
                    'first_name' => 'Sophie',
                    'last_name' => 'Martin',
                    'email' => 'sophie.martin@email.com',
                    'phone' => '+33 6 98 76 54 32',
                    'nationality' => 'French',
                    'id_number' => 'FR98765432',
                ]),
                Guest::create([
                    'first_name' => 'Carlos',
                    'last_name' => 'Rodriguez',
                    'email' => 'carlos.rodriguez@email.com',
                    'phone' => '+34 611 222 333',
                    'nationality' => 'Spanish',
                    'id_number' => 'ES11223344',
                ]),
            ]);
        }

        // Get available rooms
        $rooms = Room::with('roomType')->get();
        if ($rooms->isEmpty()) {
            $this->command->warn('No rooms found. Please run room seeders first.');
            return;
        }

        // OTA confirmation number prefixes
        $otaPrefixes = [
            'booking_com' => 'BKG',
            'expedia' => 'EXP',
            'agoda' => 'AGD',
            'airbnb' => 'ABNB',
            'tripadvisor' => 'TA',
        ];

        // Create reservations for each OTA source
        $reservationsCreated = 0;

        foreach ($otaSources as $source) {
            // Create 2-4 reservations per source
            $numReservations = rand(2, 4);

            for ($i = 0; $i < $numReservations; $i++) {
                $guest = $guests->random();
                $room = $rooms->random();
                $roomType = $room->roomType;

                // Generate dates
                $checkIn = Carbon::now()->addDays(rand(-3, 14));
                $nights = rand(1, 7);
                $checkOut = $checkIn->copy()->addDays($nights);

                // Determine status based on dates
                if ($checkIn->isPast() && !$checkOut->isPast()) {
                    $status = 'checked_in';
                } elseif ($checkIn->isFuture()) {
                    $status = rand(0, 10) > 2 ? 'confirmed' : 'pending';
                } else {
                    $status = $statuses[rand(0, 3)];
                }

                // Calculate amounts
                $roomRate = $roomType ? ($roomType->price_per_night ?? $roomType->base_price ?? 50000) : 50000;
                $totalAmount = $roomRate * $nights;

                // Generate OTA confirmation number
                $otaConfirmationNumber = $otaPrefixes[$source] . '-' . strtoupper(uniqid());

                // Create reservation
                try {
                    Reservation::create([
                            'reservation_number' => 'OTA-' . strtoupper(uniqid()),
                            'guest_id' => $guest->id,
                        'room_id' => $room->id,
                        'room_type_id' => $roomType ? $roomType->id : null,
                        'check_in_date' => $checkIn->format('Y-m-d'),
                        'check_out_date' => $checkOut->format('Y-m-d'),
                                'nights' => $nights,
                                'adults' => rand(1, 2),
                        'children' => rand(0, 2),
                        'room_rate' => $roomRate,
                                    'total_room_charges' => $totalAmount,
                                    'total_amount' => $totalAmount,
                        'status' => $status,
                        'payment_status' => $status === 'checked_in' ? 'paid' : 'pending',
                        'source' => $source,
                        'ota_confirmation_number' => $otaConfirmationNumber,
                        'special_requests' => $this->getRandomSpecialRequest(),
                                        'created_by' => 1,
                        'created_at' => Carbon::now()->subDays(rand(1, 30)),
                        'updated_at' => Carbon::now(),
                    ]);
                    $reservationsCreated++;
                } catch (\Exception $e) {
                    $this->command->warn("Failed to create reservation: " . $e->getMessage());
                }
            }
        }

        $this->command->info("Created {$reservationsCreated} OTA reservations.");
    }

    /**
     * Get a random special request.
     */
    private function getRandomSpecialRequest(): ?string
    {
        $requests = [
            'Late check-out requested',
            'Early check-in requested',
            'Non-smoking room preferred',
            'High floor preferred',
            'Quiet room requested',
            'Extra bed needed',
            'Airport transfer required',
            'Baby cot required',
            'Connecting rooms needed',
            null,
            null,
            null, // More nulls to make special requests optional
        ];

        return $requests[array_rand($requests)];
    }
}
