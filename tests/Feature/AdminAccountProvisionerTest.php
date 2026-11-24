<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Http\Middleware\CheckLicense;
use App\Services\AdminAccountProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccountProvisionerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_admin_with_a_random_password_only_once(): void
    {
        $provisioner = app(AdminAccountProvisioner::class);

        $result = $provisioner->provision();

        $this->assertTrue($result['created']);
        $this->assertSame('admin@hotel.com', $result['email']);
        $this->assertIsString($result['password']);
        $this->assertGreaterThanOrEqual(40, strlen($result['password']));

        $user = User::where('email', 'admin@hotel.com')->firstOrFail();
        $this->assertTrue(Hash::check($result['password'], $user->password));
        $this->assertTrue($user->roles()->where('roles.name', 'admin')->exists());
        $this->assertTrue(Role::where('name', 'admin')->exists());

        $secondResult = $provisioner->provision();
        $this->assertFalse($secondResult['created']);
        $this->assertNull($secondResult['password']);
    }

    public function test_it_does_not_change_an_existing_admin_account_password(): void
    {
        $existingPassword = Hash::make('existing-password');
        User::query()->create([
            'first_name' => 'Existing',
            'last_name' => 'Administrator',
            'email' => 'admin@hotel.com',
            'password' => $existingPassword,
            'employment_status' => 'active',
            'is_active' => true,
        ]);

        $result = app(AdminAccountProvisioner::class)->provision();

        $this->assertFalse($result['created']);
        $this->assertNull($result['password']);
        $this->assertSame($existingPassword, User::where('email', 'admin@hotel.com')->value('password'));
    }

    public function test_initial_admin_credentials_are_shared_with_login_only_once(): void
    {
        $this->withoutMiddleware(CheckLicense::class);

        $credentials = [
            'email' => 'admin@hotel.com',
            'password' => 'temporary-one-time-password',
        ];

        $this->withSession([
            'initial_admin_credentials' => $credentials,
            'success' => 'License activated successfully.',
        ])->get(route('login'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('flash.initial_admin_credentials.email', $credentials['email'])
                ->where('flash.initial_admin_credentials.password', $credentials['password'])
            );

        $this->get(route('login'))
            ->assertInertia(fn (Assert $page) => $page
                ->missing('flash.initial_admin_credentials')
            );
    }
}
