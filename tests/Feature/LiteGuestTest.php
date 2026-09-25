<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiteGuestTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Admin',
                'description' => 'Admin test role',
                'is_active' => true,
            ]
        );

        $user = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'lite-admin@example.test',
            'password' => bcrypt('password'),
            'country' => 'Cameroon',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->attach($role->id, ['assigned_by' => $user->id]);

        return $user->fresh('roles');
    }

    private function makeRoom(): Room
    {
        $type = RoomType::create([
            'name' => 'Standard',
            'code' => 'STD',
            'description' => 'Default room type',
            'max_occupancy' => 2,
            'max_adults' => 2,
            'max_children' => 0,
            'base_price' => 0.00,
            'is_active' => true,
        ]);

        return Room::create([
            'room_number' => '101',
            'room_type_id' => $type->id,
            'status' => 'available',
            'is_active' => true,
        ]);
    }

    public function test_guest_name_input_creates_checked_in_reservation(): void
    {
        $this->actingAs($this->adminUser());

        $room = $this->makeRoom();

        $this->post(route('lite.guests.store'), [
            'room_id' => $room->id,
            'first_name' => 'John Doe',
        ])->assertRedirect(route('lite.dashboard'));

        $guest = Guest::where('first_name', 'John')->where('last_name', 'Doe')->first();
        $this->assertNotNull($guest, 'A guest row should be created for the typed name.');

        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->first();
        $this->assertNotNull($reservation, 'A checked-in reservation should exist for the room.');
        $this->assertEquals($guest->id, $reservation->guest_id);

        // The reservation must be visible to the TV display reader
        $this->assertTrue(
            $reservation->check_in_date->lte(now()->toDateString())
            && $reservation->check_out_date->gte(now()->toDateString())
        );

        $this->assertEquals('occupied', $room->fresh()->status);
    }

    public function test_lite_guest_checkout_clears_the_room(): void
    {
        $this->actingAs($this->adminUser());

        $room = $this->makeRoom();

        $this->post(route('lite.guests.store'), [
            'room_id' => $room->id,
            'first_name' => 'Jane',
        ]);

        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->firstOrFail();

        $this->post(route('lite.guests.checkout'), [
            'reservation_id' => $reservation->id,
        ])->assertRedirect(route('lite.dashboard'));

        $this->assertEquals('checked_out', $reservation->fresh()->status);
        $this->assertEquals('available', $room->fresh()->status);
    }

    public function test_room_creation_via_lite_flow(): void
    {
        $this->actingAs($this->adminUser());

        $response = $this->post(route('lite.rooms.store'), [
            'room_number' => '202',
        ]);

        $response->assertRedirect(route('lite.dashboard'));
        $this->assertDatabaseHas('rooms', [
            'room_number' => '202',
            'status' => 'available',
        ]);
    }

    public function test_guest_name_expiry_is_stored_and_swept_by_dashboard(): void
    {
        $this->actingAs($this->adminUser());

        $room = $this->makeRoom();

        $this->post(route('lite.guests.store'), [
            'room_id'     => $room->id,
            'first_name'  => 'Expiry Guest',
            'ttl_minutes' => 30,
        ])->assertRedirect(route('lite.dashboard'));

        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->firstOrFail();

        $this->assertNotNull($reservation->guest_display_expires_at, 'TTL must be stored on the reservation.');
        $this->assertTrue($reservation->guest_display_expires_at->greaterThan(now()->addMinutes(29)));

        // Push the expiry into the past — the dashboard sweep must clear it
        $reservation->update(['guest_display_expires_at' => now()->subMinute()]);
        $this->get(route('lite.dashboard'))->assertOk();

        $this->assertEquals('checked_out', $reservation->fresh()->status);
        $this->assertEquals('available', $room->fresh()->status);
    }

    public function test_zero_ttl_means_the_name_never_expires(): void
    {
        $this->actingAs($this->adminUser());

        $room = $this->makeRoom();

        $this->post(route('lite.guests.store'), [
            'room_id'     => $room->id,
            'first_name'  => 'Forever Guest',
            'ttl_minutes' => 0,
        ])->assertRedirect(route('lite.dashboard'));

        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->firstOrFail();

        $this->assertNull($reservation->guest_display_expires_at, 'TTL 0 must mean no expiry.');
    }

    public function test_default_ttl_setting_persists(): void
    {
        $this->actingAs($this->adminUser());

        $this->post(route('lite.settings.ttl'), [
            'ttl_minutes' => 240,
        ])->assertRedirect(route('lite.dashboard'));

        $this->assertEquals(240, (int) \App\Models\Setting::get('guest_display_ttl_minutes', 120));
    }

    public function test_client_info_hides_expired_guest_name(): void
    {
        $room = $this->makeRoom();

        $device = \App\Models\IptvDevice::create([
            'room_id'           => $room->id,
            'device_id'         => 'TEST-DEVICE-1',
            'device_name'       => 'Room TV',
            'registration_token' => 'test-token-1',
            'is_active'         => true,
        ]);

        $this->actingAs($this->adminUser());
        $this->post(route('lite.guests.store'), [
            'room_id'     => $room->id,
            'first_name'  => 'Visible Guest',
            'ttl_minutes' => 60,
        ]);

        $headers = ['X-Device-ID' => 'TEST-DEVICE-1'];

        $res = $this->getJson('/api/iptv/client-info', $headers);
        $res->assertOk();
        $this->assertEquals(
            'Visible Guest',
            $res->json('data.guest.name'),
            'A guest name within its expiry window must be visible to the TV.'
        );

        Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->update(['guest_display_expires_at' => now()->subMinute()]);

        $res = $this->getJson('/api/iptv/client-info', $headers);
        $res->assertOk();
        $this->assertNull(
            $res->json('data.guest.name'),
            'An expired guest name must be hidden from the TV immediately.'
        );
    }
}