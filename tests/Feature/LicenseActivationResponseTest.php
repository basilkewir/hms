<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckLicense;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RestrictFrontDeskToLite;
use App\Http\Middleware\ShareSettings;
use App\Http\Middleware\ShareUserPermissions;
use App\Services\AdminAccountProvisioner;
use App\Services\LicenseValidationService;
use Mockery;
use Tests\TestCase;

class LicenseActivationResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            CheckLicense::class,
            HandleInertiaRequests::class,
            RestrictFrontDeskToLite::class,
            ShareSettings::class,
            ShareUserPermissions::class,
        ]);
    }

    public function test_rejected_license_returns_an_inertia_redirect_with_a_field_error(): void
    {
        $message = 'Device is blocked or the license device allocation limit has been reached';

        $licenseService = Mockery::mock(LicenseValidationService::class);
        $licenseService->shouldReceive('validateLicense')
            ->once()
            ->andReturn(['valid' => false, 'message' => $message]);

        $adminProvisioner = Mockery::mock(AdminAccountProvisioner::class);
        $adminProvisioner->shouldNotReceive('provision');

        $this->app->instance(LicenseValidationService::class, $licenseService);
        $this->app->instance(AdminAccountProvisioner::class, $adminProvisioner);

        $this->from('/license/activate')
            ->withHeader('X-Inertia', 'true')
            ->post('/license/activate', ['license_key' => str_repeat('A', 35)])
            ->assertRedirect('/license/activate')
            ->assertSessionHasErrors(['license_key' => $message]);
    }

    public function test_json_license_activation_keeps_returning_json_errors(): void
    {
        $message = 'Device is blocked or the license device allocation limit has been reached';

        $licenseService = Mockery::mock(LicenseValidationService::class);
        $licenseService->shouldReceive('validateLicense')
            ->once()
            ->andReturn(['valid' => false, 'message' => $message]);

        $adminProvisioner = Mockery::mock(AdminAccountProvisioner::class);
        $adminProvisioner->shouldNotReceive('provision');

        $this->app->instance(LicenseValidationService::class, $licenseService);
        $this->app->instance(AdminAccountProvisioner::class, $adminProvisioner);

        $this->withHeader('Accept', 'application/json')
            ->postJson('/license/activate', ['license_key' => str_repeat('A', 35)])
            ->assertBadRequest()
            ->assertJson([
                'success' => false,
                'message' => $message,
            ]);
    }

    public function test_successful_inertia_activation_redirects_to_login(): void
    {
        $licenseService = Mockery::mock(LicenseValidationService::class);
        $licenseService->shouldReceive('validateLicense')
            ->once()
            ->andReturn(['valid' => true, 'message' => 'License activated']);

        $adminProvisioner = Mockery::mock(AdminAccountProvisioner::class);
        $adminProvisioner->shouldReceive('provision')
            ->once()
            ->andReturn([
                'created' => true,
                'email' => 'admin@hotel.com',
                'password' => 'one-time-test-password',
            ]);

        $this->app->instance(LicenseValidationService::class, $licenseService);
        $this->app->instance(AdminAccountProvisioner::class, $adminProvisioner);

        $this->withHeader('X-Inertia', 'true')
            ->post('/license/activate', ['license_key' => str_repeat('A', 35)])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'License activated successfully.')
            ->assertSessionHas('initial_admin_credentials', [
                'email' => 'admin@hotel.com',
                'password' => 'one-time-test-password',
            ]);
    }
}
