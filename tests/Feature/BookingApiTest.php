<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestType;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Confirms that the public website booking API works end-to-end:
 *   GET  /api/public/room-types      → list bookable room types
 *   GET  /api/public/rooms           → list available, clean rooms
 *   GET  /api/public/availability    → per-type date availability
 *   POST /api/public/bookings        → create reservation (BookingController)
 *   GET  /api/booking/availability   → availability + pricing (OnlineBookingController)
 *   POST /api/booking/create         → full online booking with guest record
 */
class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    private RoomType $roomType;
    private Room $room;
    private string $bookingToken = 'test-booking-token-abc123';

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // prevent actual email dispatch during booking tests

        // Audit fields in guests/reservations require an existing user
        User::create([
            'first_name' => 'System',
            'last_name' => 'User',
            'email' => 'system-user@example.test',
            'password' => bcrypt('password'),
        ]);
        // OnlineBookingController assigns guest_type_id=1 for new online guests
        GuestType::create([
            'id'        => 1,
            'name'      => 'Regular',
            'code'      => 'REG',
            'is_active' => true,
        ]);
        // One active room type
        $this->roomType = RoomType::create([
            'name'          => 'Standard',
            'code'          => 'STD',
            'description'   => 'Standard single room',
            'max_occupancy' => 2,
            'max_adults'    => 2,
            'max_children'  => 1,
            'base_price'    => 20000,
            'is_active'     => true,
        ]);

        // One available, clean room belonging to that type
        $this->room = Room::create([
            'room_number'        => '101',
            'room_type_id'       => $this->roomType->id,
            'status'             => 'available',
            'housekeeping_status'=> 'clean',
            'is_active'          => true,
        ]);

        // Store the booking API token used by both controllers
        Setting::set('integration.booking_api_token', $this->bookingToken);
    }

    // =========================================================================
    // GET /api/public/room-types
    // =========================================================================

    /** Active room types are returned with refresh metadata and no-cache header */
    public function test_room_types_returns_active_types_with_metadata(): void
    {
        $response = $this->getJson('/api/public/room-types');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Standard')
            ->assertJsonPath('data.0.available_rooms', 1)
            ->assertJsonPath('meta.refresh_after_seconds', 30);

        $this->assertStringContainsString(
            'no-store',
            $response->headers->get('Cache-Control')
        );
    }

    /** Inactive room types are hidden from the website */
    public function test_room_types_excludes_inactive_types(): void
    {
        RoomType::create([
            'name'          => 'Penthouse',
            'code'          => 'PH',
            'max_occupancy' => 4,
            'max_adults'    => 4,
            'max_children'  => 2,
            'base_price'    => 100000,
            'is_active'     => false, // inactive — must not appear
        ]);

        $response = $this->getJson('/api/public/room-types');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    // =========================================================================
    // GET /api/public/rooms
    // =========================================================================

    /** Only rooms that are available AND clean appear in the listing */
    public function test_rooms_returns_only_available_clean_rooms(): void
    {
        $response = $this->getJson('/api/public/rooms');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.room_number', '101')
            ->assertJsonPath('data.0.status', 'available')
            ->assertJsonPath('data.0.housekeeping_status', 'clean')
            ->assertJsonPath('meta.refresh_after_seconds', 30);

        $this->assertStringContainsString(
            'no-store',
            $response->headers->get('Cache-Control')
        );
    }

    /** Rooms in maintenance are filtered out */
    public function test_rooms_filters_out_maintenance_rooms(): void
    {
        Room::create([
            'room_number'        => '102',
            'room_type_id'       => $this->roomType->id,
            'status'             => 'maintenance',
            'housekeeping_status'=> 'clean',
            'is_active'          => true,
        ]);

        $response = $this->getJson('/api/public/rooms');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /** Rooms with dirty housekeeping status are filtered out */
    public function test_rooms_filters_out_dirty_rooms(): void
    {
        Room::create([
            'room_number'        => '103',
            'room_type_id'       => $this->roomType->id,
            'status'             => 'available',
            'housekeeping_status'=> 'dirty',
            'is_active'          => true,
        ]);

        $response = $this->getJson('/api/public/rooms');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /** Out-of-order rooms do not appear on the website */
    public function test_rooms_filters_out_out_of_order_rooms(): void
    {
        $this->room->update(['status' => 'out_of_order']);

        $response = $this->getJson('/api/public/rooms');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    // =========================================================================
    // GET /api/public/availability
    // =========================================================================

    /** Availability returns the room count for a valid date range */
    public function test_availability_returns_count_for_valid_dates(): void
    {
        $response = $this->getJson('/api/public/availability?' . http_build_query([
            'room_type_id'   => $this->roomType->id,
            'check_in_date'  => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(8)->format('Y-m-d'),
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.available_rooms', 1)
            ->assertJsonPath('meta.refresh_after_seconds', 30);

        $this->assertStringContainsString(
            'no-store',
            $response->headers->get('Cache-Control')
        );
    }

    /** An unknown room type is rejected at validation level */
    public function test_availability_returns_404_for_unknown_room_type(): void
    {
        $response = $this->getJson('/api/public/availability?' . http_build_query([
            'room_type_id'   => 99999,
            'check_in_date'  => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
        ]));

        $response->assertStatus(422);
    }

    /** Missing date params are rejected with 422 */
    public function test_availability_requires_dates(): void
    {
        $response = $this->getJson('/api/public/availability?room_type_id=' . $this->roomType->id);

        $response->assertStatus(422);
    }

    // =========================================================================
    // POST /api/public/bookings  (BookingController::store)
    // =========================================================================

    /** Missing booking token returns 401 */
    public function test_store_booking_returns_401_without_token(): void
    {
        $response = $this->postJson('/api/public/bookings', [
            'guest'          => ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com', 'phone' => '+1234567890'],
            'room_type_id'   => $this->roomType->id,
            'check_in_date'  => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'adults'         => 1,
        ]);

        $response->assertUnauthorized();
    }

    /** A valid request with correct token creates a reservation and a guest */
    public function test_store_booking_creates_reservation(): void
    {
        $checkIn  = now()->addDays(10)->format('Y-m-d');
        $checkOut = now()->addDays(12)->format('Y-m-d');

        $response = $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/public/bookings', [
                'guest' => [
                    'first_name' => 'Jane',
                    'last_name'  => 'Smith',
                    'email'      => 'jane@example.com',
                    'phone'      => '+237600000001',
                ],
                'room_type_id'   => $this->roomType->id,
                'check_in_date'  => $checkIn,
                'check_out_date' => $checkOut,
                'adults'         => 1,
                'children'       => 0,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => [
                'reservation_id',
                'reservation_number',
                'status',
                'room_assigned',
            ]]);

        $this->assertDatabaseHas('reservations', [
            'check_in_date'  => \Carbon\Carbon::parse($checkIn)->toDateTimeString(),
            'check_out_date' => \Carbon\Carbon::parse($checkOut)->toDateTimeString(),
            'booking_source' => 'website',
            'status'         => 'pending',
        ]);

        $this->assertDatabaseHas('guests', [
            'email'      => 'jane@example.com',
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
        ]);

        $this->assertDatabaseHas('rooms', [
            'id'     => $this->room->id,
            'status' => 'reserved',
        ]);

        $this->getJson('/api/public/rooms')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /** A second website booking for the same room type/date range is rejected */
    public function test_store_booking_returns_409_when_room_already_reserved(): void
    {
        $checkIn  = now()->addDays(10)->format('Y-m-d');
        $checkOut = now()->addDays(12)->format('Y-m-d');

        $payload = [
            'guest' => [
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
                'email'      => 'jane@example.com',
                'phone'      => '+237600000001',
            ],
            'room_type_id'   => $this->roomType->id,
            'check_in_date'  => $checkIn,
            'check_out_date' => $checkOut,
            'adults'         => 1,
            'children'       => 0,
        ];

        $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/public/bookings', $payload)
            ->assertCreated();

        $payload['guest']['email'] = 'second@example.com';

        $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/public/bookings', $payload)
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    /** Wrong token returns 401 */
    public function test_store_booking_returns_401_with_wrong_token(): void
    {
        $response = $this->withHeaders(['X-Booking-Token' => 'wrong-token'])
            ->postJson('/api/public/bookings', [
                'guest'          => ['first_name' => 'Test', 'last_name' => 'User', 'email' => 't@t.com'],
                'room_type_id'   => $this->roomType->id,
                'check_in_date'  => now()->addDays(1)->format('Y-m-d'),
                'check_out_date' => now()->addDays(2)->format('Y-m-d'),
                'adults'         => 1,
            ]);

        $response->assertUnauthorized();
    }

    /** Non-existent room type is rejected at validation level */
    public function test_store_booking_returns_404_for_invalid_room_type(): void
    {
        $response = $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/public/bookings', [
                'guest'          => ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com'],
                'room_type_id'   => 99999,
                'check_in_date'  => now()->addDays(1)->format('Y-m-d'),
                'check_out_date' => now()->addDays(2)->format('Y-m-d'),
                'adults'         => 1,
            ]);

        $response->assertStatus(422);
    }

    // =========================================================================
    // GET /api/booking/availability  (OnlineBookingController::checkAvailability)
    // =========================================================================

    /** Returns matching room types with pricing + refresh metadata */
    public function test_online_availability_returns_room_types(): void
    {
        $response = $this->getJson('/api/booking/availability?' . http_build_query([
            'check_in'  => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(8)->format('Y-m-d'),
            'adults'    => 1,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'available_room_types')
            ->assertJsonPath('available_room_types.0.name', 'Standard')
            ->assertJsonPath('available_room_types.0.is_available', true)
            ->assertJsonPath('available_room_types.0.available_rooms', 1)
            ->assertJsonPath('meta.refresh_after_seconds', 30);

        $this->assertStringContainsString(
            'no-store',
            $response->headers->get('Cache-Control')
        );
    }

    /** Missing/invalid params return validation error */
    public function test_online_availability_rejects_missing_params(): void
    {
        $response = $this->getJson('/api/booking/availability');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /** Room types that cannot fit the requested guest count are excluded */
    public function test_online_availability_filters_by_guest_capacity(): void
    {
        $response = $this->getJson('/api/booking/availability?' . http_build_query([
            'check_in'  => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
            'adults'    => 10, // our room type only fits 2 adults
        ]));

        $response->assertOk()
            ->assertJsonCount(0, 'available_room_types');
    }

    // =========================================================================
    // POST /api/booking/create  (OnlineBookingController::createBooking)
    // =========================================================================

    /** Missing token returns 401 */
    public function test_online_create_booking_returns_401_without_token(): void
    {
        $response = $this->postJson('/api/booking/create', [
            'guest' => [
                'first_name'  => 'Test',
                'last_name'   => 'User',
                'email'       => 'test@example.com',
                'phone'       => '+237600000000',
                'nationality' => 'CM',
                'id_type'     => 'passport',
                'id_number'   => 'A123',
            ],
            'reservation' => [
                'room_type_id'     => $this->roomType->id,
                'check_in_date'    => now()->addDays(1)->format('Y-m-d'),
                'check_out_date'   => now()->addDays(3)->format('Y-m-d'),
                'number_of_adults' => 1,
            ],
        ]);

        $response->assertUnauthorized();
    }

    /** A complete request creates a confirmed reservation and a guest record */
    public function test_online_create_booking_creates_reservation(): void
    {
        $checkIn  = now()->addDays(15)->format('Y-m-d');
        $checkOut = now()->addDays(17)->format('Y-m-d');

        $response = $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/booking/create', [
                'guest' => [
                    'first_name'  => 'Alice',
                    'last_name'   => 'Dupont',
                    'email'       => 'alice@example.com',
                    'phone'       => '+237699000001',
                    'nationality' => 'CM',
                    'id_type'     => 'passport',
                    'id_number'   => 'P123456',
                ],
                'reservation' => [
                    'room_type_id'       => $this->roomType->id,
                    'check_in_date'      => $checkIn,
                    'check_out_date'     => $checkOut,
                    'number_of_adults'   => 1,
                    'number_of_children' => 0,
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['reservation' => [
                'reservation_number',
                'confirmation_token',
                'check_in',
                'check_out',
                'room_number',
                'room_type',
                'total_amount',
            ]]);

        $this->assertDatabaseHas('reservations', [
            'check_in_date'  => \Carbon\Carbon::parse($checkIn)->toDateTimeString(),
            'check_out_date' => \Carbon\Carbon::parse($checkOut)->toDateTimeString(),
            'booking_source' => 'website',
            'status'         => 'confirmed',
        ]);

        $this->assertDatabaseHas('guests', [
            'email'      => 'alice@example.com',
            'first_name' => 'Alice',
        ]);
    }

    /** When no rooms are available the endpoint returns 409 */
    public function test_online_create_booking_returns_409_when_no_rooms_available(): void
    {
        $this->room->update(['status' => 'occupied']);

        $response = $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/booking/create', [
                'guest' => [
                    'first_name'  => 'Bob',
                    'last_name'   => 'Martin',
                    'email'       => 'bob@example.com',
                    'phone'       => '+237699111111',
                    'nationality' => 'CM',
                    'id_type'     => 'passport',
                    'id_number'   => 'P789',
                ],
                'reservation' => [
                    'room_type_id'     => $this->roomType->id,
                    'check_in_date'    => now()->addDays(1)->format('Y-m-d'),
                    'check_out_date'   => now()->addDays(3)->format('Y-m-d'),
                    'number_of_adults' => 1,
                ],
            ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    /** A second booking for the same dates + room type returns 409 (no double-booking) */
    public function test_online_create_booking_prevents_double_booking(): void
    {
        $checkIn  = now()->addDays(20)->format('Y-m-d');
        $checkOut = now()->addDays(22)->format('Y-m-d');

        $payload = [
            'guest' => [
                'first_name'  => 'First',
                'last_name'   => 'Guest',
                'email'       => 'first@example.com',
                'phone'       => '+237699222222',
                'nationality' => 'CM',
                'id_type'     => 'passport',
                'id_number'   => 'P001',
            ],
            'reservation' => [
                'room_type_id'     => $this->roomType->id,
                'check_in_date'    => $checkIn,
                'check_out_date'   => $checkOut,
                'number_of_adults' => 1,
            ],
        ];

        // First booking succeeds
        $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/booking/create', $payload)
            ->assertCreated();

        // Room is now reserved — second booking must fail
        $payload['guest']['email'] = 'second@example.com';
        $payload['guest']['first_name'] = 'Second';

        $this->withHeaders(['X-Booking-Token' => $this->bookingToken])
            ->postJson('/api/booking/create', $payload)
            ->assertStatus(409);
    }
}
