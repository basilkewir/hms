<?php

namespace Tests\Feature;

use App\Models\License;
use App\Services\LicenseValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LicenseValidationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_license_is_preferred_over_newer_trial_record(): void
    {
        License::create([
            'license_key' => 'ACTIVE-1234-5678-9012-34567890',
            'license_type' => 'BASIC',
            'product_name' => 'Hotel Management System',
            'customer_name' => 'Active Hotel',
            'customer_email' => 'active@example.test',
            'status' => 'active',
            'issued_at' => now()->subDays(5),
            'activated_at' => now()->subDays(5),
            'last_validated_at' => now(),
            'expires_at' => null,
            'license_data' => [
                'license_key' => 'ACTIVE-1234-5678-9012-34567890',
                'license_type' => 'BASIC',
                'status' => 'ACTIVE',
                'token' => 'active-token',
                'validated_at' => now()->toIso8601String(),
            ],
        ]);

        License::create([
            'license_key' => 'TRIAL-ABCDEFGH',
            'license_type' => 'TRIAL',
            'product_name' => 'Hotel Management System',
            'customer_name' => 'Trial Hotel',
            'customer_email' => 'trial@example.test',
            'status' => 'trial',
            'issued_at' => now(),
            'activated_at' => now(),
            'last_validated_at' => now(),
            'expires_at' => now()->addDays(14),
            'license_data' => [
                'license_key' => 'TRIAL-ABCDEFGH',
                'license_type' => 'TRIAL',
                'status' => 'TRIAL',
                'validated_at' => now()->toIso8601String(),
            ],
        ]);

        $service = app(LicenseValidationService::class);
        $status = $service->getLicenseStatus();

        $this->assertTrue($status['licensed']);
        $this->assertSame('ACTIVE-1234-5678-9012-34567890', $status['status']['license_key']);
        $this->assertSame('BASIC', $status['status']['license_type']);
    }

    public function test_verified_active_license_remains_valid_locally_after_short_server_expiry(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'status' => 'ACTIVE',
                'license_type' => 'BASIC',
                'hotel_name' => 'Persistent Hotel',
                'expires_at' => now()->addMinute()->toIso8601String(),
                'token_expires_at' => now()->addMinute()->toIso8601String(),
                'token' => 'verified-token',
                'features' => [
                    'max_users' => 100,
                ],
            ], 200),
        ]);

        $service = app(LicenseValidationService::class);
        $result = $service->validateLicense('ABCD1234-EFGH5678-IJKL9012-MNOP3456', 'Persistent Hotel');

        $this->assertTrue($result['valid']);

        $license = License::where('license_key', 'ABCD1234-EFGH5678-IJKL9012-MNOP3456')->first();

        $this->assertNotNull($license);
        $this->assertSame('active', $license->status);
        $this->assertNull($license->expires_at);
        $this->assertNotEmpty($license->license_data['expires_at']);

        $this->travel(2)->minutes();

        $this->assertTrue($service->isSystemLicensed());
        $this->assertTrue($service->getLicenseStatus()['licensed']);
    }
}