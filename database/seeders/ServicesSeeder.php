<?php

namespace Database\Seeders;

use App\Models\ConciergeRequest;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $rooms = Room::all();
        $users = User::all();

        if ($rooms->isEmpty()) {
            $this->command->info('No rooms found. Please run rooms seeder first.');
            return;
        }

        // Create Housekeeping Tasks
        $housekeepingTasks = [
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'cleaning',
                'priority' => 'normal',
                'status' => 'pending',
                'scheduled_date' => Carbon::today(),
                'scheduled_time' => '09:00:00',
                'notes' => 'Regular room cleaning',
            ],
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'checkout',
                'priority' => 'high',
                'status' => 'in_progress',
                'scheduled_date' => Carbon::today(),
                'scheduled_time' => '10:00:00',
                'notes' => 'Guest checking out, deep clean needed',
            ],
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'stayover',
                'priority' => 'normal',
                'status' => 'completed',
                'scheduled_date' => Carbon::yesterday(),
                'scheduled_time' => '14:00:00',
                'notes' => 'Light cleaning for staying guest',
            ],
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'deep_clean',
                'priority' => 'urgent',
                'status' => 'pending',
                'scheduled_date' => Carbon::today()->addDay(),
                'scheduled_time' => '08:00:00',
                'notes' => 'Deep cleaning required after spill',
            ],
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'inspection',
                'priority' => 'normal',
                'status' => 'completed',
                'scheduled_date' => Carbon::yesterday(),
                'scheduled_time' => '16:00:00',
                'notes' => 'Weekly room inspection',
            ],
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'cleaning',
                'priority' => 'high',
                'status' => 'in_progress',
                'scheduled_date' => Carbon::today(),
                'scheduled_time' => '11:00:00',
                'notes' => 'VIP guest arriving soon',
            ],
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'checkout',
                'priority' => 'normal',
                'status' => 'pending',
                'scheduled_date' => Carbon::today()->addDay(),
                'scheduled_time' => '09:30:00',
                'notes' => 'Standard checkout cleaning',
            ],
            [
                'room_id' => $rooms->random()->id,
                'task_type' => 'maintenance',
                'priority' => 'low',
                'status' => 'completed',
                'scheduled_date' => Carbon::today()->subDays(2),
                'scheduled_time' => '13:00:00',
                'notes' => 'Assistance with maintenance issue',
            ],
        ];

        foreach ($housekeepingTasks as $task) {
            HousekeepingTask::firstOrCreate(
                ['room_id' => $task['room_id'], 'task_type' => $task['task_type'], 'scheduled_date' => $task['scheduled_date']],
                $task
            );
        }

        // Create Maintenance Requests
        $maintenanceRequests = [
            [
                'request_number' => 'MR-2026-001',
                'room_id' => $rooms->random()->id,
                'title' => 'AC Not Cooling',
                'category' => 'hvac',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'location' => 'Room 205',
                'description' => 'Air conditioning unit is not cooling the room properly. Temperature stays at 28°C even when set to 20°C.',
            ],
            [
                'request_number' => 'MR-2026-002',
                'room_id' => $rooms->random()->id,
                'title' => 'Leaking Faucet',
                'category' => 'plumb',
                'priority' => 'normal',
                'status' => 'open',
                'location' => 'Room 312 - Bathroom',
                'description' => 'The bathroom sink faucet is leaking and needs repair.',
            ],
            [
                'request_number' => 'MR-2026-003',
                'room_id' => $rooms->random()->id,
                'title' => 'Light Bulb Replacement',
                'category' => 'electr',
                'priority' => 'low',
                'status' => 'resolved',
                'location' => 'Room 108 - Closet',
                'description' => 'Closet light bulb burned out.',
                'resolved_at' => Carbon::yesterday(),
                'cost' => 15.00,
            ],
            [
                'request_number' => 'MR-2026-004',
                'room_id' => $rooms->random()->id,
                'title' => 'TV Remote Not Working',
                'category' => 'electr',
                'priority' => 'normal',
                'status' => 'open',
                'location' => 'Room 401',
                'description' => 'TV remote control is not responding. Tried replacing batteries but still not working.',
            ],
            [
                'request_number' => 'MR-2026-005',
                'room_id' => $rooms->random()->id,
                'title' => 'Door Lock Malfunction',
                'category' => 'car',
                'priority' => 'high',
                'status' => 'in_progress',
                'location' => 'Room 215',
                'description' => 'Room door lock is sticking and difficult to open/close.',
            ],
            [
                'request_number' => 'MR-2026-006',
                'room_id' => $rooms->random()->id,
                'title' => 'Wall Paint Touch-up',
                'category' => 'paint',
                'priority' => 'low',
                'status' => 'open',
                'location' => 'Room 503',
                'description' => 'Wall near window has paint peeling off.',
            ],
            [
                'request_number' => 'MR-2026-007',
                'room_id' => $rooms->random()->id,
                'title' => 'Hot Water Issue',
                'category' => 'plumb',
                'priority' => 'urgent',
                'status' => 'open',
                'location' => 'Room 220 - Bathroom',
                'description' => 'No hot water coming from shower. Cold water only.',
            ],
            [
                'request_number' => 'MR-2026-008',
                'room_id' => $rooms->random()->id,
                'title' => 'WiFi Connection Problem',
                'category' => 'electr',
                'priority' => 'normal',
                'status' => 'resolved',
                'location' => 'Room 330',
                'description' => 'WiFi signal very weak in room. Can barely connect.',
                'resolved_at' => Carbon::today()->subDays(3),
                'cost' => 0.00,
            ],
        ];

        $staffUsers = $users->filter(function($user) {
            return $user->hasAnyRole(['housekeeping', 'maintenance', 'manager', 'admin']);
        });

        foreach ($maintenanceRequests as $request) {
            $reportedBy = $users->random();
            $assignedTo = $staffUsers->isNotEmpty() ? $staffUsers->random() : null;

            MaintenanceRequest::firstOrCreate(
                ['request_number' => $request['request_number']],
                [
                    'room_id' => $request['room_id'],
                    'title' => $request['title'],
                    'category' => $request['category'],
                    'priority' => $request['priority'],
                    'status' => $request['status'],
                    'location' => $request['location'],
                    'description' => $request['description'],
                    'reported_by' => $reportedBy->id,
                    'assigned_to' => $assignedTo ? $assignedTo->id : null,
                    'reported_at' => Carbon::now()->subDays(rand(0, 5)),
                    'resolved_at' => $request['resolved_at'] ?? null,
                    'cost' => $request['cost'] ?? null,
                ]
            );
        }

        // Create Concierge Requests
        $conciergeRequests = [
            [
                'request_number' => 'CR-A1B2C3D4',
                'guest_name' => 'John Smith',
                'room_number' => '205',
                'service_type' => 'Room Service',
                'status' => 'completed',
                'details' => 'Breakfast order for 2 persons - Continental breakfast with coffee and pastries.',
            ],
            [
                'request_number' => 'CR-E5F6G7H8',
                'guest_name' => 'Sarah Johnson',
                'room_number' => '312',
                'service_type' => 'Transportation',
                'status' => 'in_progress',
                'details' => 'Airport pickup needed tomorrow at 8:00 AM. Flight number AF234.',
            ],
            [
                'request_number' => 'CR-I9J0K1L2',
                'guest_name' => 'Michael Brown',
                'room_number' => '108',
                'service_type' => 'Restaurant Reservation',
                'status' => 'pending',
                'details' => 'Table for 4 at Italian restaurant tonight at 7:30 PM.',
            ],
            [
                'request_number' => 'CR-M3N4O5P6',
                'guest_name' => 'Emily Davis',
                'room_number' => '401',
                'service_type' => 'Spa Booking',
                'status' => 'completed',
                'details' => 'Massage appointment for 2 persons - Swedish massage 60 min.',
            ],
            [
                'request_number' => 'CR-Q7R8S9T0',
                'guest_name' => 'Robert Wilson',
                'room_number' => '215',
                'service_type' => 'Extra Amenities',
                'status' => 'in_progress',
                'details' => 'Extra towels and toiletries needed. King size bed requested.',
            ],
            [
                'request_number' => 'CR-U1V2W3X4',
                'guest_name' => 'Jennifer Lee',
                'room_number' => '503',
                'service_type' => 'Information',
                'status' => 'completed',
                'details' => 'Information about local attractions and tourist spots.',
            ],
            [
                'request_number' => 'CR-Y5Z6A7B8',
                'guest_name' => 'David Miller',
                'room_number' => '220',
                'service_type' => 'Late Checkout',
                'status' => 'pending',
                'details' => 'Request for late checkout until 2:00 PM.',
            ],
            [
                'request_number' => 'CR-C9D0E1F2',
                'guest_name' => 'Lisa Anderson',
                'room_number' => '330',
                'service_type' => 'Gift Arrangement',
                'status' => 'in_progress',
                'details' => 'Flower arrangement and champagne for anniversary celebration.',
            ],
        ];

        foreach ($conciergeRequests as $request) {
            $createdBy = $users->random();

            ConciergeRequest::firstOrCreate(
                ['request_number' => $request['request_number']],
                [
                    'guest_name' => $request['guest_name'],
                    'room_number' => $request['room_number'],
                    'service_type' => $request['service_type'],
                    'status' => $request['status'],
                    'details' => $request['details'],
                    'created_by' => $createdBy->id,
                    'requested_at' => Carbon::now()->subHours(rand(1, 48)),
                ]
            );
        }

        $this->command->info('Services sample data seeded successfully!');
        $this->command->info('Created ' . count($housekeepingTasks) . ' housekeeping tasks');
        $this->command->info('Created ' . count($maintenanceRequests) . ' maintenance requests');
        $this->command->info('Created ' . count($conciergeRequests) . ' concierge requests');
    }
}
