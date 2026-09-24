<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontDeskLiteLockdownTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName, string $email): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            [
                'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                'description' => $roleName . ' test role',
                'is_active' => true,
            ]
        );

        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
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
            'room_number' => '301',
            'room_type_id' => $type->id,
            'status' => 'available',
            'is_active' => true,
        ]);
    }

    public function test_front_desk_can_open_guest_display(): void
    {
        $this->actingAs($this->userWithRole('front_desk', 'fd-lite@example.test'));

        $this->get(route('lite.dashboard'))->assertOk();
    }

    public function test_front_desk_is_blocked_from_other_pages(): void
    {
        $this->actingAs($this->userWithRole('front_desk', 'fd-block@example.test'));

        $this->get('/admin/rooms')->assertForbidden();
        $this->get('/front-desk/dashboard')->assertForbidden();
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/user/profile')->assertOk();
    }

    public function test_admin_can_still_use_guest_display_and_admin_pages(): void
    {
        $admin = $this->userWithRole('admin', 'admin-lite@example.test');
        $this->actingAs($admin);

        $this->get(route('lite.dashboard'))->assertOk();
        $this->get('/admin/rooms')->assertOk();
    }

    public function test_front_desk_can_set_guest_name_on_lite_page(): void
    {
        $this->actingAs($this->userWithRole('front_desk', 'fd-guest@example.test'));
        $room = $this->makeRoom();

        $this->post(route('lite.guests.store'), [
            'room_id' => $room->id,
            'first_name' => 'Alex Guest',
        ])->assertRedirect(route('lite.dashboard'));
    }
}
