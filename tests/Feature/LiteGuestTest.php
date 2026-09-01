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
}