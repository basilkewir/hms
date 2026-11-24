<?php

namespace Tests\Feature;

use App\Models\IptvDevice;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsIptvSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\CheckLicense::class);
    }

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
            'email' => 'admin-settings@example.test',
            'password' => bcrypt('password'),
            'country' => 'Cameroon',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->attach($role->id, ['assigned_by' => $user->id]);

        return $user->fresh('roles');
    }

    public function test_saving_iptv_settings_persists_keys_and_bumps_all_devices(): void
    {
        $this->actingAs($this->adminUser());

        $device = IptvDevice::create([
            'device_id'         => 'TST-SETTINGS-1',
            'device_name'       => 'Settings Test TV',
            'registration_token' => 'tst-settings-token',
            'is_active'         => true,
        ]);
        $before = (int) ($device->settings_version ?? 0);

        $response = $this->putJson(route('admin.settings.update'), [
            'settings' => [
                'weather_city'    => 'Kribi',
                'weather_enabled' => true,
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);
        $response->assertJsonPath('devices_pushed', 1);

        $this->assertEquals('Kribi', Setting::get('weather_city'));
        $this->assertTrue((bool) Setting::get('weather_enabled'));
        $this->assertEquals(
            'iptv',
            Setting::where('key', 'weather_city')->value('group'),
            'IPTV keys must be stored under the iptv group.'
        );
        $this->assertEquals(
            $before + 1,
            (int) $device->fresh()->settings_version,
            'Every active device must get its settings_version bumped.'
        );
    }

    public function test_saving_non_iptv_settings_does_not_push_devices(): void
    {
        $this->actingAs($this->adminUser());

        $device = IptvDevice::create([
            'device_id'         => 'TST-SETTINGS-2',
            'device_name'       => 'Settings Test TV 2',
            'registration_token' => 'tst-settings-token-2',
            'is_active'         => true,
        ]);
        $before = (int) ($device->settings_version ?? 0);

        $response = $this->putJson(route('admin.settings.update'), [
            'settings' => [
                'hotel_fax' => '+1 555 0000',
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'devices_pushed' => 0,
        ]);
        $this->assertEquals('+1 555 0000', Setting::get('hotel_fax'));
        $this->assertEquals($before, (int) $device->fresh()->settings_version);
    }

    public function test_theme_settings_persist_and_are_served_as_the_authoritative_theme(): void
    {
        $this->actingAs($this->adminUser());

        $this->putJson(route('admin.settings.update'), [
            'settings' => [
                'theme_mode' => 'light',
                'theme_primary_color' => '#123456',
                'theme_background_color' => '#fefefe',
            ],
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('settings', [
            'key' => 'theme_primary_color',
            'value' => '#123456',
            'group' => 'theme',
        ]);

        $this->getJson('/api/theme')
            ->assertOk()
            ->assertJsonPath('theme_mode', 'light')
            ->assertJsonPath('theme_primary_color', '#123456')
            ->assertJsonPath('theme_background_color', '#fefefe')
            ->assertJsonPath('theme_info_color', '#3b82f6');
    }

    public function test_license_info_endpoint_returns_refreshed_license_details(): void
    {
        $this->actingAs($this->adminUser());
        $this->mock(LicenseValidationService::class)
            ->shouldReceive('refreshLicenseStatus')
            ->once()
            ->andReturn([
                'licensed' => true,
                'status' => [
                    'license_key' => 'ABCD1234-EFGH5678-IJKL9012-MNOP3456',
                    'hotel_name' => 'Current Hotel',
                    'license_type' => 'PREMIUM',
                    'status' => 'ACTIVE',
                    'expires_at' => '2027-01-01T00:00:00Z',
                    'features' => ['analytics' => true],
                    'device_allocation' => [],
                    'max_devices' => 50,
                ],
            ]);

        $this->getJson(route('admin.license.info'))
            ->assertOk()
            ->assertJsonPath('licensed', true)
            ->assertJsonPath('hotel_name', 'Current Hotel')
            ->assertJsonPath('license_type', 'PREMIUM')
            ->assertJsonPath('max_devices', 50);
    }
}
