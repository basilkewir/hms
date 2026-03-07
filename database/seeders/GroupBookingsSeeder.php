<?php

namespace Database\Seeders;

use App\Models\GroupBooking;
use Illuminate\Database\Seeder;

class GroupBookingsSeeder extends Seeder
{
    public function run()
    {
        $groupBookings = [
            [
                'name' => 'Tech Conference 2026',
                'contact_person' => 'John Smith',
                'contact_email' => 'john.smith@techconf.com',
                'contact_phone' => '+1234567890',
                'total_guests' => 250,
                'total_amount' => 15000.00,
                'deposit_amount' => 5000.00,
                'status' => 'confirmed',
                'booking_details' => [
                    'halls' => [
                        [
                            'hall_id' => 1,
                            'quantity' => 1,
                            'start_time' => '2026-03-15 08:00:00',
                            'end_time' => '2026-03-15 18:00:00'
                        ]
                    ],
                    'rooms' => [
                        [
                            'room_type_id' => 1,
                            'quantity' => 10
                        ],
                        [
                            'room_type_id' => 2,
                            'quantity' => 5
                        ]
                    ],
                    'packages' => [
                        [
                            'package_id' => 1,
                            'optional_features' => ['coffee_break', 'extended_hours']
                        ]
                    ]
                ],
                'notes' => 'Requires AV equipment and catering for 250 attendees'
            ],
            [
                'name' => 'Wedding Celebration',
                'contact_person' => 'Sarah Johnson',
                'contact_email' => 'sarah.johnson@gmail.com',
                'contact_phone' => '+0987654321',
                'total_guests' => 150,
                'total_amount' => 25000.00,
                'deposit_amount' => 8000.00,
                'status' => 'confirmed',
                'booking_details' => [
                    'halls' => [
                        [
                            'hall_id' => 5,
                            'quantity' => 1,
                            'start_time' => '2026-04-20 16:00:00',
                            'end_time' => '2026-04-20 23:00:00'
                        ]
                    ],
                    'rooms' => [
                        [
                            'room_type_id' => 3,
                            'quantity' => 2
                        ]
                    ],
                    'packages' => [
                        [
                            'package_id' => 3,
                            'optional_features' => ['premium_decor', 'live_band']
                        ]
                    ]
                ],
                'notes' => 'Wedding ceremony and reception. Need bridal suite and catering.'
            ],
            [
                'name' => 'Corporate Training',
                'contact_person' => 'Michael Brown',
                'contact_email' => 'michael.brown@company.com',
                'contact_phone' => '+1122334455',
                'total_guests' => 40,
                'total_amount' => 8000.00,
                'deposit_amount' => 2500.00,
                'status' => 'pending',
                'booking_details' => [
                    'halls' => [
                        [
                            'hall_id' => 2,
                            'quantity' => 1,
                            'start_time' => '2026-05-10 09:00:00',
                            'end_time' => '2026-05-10 17:00:00'
                        ]
                    ],
                    'rooms' => [
                        [
                            'room_type_id' => 1,
                            'quantity' => 3
                        ]
                    ],
                    'packages' => [
                        [
                            'package_id' => 4,
                            'optional_features' => ['catering', 'video_conferencing']
                        ]
                    ]
                ],
                'notes' => '3-day training program. Need AV equipment and breakout rooms.'
            ],
            [
                'name' => 'Family Reunion',
                'contact_person' => 'Emily Davis',
                'contact_email' => 'emily.davis@gmail.com',
                'contact_phone' => '+3344556677',
                'total_guests' => 80,
                'total_amount' => 6000.00,
                'deposit_amount' => 2000.00,
                'status' => 'confirmed',
                'booking_details' => [
                    'halls' => [
                        [
                            'hall_id' => 3,
                            'quantity' => 1,
                            'start_time' => '2026-06-12 12:00:00',
                            'end_time' => '2026-06-12 20:00:00'
                        ]
                    ],
                    'rooms' => [
                        [
                            'room_type_id' => 2,
                            'quantity' => 8
                        ]
                    ],
                    'packages' => [
                        [
                            'package_id' => 5,
                            'optional_features' => ['themed_decor', 'live_entertainment']
                        ]
                    ]
                ],
                'notes' => 'Family gathering with dinner and entertainment.'
            ],
            [
                'name' => 'Product Launch Event',
                'contact_person' => 'David Wilson',
                'contact_email' => 'david.wilson@company.com',
                'contact_phone' => '+4455667788',
                'total_guests' => 120,
                'total_amount' => 18000.00,
                'deposit_amount' => 6000.00,
                'status' => 'confirmed',
                'booking_details' => [
                    'halls' => [
                        [
                            'hall_id' => 1,
                            'quantity' => 1,
                            'start_time' => '2026-07-25 18:00:00',
                            'end_time' => '2026-07-25 22:00:00'
                        ]
                    ],
                    'rooms' => [
                        [
                            'room_type_id' => 1,
                            'quantity' => 5
                        ]
                    ],
                    'packages' => [
                        [
                            'package_id' => 2,
                            'optional_features' => ['live_streaming', 'photography']
                        ]
                    ]
                ],
                'notes' => 'Product launch with media coverage and VIP guests.'
            ],
        ];

        foreach ($groupBookings as $groupBooking) {
            GroupBooking::create($groupBooking);
        }
    }
}
