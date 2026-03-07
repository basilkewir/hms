<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Room;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample reservations
        $guests = Guest::take(5)->get();
        $rooms = Room::take(10)->get();

        if ($guests->isEmpty()) {
            // Create sample guests if none exist
            for ($i = 1; $i <= 5; $i++) {
                $guest = Guest::create([
                    'name' => 'Guest ' . $i,
                    'email' => 'guest' . $i . '@example.com',
                    'phone' => '+1234567890' . $i,
                    'address' => 'Address ' . $i,
                    'id_type' => 'passport',
                    'id_number' => 'ID' . str_pad($i, 6, '0', STR_PAD_LEFT),
                ]);
                $guests->push($guest);
            }
        }

        if ($rooms->isEmpty()) {
            // Create sample rooms if none exist
            for ($i = 1; $i <= 10; $i++) {
                $room = Room::create([
                    'room_number' => str_pad($i, 3, '0', STR_PAD_LEFT),
                    'room_type_id' => 1,
                    'floor' => ceil($i / 3),
                    'status' => 'available',
                    'base_rate' => 100 + ($i * 10),
                ]);
                $rooms->push($room);
            }
        }

        // Create reservations
        $statuses = ['confirmed', 'checked_in', 'checked_out', 'cancelled'];
        $bookingSources = ['walk_in', 'phone', 'email', 'website', 'booking_com', 'expedia', 'agoda', 'travel_agent', 'corporate'];
        
        foreach ($guests as $index => $guest) {
            $room = $rooms[$index % $rooms->count()];
            $checkIn = Carbon::now()->addDays(rand(-5, 10));
            $checkOut = $checkIn->copy()->addDays(rand(1, 7));
            
            Reservation::create([
                'hotel_id' => 1,
                'reservation_number' => 'RES' . strtoupper(str_pad($index + 1, 6, '0', STR_PAD_LEFT)),
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'room_type_id' => $room->room_type_id ?? 1,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'nights' => $checkIn->diffInDays($checkOut),
                'number_of_adults' => rand(1, 2),
                'number_of_children' => rand(0, 2),
                'status' => $statuses[array_rand($statuses)],
                'room_rate' => $room->base_rate ?? 100,
                'total_room_charges' => ($room->base_rate ?? 100) * $checkIn->diffInDays($checkOut),
                'taxes' => 10,
                'service_charges' => 5,
                'total_amount' => ($room->base_rate ?? 100) * $checkIn->diffInDays($checkOut) + 15,
                'paid_amount' => ($room->base_rate ?? 100) * $checkIn->diffInDays($checkOut) + 15,
                'balance_amount' => 0,
                'booking_source' => $bookingSources[array_rand($bookingSources)],
                'special_requests' => 'Sample special request',
                'created_by' => 1,
            ]);
        }
    }
}
