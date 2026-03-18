<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomAmenity;

class RoomAmenitiesSeeder extends Seeder
{
    public function run()
    {
        $amenities = [
            // Connectivity & Entertainment
            ['name' => 'WiFi',                 'icon' => 'wifi',        'description' => 'High-speed wireless internet access',                  'is_active' => true],
            ['name' => 'Television',            'icon' => 'tv',          'description' => 'Flat-screen TV with cable/satellite channels',         'is_active' => true],
            ['name' => 'Smart TV',              'icon' => 'tv',          'description' => 'Smart TV with streaming apps (Netflix, YouTube etc.)',  'is_active' => true],
            ['name' => 'Telephone',             'icon' => 'telephone',   'description' => 'In-room telephone for local and international calls',   'is_active' => true],

            // Climate & Comfort
            ['name' => 'Air Conditioning',      'icon' => 'ac',          'description' => 'Individual climate control unit',                      'is_active' => true],
            ['name' => 'Ceiling Fan',           'icon' => 'ac',          'description' => 'Ceiling fan for air circulation',                      'is_active' => true],
            ['name' => 'Heating',               'icon' => 'ac',          'description' => 'Central or individual in-room heating',                'is_active' => true],
            ['name' => 'Blackout Curtains',     'icon' => 'balcony',     'description' => 'Full blackout curtains for complete darkness',          'is_active' => true],

            // Bathroom & Personal Care
            ['name' => 'Private Bathroom',      'icon' => 'jacuzzi',     'description' => 'Ensuite bathroom with shower or bathtub',              'is_active' => true],
            ['name' => 'Bathtub',               'icon' => 'jacuzzi',     'description' => 'Full-size soaking bathtub',                            'is_active' => true],
            ['name' => 'Jacuzzi / Hot Tub',     'icon' => 'jacuzzi',     'description' => 'Private jacuzzi or hot tub in room',                   'is_active' => true],
            ['name' => 'Walk-in Shower',        'icon' => 'jacuzzi',     'description' => 'Spacious walk-in rainfall shower',                     'is_active' => true],
            ['name' => 'Hair Dryer',            'icon' => 'hair_dryer',  'description' => 'Complimentary hair dryer provided',                    'is_active' => true],
            ['name' => 'Toiletries',            'icon' => 'hair_dryer',  'description' => 'Premium toiletries (shampoo, conditioner, soap)',      'is_active' => true],
            ['name' => 'Towels & Bathrobes',    'icon' => 'laundry',     'description' => 'Luxury bath towels and robes provided',                'is_active' => true],

            // Kitchen & Dining
            ['name' => 'Mini Bar',              'icon' => 'minibar',     'description' => 'Stocked minibar with drinks and snacks',               'is_active' => true],
            ['name' => 'Mini Fridge',           'icon' => 'fridge',      'description' => 'Personal refrigerator for guest use',                  'is_active' => true],
            ['name' => 'Microwave',             'icon' => 'kitchen',     'description' => 'In-room microwave oven',                               'is_active' => true],
            ['name' => 'Kettle',                'icon' => 'coffee',      'description' => 'Electric kettle for hot drinks',                       'is_active' => true],
            ['name' => 'Coffee Maker',          'icon' => 'coffee',      'description' => 'Coffee machine with complimentary pods or sachets',    'is_active' => true],
            ['name' => 'Kitchenette',           'icon' => 'kitchen',     'description' => 'Compact kitchen with basic cooking facilities',        'is_active' => true],
            ['name' => 'Full Kitchen',          'icon' => 'kitchen',     'description' => 'Fully equipped kitchen with stove, oven and sink',     'is_active' => true],
            ['name' => 'Dining Area',           'icon' => 'restaurant',  'description' => 'Dedicated dining space within the room',               'is_active' => true],

            // Workspace & Business
            ['name' => 'Work Desk',             'icon' => 'desk',        'description' => 'Dedicated workspace with ergonomic chair',             'is_active' => true],
            ['name' => 'Desk Lamp',             'icon' => 'desk',        'description' => 'Adjustable desk lamp for working',                     'is_active' => true],

            // Security & Storage
            ['name' => 'Safe',                  'icon' => 'safe',        'description' => 'In-room electronic safe for passports and valuables',  'is_active' => true],
            ['name' => 'Luggage Rack',          'icon' => 'desk',        'description' => 'Luggage rack for convenient bag storage',              'is_active' => true],
            ['name' => 'Wardrobe / Closet',     'icon' => 'desk',        'description' => 'Built-in wardrobe or walk-in closet',                  'is_active' => true],

            // Laundry & Housekeeping
            ['name' => 'Iron & Ironing Board',  'icon' => 'iron',        'description' => 'Iron and ironing board available in room',             'is_active' => true],
            ['name' => 'Washing Machine',       'icon' => 'laundry',     'description' => 'In-room washing machine',                              'is_active' => true],
            ['name' => 'Daily Housekeeping',    'icon' => 'laundry',     'description' => 'Daily room cleaning and linen change service',         'is_active' => true],
            ['name' => 'Turndown Service',      'icon' => 'laundry',     'description' => 'Evening turndown service',                             'is_active' => true],

            // Views & Outdoor
            ['name' => 'Balcony / Terrace',     'icon' => 'balcony',     'description' => 'Private balcony or terrace',                           'is_active' => true],
            ['name' => 'Ocean View',            'icon' => 'ocean_view',  'description' => 'Room with a view of the ocean or sea',                 'is_active' => true],
            ['name' => 'City View',             'icon' => 'city_view',   'description' => 'Room overlooking the city skyline',                    'is_active' => true],
            ['name' => 'Garden View',           'icon' => 'balcony',     'description' => 'Room overlooking a garden or green area',              'is_active' => true],
            ['name' => 'Pool View',             'icon' => 'pool',        'description' => 'Room overlooking the swimming pool',                   'is_active' => true],
            ['name' => 'Mountain View',         'icon' => 'balcony',     'description' => 'Scenic mountain or landscape view',                    'is_active' => true],

            // Hotel Facilities Included
            ['name' => 'Swimming Pool Access',  'icon' => 'pool',        'description' => 'Complimentary access to hotel swimming pool',          'is_active' => true],
            ['name' => 'Gym / Fitness Access',  'icon' => 'gym',         'description' => 'Complimentary access to hotel fitness centre',         'is_active' => true],
            ['name' => 'Spa Access',            'icon' => 'spa',         'description' => 'Complimentary or discounted access to hotel spa',      'is_active' => true],
            ['name' => 'Breakfast Included',    'icon' => 'breakfast',   'description' => 'Complimentary breakfast for all guests',               'is_active' => true],
            ['name' => 'Parking',               'icon' => 'parking',     'description' => 'Free or paid secure on-site parking',                  'is_active' => true],
            ['name' => 'Airport Shuttle',       'icon' => 'parking',     'description' => 'Complimentary or paid airport transfer service',       'is_active' => true],
            ['name' => 'Room Service',          'icon' => 'restaurant',  'description' => '24/7 in-room dining service',                          'is_active' => true],
            ['name' => 'Concierge Service',     'icon' => 'restaurant',  'description' => 'Dedicated concierge for bookings and requests',        'is_active' => true],

            // Accessibility
            ['name' => 'Wheelchair Accessible', 'icon' => 'desk',        'description' => 'Room designed for guests with mobility needs',         'is_active' => true],
            ['name' => 'Roll-in Shower',        'icon' => 'jacuzzi',     'description' => 'Accessible roll-in shower for mobility-impaired guests','is_active' => true],
        ];

        $created = 0;
        foreach ($amenities as $data) {
            $amenity = RoomAmenity::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
            if ($amenity->wasRecentlyCreated) {
                $created++;
            } else {
                // Update icon & description on re-run without changing is_active
                $amenity->update([
                    'icon'        => $data['icon'],
                    'description' => $data['description'],
                ]);
            }
        }

        $total = RoomAmenity::count();
        $this->command->info("Room Amenities: {$created} new records seeded, {$total} total in database.");
    }
}
