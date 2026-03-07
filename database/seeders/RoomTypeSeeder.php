<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomTypes = [
            [
                'name' => 'Standard Single',
                'code' => 'STD-SGL',
                'description' => 'Comfortable single room with standard amenities',
                'max_occupancy' => 1,
                'max_adults' => 1,
                'max_children' => 0,
                'base_price' => 50.00,
                'extra_adult_charge' => 0,
                'extra_child_charge' => 0,
                'room_size_sqft' => 200,
                'bed_type' => 'Single',
                'bed_count' => 1,
                'has_balcony' => false,
                'has_living_room' => false,
                'view_type' => 'City View',
                'is_active' => true,
            ],
            [
                'name' => 'Standard Double',
                'code' => 'STD-DBL',
                'description' => 'Comfortable double room with standard amenities',
                'max_occupancy' => 2,
                'max_adults' => 2,
                'max_children' => 0,
                'base_price' => 75.00,
                'extra_adult_charge' => 20.00,
                'extra_child_charge' => 10.00,
                'room_size_sqft' => 250,
                'bed_type' => 'Double',
                'bed_count' => 1,
                'has_balcony' => false,
                'has_living_room' => false,
                'view_type' => 'City View',
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe Single',
                'code' => 'DLX-SGL',
                'description' => 'Spacious single room with premium amenities',
                'max_occupancy' => 1,
                'max_adults' => 1,
                'max_children' => 0,
                'base_price' => 80.00,
                'extra_adult_charge' => 0,
                'extra_child_charge' => 0,
                'room_size_sqft' => 300,
                'bed_type' => 'Queen',
                'bed_count' => 1,
                'has_balcony' => true,
                'has_living_room' => false,
                'view_type' => 'City View',
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe Double',
                'code' => 'DLX-DBL',
                'description' => 'Spacious double room with premium amenities',
                'max_occupancy' => 2,
                'max_adults' => 2,
                'max_children' => 1,
                'base_price' => 120.00,
                'extra_adult_charge' => 30.00,
                'extra_child_charge' => 15.00,
                'room_size_sqft' => 350,
                'bed_type' => 'Queen',
                'bed_count' => 1,
                'has_balcony' => true,
                'has_living_room' => false,
                'view_type' => 'Ocean View',
                'is_active' => true,
            ],
            [
                'name' => 'Suite',
                'code' => 'STE',
                'description' => 'Luxurious suite with separate living area',
                'max_occupancy' => 4,
                'max_adults' => 3,
                'max_children' => 2,
                'base_price' => 200.00,
                'extra_adult_charge' => 40.00,
                'extra_child_charge' => 20.00,
                'room_size_sqft' => 500,
                'bed_type' => 'King',
                'bed_count' => 1,
                'has_balcony' => true,
                'has_living_room' => true,
                'view_type' => 'Ocean View',
                'is_active' => true,
            ],
            [
                'name' => 'Presidential Suite',
                'code' => 'PRES',
                'description' => 'Ultra-luxurious presidential suite with all amenities',
                'max_occupancy' => 6,
                'max_adults' => 4,
                'max_children' => 3,
                'base_price' => 500.00,
                'extra_adult_charge' => 50.00,
                'extra_child_charge' => 25.00,
                'room_size_sqft' => 1000,
                'bed_type' => 'King',
                'bed_count' => 2,
                'has_balcony' => true,
                'has_living_room' => true,
                'view_type' => 'Ocean View',
                'is_active' => true,
            ],
        ];

        foreach ($roomTypes as $roomType) {
            \App\Models\RoomType::updateOrCreate(
                ['code' => $roomType['code']],
                $roomType
            );
        }
    }
}
