<?php

namespace Tests\Feature;

use App\Models\License;
use App\Services\LicenseValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LicenseValidationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private TestableLicenseValidationService $licenseService;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.license.signature_secret' => 'test-license-signature-secret',
            'services.license.server_url' => 'http://127.0.0.1:8000/api/license',
        ]);

        $this->licenseService = new TestableLicenseValidationService();
        $this->app->instance(LicenseValidationService::class, $this->licenseService);
    }

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
                'device_id' => TestableLicenseValidationService::DEVICE_ID,
                'hardware_fingerprint' => TestableLicenseValidationService::HARDWARE_FINGERPRINT,
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

        $status = $this->licenseService->getLicenseStatus();

        $this->assertTrue($status['licensed']);
        $this->assertSame('ACTIVE-1234-5678-9012-34567890', $status['status']['license_key']);
        $this->assertSame('BASIC', $status['status']['license_type']);
    }

    public function test_verified_active_license_remains_valid_locally_after_short_server_expiry(): void
    {
        Http::fake(function ($request) {
            $nonce = str_ends_with($request->url(), '/validate-token')
                ? (string) data_get($request->data(), 'nonce')
                : (string) data_get($request->data(), 'metadata.license_nonce');

            return Http::response($this->signedTimePayload([
                'success' => true,
                'status' => 'ACTIVE',
                'license_type' => 'PERPETUAL',
                'hotel_name' => 'Persistent Hotel',
                'expires_at' => 'Never Expires',
                'token_expires_at' => now()->addMinute()->toIso8601String(),
                'token' => 'verified-token',
                'max_rooms' => 100,
                'max_devices' => 5,
                'max_android_tv' => 2,
                'max_smart_tv' => 1,
                'max_backend' => 1,
                'max_admin_panel' => 1,
                'device_allocation' => [
                    'android_tv' => ['current' => 1, 'max' => 2, 'available' => 1],
                    'smart_tv' => ['current' => 1, 'max' => 1, 'available' => 0],
                    'backend' => ['current' => 1, 'max' => 1, 'available' => 0],
                    'admin_panel' => ['current' => 0, 'max' => 1, 'available' => 1],
                ],
                'features' => ['max_channels' => 50],
            ], $nonce), 200);
        });

        $result = $this->licenseService->validateLicense(
            'ABCD1234-EFGH5678-IJKL9012-MNOP3456',
            'Persistent Hotel'
        );

        $this->assertTrue($result['valid']);

        $license = License::where('license_key', 'ABCD1234-EFGH5678-IJKL9012-MNOP3456')->first();

        $this->assertNotNull($license);
        $this->assertSame('active', $license->status);
        $this->assertNull($license->expires_at);
        $this->assertSame('Never Expires', $license->license_data['expires_at']);
        $this->assertSame(100, $license->license_data['max_rooms']);
        $this->assertSame(2, $license->max_android_tv);
        $this->assertSame(1, $license->max_smart_tv);
        $this->assertSame(1, $license->max_backend);
        $this->assertSame(1, $license->max_admin_panel);
        $this->assertSame(3, $license->license_data['total_used']);
        $this->assertSame(5, $license->license_data['total_limit']);
        $this->assertSame(1, $license->license_data['device_allocation'][0]['used']);
        $this->assertSame('Rooms', $license->license_data['device_allocation'][4]['type']);

        $this->travel(2)->minutes();

        $this->assertTrue($this->licenseService->isSystemLicensed());
        $this->assertTrue($this->licenseService->getLicenseStatus()['licensed']);
    }

    public function test_license_allows_access_during_bounded_offline_grace_after_online_verification(): void
    {
        config([
            'services.license.server_url' => 'http://127.0.0.1:8000/api/license',
            'services.license.offline_grace_hours' => 72,
        ]);
        Http::fake([
            '*' => Http::response([], 503),
        ]);

        $this->createOnlineVerifiedLicense(now()->subHours(24));

        $this->assertTrue($this->licenseService->periodicCheck());

        Http::assertSent(fn ($request) => $request->url() === 'http://127.0.0.1:8000/api/license/validate-token');
    }

    public function test_license_blocks_access_when_offline_grace_period_has_expired(): void
    {
        config(['services.license.offline_grace_hours' => 72]);
        Http::fake([
            '*' => Http::response([], 503),
        ]);

        $this->createOnlineVerifiedLicense(now()->subHours(73));

        $this->assertFalse($this->licenseService->periodicCheck());
    }

    public function test_explicit_server_rejection_does_not_use_offline_grace(): void
    {
        config(['services.license.offline_grace_hours' => 72]);
        Http::fake([
            '*' => Http::response(['success' => false, 'error' => 'License revoked.'], 401),
        ]);

        $this->createOnlineVerifiedLicense(now()->subHour());

        $this->assertFalse($this->licenseService->periodicCheck());
    }

    public function test_license_without_online_verification_cannot_use_offline_grace(): void
    {
        config(['services.license.offline_grace_hours' => 72]);
        Http::fake([
            '*' => Http::response([], 503),
        ]);

        $license = $this->createOnlineVerifiedLicense(now()->subHour());
        $licenseData = $license->license_data;
        unset($licenseData['online_verified_at']);
        $license->update(['license_data' => $licenseData]);

        $this->assertFalse($this->licenseService->periodicCheck());
    }

    public function test_activation_rejects_an_invalid_server_time_signature(): void
    {
        Http::fake(function ($request) {
            $nonce = (string) data_get($request->data(), 'metadata.license_nonce');
            $payload = $this->signedTimePayload(['success' => true, 'token' => 'bad-token'], $nonce);
            $payload['server_time_signature'] = 'invalid-signature';

            return Http::response($payload, 200);
        });

        $result = $this->licenseService->validateLicense(
            'ABCD1234-EFGH5678-IJKL9012-MNOP3456',
            'Persistent Hotel'
        );

        $this->assertFalse($result['valid']);
        $this->assertDatabaseMissing('licenses', [
            'license_key' => 'ABCD1234-EFGH5678-IJKL9012-MNOP3456',
        ]);
    }

    public function test_activation_reports_local_persistence_errors_as_system_errors_not_network_errors(): void
    {
        Schema::table('licenses', function ($table) {
            $table->dropColumn('max_android_tv');
        });

        Http::fake(function ($request) {
            $nonce = (string) data_get($request->data(), 'metadata.license_nonce');

            return Http::response($this->signedTimePayload([
                'success' => true,
                'license_type' => 'BASIC',
                'token' => 'verified-token',
                'max_rooms' => 100,
                'max_devices' => 2,
                'max_android_tv' => 1,
                'max_smart_tv' => 1,
                'max_backend' => 0,
                'max_admin_panel' => 0,
                'device_allocation' => [],
                'features' => [],
            ], $nonce), 200);
        });

        $result = $this->licenseService->validateLicense(
            'ABCD1234-EFGH5678-IJKL9012-MNOP3456',
            'Persistent Hotel'
        );

        $this->assertFalse($result['valid']);
        $this->assertFalse($result['network_error']);
        $this->assertStringContainsString('local system error', $result['message']);
    }

    public function test_token_validation_rejects_a_server_time_nonce_mismatch(): void
    {
        Http::fake(function ($request) {
            $nonce = (string) data_get($request->data(), 'nonce');
            $payload = $this->signedTimePayload([
                'success' => true,
                'license_type' => 'BASIC',
                'features' => [],
            ], $nonce);
            $payload['server_time_nonce'] = 'different-nonce';
            $payload['server_time_signature'] = hash_hmac(
                'sha256',
                $payload['server_time'] . '|different-nonce',
                config('services.license.signature_secret')
            );

            return Http::response($payload, 200);
        });

        $this->createOnlineVerifiedLicense(now()->subHour());

        $this->assertFalse($this->licenseService->periodicCheck());
    }

    public function test_refresh_license_status_persists_authoritative_server_details(): void
    {
        $license = $this->createOnlineVerifiedLicense(now()->subHour());
        $licenseData = $license->license_data;
        $licenseData['hotel_name'] = 'Stale Hotel';
        $licenseData['license_type'] = 'TRIAL';
        $license->update([
            'license_type' => 'TRIAL',
            'customer_name' => 'Stale Hotel',
            'license_data' => $licenseData,
        ]);

        Http::fake(function ($request) {
            $nonce = (string) data_get($request->data(), 'nonce');

            return Http::response($this->signedTimePayload([
                'success' => true,
                'status' => 'ACTIVE',
                'license_type' => 'PREMIUM',
                'hotel_name' => 'Current Hotel',
                'expires_at' => now()->addYear()->toIso8601String(),
                'features' => ['max_channels' => 200, 'analytics' => true],
                'max_rooms' => 120,
                'max_devices' => 40,
                'max_android_tv' => 25,
                'max_smart_tv' => 10,
                'max_backend' => 3,
                'max_admin_panel' => 2,
                'device_allocation' => [
                    'android_tv' => ['current' => 4, 'max' => 25],
                    'smart_tv' => ['current' => 2, 'max' => 10],
                    'backend' => ['current' => 1, 'max' => 3],
                    'admin_panel' => ['current' => 1, 'max' => 2],
                ],
            ], $nonce), 200);
        });

        $status = $this->licenseService->refreshLicenseStatus();
        $updated = $license->fresh();

        $this->assertTrue($status['licensed']);
        $this->assertFalse($status['status']['offline_mode']);
        $this->assertSame('Current Hotel', $status['status']['hotel_name']);
        $this->assertSame('PREMIUM', $status['status']['license_type']);
        $this->assertSame('Current Hotel', $updated->customer_name);
        $this->assertSame('PREMIUM', $updated->license_type);
        $this->assertSame(120, $status['status']['rooms_limit']);
        $this->assertSame(40, $status['status']['total_limit']);
        $this->assertSame(4, $status['status']['device_allocation'][0]['used']);
        $this->assertSame(25, $status['status']['device_allocation'][0]['limit']);
        $this->assertNotNull($updated->expires_at);
    }

    public function test_offline_grace_is_denied_when_the_license_belongs_to_other_hardware(): void
    {
        Http::fake([
            '*' => Http::response([], 503),
        ]);

        $license = $this->createOnlineVerifiedLicense(now()->subHour());
        $licenseData = $license->license_data;
        $licenseData['hardware_fingerprint'] = 'different-server';
        $license->update(['license_data' => $licenseData]);

        $this->assertFalse($this->licenseService->periodicCheck());
    }

    private function createOnlineVerifiedLicense(Carbon $verifiedAt): License
    {
        $verificationData = $this->licenseService->testOnlineVerificationData($verifiedAt);

        return License::create([
            'license_key' => 'ACTIVE-1234-5678-9012-34567890',
            'license_type' => 'BASIC',
            'product_name' => 'Hotel Management System',
            'customer_name' => 'Verified Hotel',
            'customer_email' => 'verified@example.test',
            'status' => 'active',
            'issued_at' => $verifiedAt,
            'activated_at' => $verifiedAt,
            'last_validated_at' => $verifiedAt,
            'expires_at' => null,
            'license_data' => [
                'license_key' => 'ACTIVE-1234-5678-9012-34567890',
                'license_type' => 'BASIC',
                'status' => 'ACTIVE',
                'token' => 'verified-token',
                'device_id' => TestableLicenseValidationService::DEVICE_ID,
                'hardware_fingerprint' => TestableLicenseValidationService::HARDWARE_FINGERPRINT,
                ...$verificationData,
            ],
        ]);
    }

    private function signedTimePayload(array $body, string $nonce): array
    {
        $serverTime = now()->toIso8601String();

        return [
            ...$body,
            'server_time' => $serverTime,
            'server_time_nonce' => $nonce,
            'server_time_signature' => hash_hmac(
                'sha256',
                $serverTime . '|' . $nonce,
                config('services.license.signature_secret')
            ),
        ];
    }
}

class TestableLicenseValidationService extends LicenseValidationService
{
    public const DEVICE_ID = 'hms-TESTDEVICE0000000000000000';
    public const HARDWARE_FINGERPRINT = 'test-hardware-fingerprint';

    private int $monotonicOffsetSeconds = 0;

    protected function getDeviceId(): string
    {
        return self::DEVICE_ID;
    }

    protected function getHardwareFingerprint(): string
    {
        return self::HARDWARE_FINGERPRINT;
    }

    protected function getBootId(): ?string
    {
        return 'test-boot-id';
    }

    protected function monotonicNanoseconds(): int
    {
        return hrtime(true) + ($this->monotonicOffsetSeconds * 1_000_000_000);
    }

    public function testOnlineVerificationData(Carbon $serverTime): array
    {
        $elapsedSeconds = abs(now()->getTimestamp() - $serverTime->getTimestamp());
        $this->monotonicOffsetSeconds = 0;
        $verificationData = parent::onlineVerificationData($serverTime);
        $this->monotonicOffsetSeconds = $elapsedSeconds;

        return $verificationData;
    }
}