<?php

namespace App\Services;

use App\Models\License;
use App\Models\Room;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseValidationService
{
    private string $licenseServer;
    private ?string $jwtToken = null;
    private ?string $deviceId = null;

    public function __construct()
    {
        $this->licenseServer = rtrim(env('LICENSE_SERVER_URL', 'https://kewirdev.com/api/license'), '/');
        $this->loadStoredToken();
    }

    private function currentLicense(): ?License
    {
        return License::whereIn('status', ['active', 'trial'])
            ->latest('id')
            ->first();
    }

    private function loadStoredToken(): void
    {
        $license = $this->currentLicense();

        if ($license && is_array($license->license_data)) {
            $this->jwtToken = $license->license_data['token'] ?? null;
            $this->deviceId = $license->license_data['device_id'] ?? null;
        }
    }

    private function getDeviceId(): string
    {
        if ($this->deviceId) {
            return $this->deviceId;
        }

        $path = storage_path('app/hms_device_id');

        if (file_exists($path)) {
            $stored = trim((string) file_get_contents($path));
            if ($stored !== '') {
                $this->deviceId = $stored;
                return $stored;
            }
        }

        $generated = 'hms-' . Str::uuid()->toString();

        try {
            file_put_contents($path, $generated);
        } catch (\Throwable $e) {
            Log::warning('Unable to persist device id: ' . $e->getMessage());
        }

        $this->deviceId = $generated;
        return $generated;
    }

    private function signatureSecret(): string
    {
        return (string) (config('services.license.signature_secret')
            ?: env('LICENSE_SIGNATURE_SECRET')
            ?: env('LICENSE_JWT_SECRET')
            ?: config('app.key', ''));
    }

    private function computeSignature(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload), $this->signatureSecret());
    }

    private function buildValidatePayload(string $licenseKey, ?string $hotelName = null): array
    {
        return [
            'license_key'       => $licenseKey,
            'device_id'         => $this->getDeviceId(),
            'device_type'       => 'management_backend',
            'device_name'       => 'Hotel Management System',
            'device_model'      => 'Server',
            'device_os'         => php_uname('s'),
            'device_os_version' => php_uname('r'),
            'app_version'       => config('app.version', '1.0.0'),
            'mac_address'       => null,
            'metadata'          => [
                'hotel_name'  => $hotelName ?: config('app.name', 'Hotel Management System'),
                'php_version' => PHP_VERSION,
            ],
        ];
    }

    private function client()
    {
        return Http::withOptions([
            'verify' => false,
            'timeout' => 20,
            'connect_timeout' => 10,
        ])->asJson()->withHeaders([
            'User-Agent' => 'HotelManagementSystem/1.0.0',
            'Accept' => 'application/json',
        ]);
    }

    public function validateLicense(string $licenseKey, ?string $hotelName = null, array $deviceInfo = []): array
    {
        $payload = $this->buildValidatePayload($licenseKey, $hotelName);

        if (!empty($deviceInfo)) {
            $payload = array_merge($payload, array_intersect_key($deviceInfo, array_flip([
                'device_name', 'device_model', 'device_os', 'device_os_version', 'app_version', 'mac_address', 'metadata', 'device_type', 'device_id'
            ])));
        }

        try {
            $response = $this->client()
                ->withHeaders(['X-License-Signature' => $this->computeSignature($payload)])
                ->post($this->licenseServer . '/validate', $payload);

            if (!$response->successful()) {
                Log::warning('License validation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->mapHttpError($response->status(), (array) ($response->json() ?? []));
            }

            $body = (array) ($response->json() ?? []);

            if (!($body['success'] ?? false)) {
                return [
                    'valid' => false,
                    'message' => $body['message'] ?? $body['error'] ?? 'License validation failed.',
                ];
            }

            return $this->persistValidatedLicense($licenseKey, $hotelName, $body);
        } catch (\Throwable $e) {
            Log::error('License validation exception: ' . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'Could not reach the license server. Please try again.',
            ];
        }
    }

    private function persistValidatedLicense(string $licenseKey, ?string $hotelName, array $serverResponse): array
    {
        $features = (array) ($serverResponse['features'] ?? []);
        $maxRooms = (int) ($features['max_users'] ?? $serverResponse['max_rooms'] ?? -1);
        $deviceId = $this->getDeviceId();

        $licenseData = [
            'license_key' => $licenseKey,
            'hotel_name' => $hotelName ?: ($serverResponse['hotel_name'] ?? config('app.name')),
            'license_type' => strtoupper((string) ($serverResponse['license_type'] ?? 'BASIC')),
            'status' => strtoupper((string) ($serverResponse['status'] ?? 'ACTIVE')),
            'expires_at' => $serverResponse['expires_at'] ?? null,
            'features' => $features,
            'max_rooms' => $maxRooms,
            'rooms_used' => Room::count(),
            'rooms_limit' => $maxRooms,
            'device_allocation' => [
                ['type' => 'Rooms', 'used' => Room::count(), 'limit' => $maxRooms],
            ],
            'total_used' => Room::count(),
            'total_limit' => $maxRooms,
            'validated_at' => now()->toISOString(),
            'token' => $serverResponse['token'] ?? null,
            'device_id' => $deviceId,
            'token_expires_at' => $serverResponse['token_expires_at'] ?? $serverResponse['expires_at'] ?? null,
        ];

        License::updateOrCreate(
            ['license_key' => $licenseKey],
            [
                'license_data' => $licenseData,
                'product_name' => 'Hotel Management System',
                'customer_name' => $licenseData['hotel_name'],
                'customer_email' => 'admin@hotel.com',
                'license_type' => $licenseData['license_type'],
                'status' => 'active',
                'issued_at' => now(),
                'activated_at' => now(),
                'last_validated_at' => now(),
                'expires_at' => $licenseData['expires_at'],
                'hardware_fingerprint' => $deviceId,
                'max_rooms' => $maxRooms,
            ]
        );

        $this->jwtToken = $licenseData['token'];
        $this->deviceId = $deviceId;
        Cache::forget('license_valid');

        return [
            'valid' => true,
            'message' => 'License activated successfully.',
            'license' => $licenseData,
            'token' => $licenseData['token'],
        ];
    }

    private function mapHttpError(int $status, array $body): array
    {
        if ($status === 429) {
            return ['valid' => false, 'message' => 'Too many validation attempts. Please wait and try again.'];
        }

        if ($status === 403) {
            $reason = (string) ($body['reason'] ?? 'access_denied');
            if (str_contains($reason, 'rate_limiting')) {
                return ['valid' => false, 'message' => 'License rejected by server (Failed security checks: rate_limiting). Contact KewirDev support.'];
            }
            return ['valid' => false, 'message' => 'License rejected by server (' . $reason . '). Contact KewirDev support.'];
        }

        if ($status === 404) {
            return ['valid' => false, 'message' => 'License key not found.'];
        }

        if ($status === 401) {
            return ['valid' => false, 'message' => 'Invalid license key.'];
        }

        if ($status === 400) {
            return ['valid' => false, 'message' => $body['error'] ?? $body['message'] ?? 'Invalid request.'];
        }

        return ['valid' => false, 'message' => $body['error'] ?? $body['message'] ?? ('License server error (HTTP ' . $status . ').')];
    }

    public function getLicenseStatus(): array
    {
        $license = $this->currentLicense();

        if (!$license || !is_array($license->license_data)) {
            return ['licensed' => false, 'status' => null];
        }

        $lastValidated = $license->last_validated_at;
        // Consider "verified online" if validated within the last 30 days
        $verifiedOnline = $lastValidated && $lastValidated->gt(now()->subDays(30));

        $status = array_merge($license->license_data, [
            'license_key'       => $license->license_key,
            'license_type'      => $license->license_type,
            'status'            => strtoupper((string) ($license->license_data['status'] ?? $license->status ?? 'ACTIVE')),
            'expires_at'        => optional($license->expires_at)->toISOString() ?? ($license->license_data['expires_at'] ?? null),
            'validated_at'      => optional($lastValidated)->toISOString(),
            'verified_online'   => $verifiedOnline,
            'online_verified_at'=> optional($lastValidated)->toISOString(),
        ]);

        return ['licensed' => !$this->licenseExpired($license), 'status' => $status];
    }

    public function getTrialStatus(): array
    {
        $license = License::where('status', 'trial')->latest('id')->first();

        if (!$license) {
            return [
                'in_trial' => false,
                'days_remaining' => 0,
                'expires_at' => null,
                'expired' => false,
            ];
        }

        $expired = $this->licenseExpired($license);
        $daysRemaining = $license->expires_at
            ? max(0, now()->startOfDay()->diffInDays($license->expires_at->copy()->startOfDay(), false))
            : 0;

        return [
            'in_trial' => !$expired,
            'days_remaining' => $daysRemaining,
            'expires_at' => optional($license->expires_at)->format('Y-m-d'),
            'expired' => $expired,
        ];
    }

    public function isSystemLicensed(): bool
    {
        $license = $this->currentLicense();

        if (!$license) {
            return false;
        }

        if ($this->licenseExpired($license)) {
            if ($license->status !== 'expired') {
                $license->update(['status' => 'expired']);
            }
            return false;
        }

        if ($license->status === 'trial') {
            return true;
        }

        return $this->periodicCheck();
    }

    public function periodicCheck(bool $force = false): bool
    {
        $license = $this->currentLicense();

        if (!$license) {
            return false;
        }

        if ($this->licenseExpired($license)) {
            if ($license->status !== 'expired') {
                $license->update(['status' => 'expired']);
            }
            return false;
        }

        if ($license->status === 'trial') {
            if (!$force && $license->last_validated_at && $license->last_validated_at->gt(now()->subDay())) {
                return true;
            }

            $license->update(['last_validated_at' => now()]);
            return true;
        }

        // Only re-verify online once every 7 days — not every 15 minutes.
        // If already validated within the last 7 days, trust the stored result.
        if (!$force && $license->last_validated_at && $license->last_validated_at->gt(now()->subDays(7))) {
            return true;
        }

        $result = $this->validateToken();

        if ($result['valid']) {
            $license->update(['last_validated_at' => now()]);
            return true;
        }

        // If the failure was a network/connectivity error (not a deliberate server rejection),
        // grant a 30-day offline grace period so users are not locked out due to server downtime.
        if (!empty($result['network_error'])) {
            if ($license->last_validated_at && $license->last_validated_at->gt(now()->subDays(30))) {
                Log::info('License server unreachable; using stored verification (grace period active).');
                return true;
            }
        }

        return false;
    }

    public function validateToken(): array
    {
        $license = License::where('status', 'active')->latest('id')->first();

        if (!$license || !is_array($license->license_data)) {
            return ['valid' => false, 'message' => 'No active license found.'];
        }

        $token = $license->license_data['token'] ?? $this->jwtToken;

        if (!$token) {
            return ['valid' => false, 'message' => 'No license token available.'];
        }

        try {
            $response = $this->client()->post($this->licenseServer . '/validate-token', [
                'token' => $token,
            ]);

            if (!$response->successful()) {
                // 4xx = server explicitly rejected — treat as hard failure
                // 5xx = server-side error — treat as transient network issue
                $networkError = $response->status() >= 500;
                return ['valid' => false, 'network_error' => $networkError, 'message' => 'Token validation failed.'];
            }

            $body = (array) ($response->json() ?? []);

            if (!($body['success'] ?? false)) {
                return ['valid' => false, 'network_error' => false, 'message' => $body['error'] ?? $body['message'] ?? 'Token invalid.'];
            }

            return [
                'valid' => true,
                'network_error' => false,
                'license_type' => $body['license_type'] ?? ($license->license_data['license_type'] ?? null),
                'features' => $body['features'] ?? ($license->license_data['features'] ?? []),
                'expires_at' => $body['expires_at'] ?? ($license->license_data['expires_at'] ?? null),
            ];
        } catch (\Throwable $e) {
            // Connection refused, timeout, DNS failure, etc. — not a deliberate rejection
            Log::warning('Token validation network error: ' . $e->getMessage());
            return ['valid' => false, 'network_error' => true, 'message' => 'Could not reach license server.'];
        }
    }

    public function refreshToken(): array
    {
        $license = License::where('status', 'active')->latest('id')->first();

        if (!$license || !is_array($license->license_data)) {
            return ['success' => false, 'message' => 'No active license found.'];
        }

        $token = $license->license_data['token'] ?? $this->jwtToken;

        if (!$token) {
            return ['success' => false, 'message' => 'No token to refresh.'];
        }

        try {
            $response = $this->client()->post($this->licenseServer . '/refresh-token', [
                'token' => $token,
                'device_id' => $license->license_data['device_id'] ?? $this->getDeviceId(),
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Failed to refresh token.'];
            }

            $body = (array) ($response->json() ?? []);

            if (!($body['success'] ?? false) || empty($body['token'])) {
                return ['success' => false, 'message' => $body['error'] ?? $body['message'] ?? 'Invalid refresh response.'];
            }

            $licenseData = $license->license_data;
            $licenseData['token'] = $body['token'];
            $licenseData['token_expires_at'] = $body['expires_at'] ?? null;
            $license->update([
                'license_data' => $licenseData,
                'last_validated_at' => now(),
            ]);

            $this->jwtToken = $body['token'];

            return [
                'success' => true,
                'token' => $body['token'],
                'expires_at' => $body['expires_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Token refresh exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not refresh token online.'];
        }
    }

    public function sendHeartbeat(): array
    {
        $license = License::where('status', 'active')->latest('id')->first();

        if (!$license || !is_array($license->license_data)) {
            return ['success' => false, 'message' => 'No active license found.'];
        }

        $token = $license->license_data['token'] ?? $this->jwtToken;
        if (!$token) {
            return ['success' => false, 'message' => 'No token available for heartbeat.'];
        }

        try {
            $response = $this->client()->post($this->licenseServer . '/heartbeat', [
                'token' => $token,
                'device_id' => $license->license_data['device_id'] ?? $this->getDeviceId(),
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Heartbeat failed.'];
            }

            $body = (array) ($response->json() ?? []);

            return [
                'success' => (bool) ($body['success'] ?? false),
                'message' => $body['message'] ?? 'Heartbeat sent.',
                'server_time' => $body['server_time'] ?? now()->toISOString(),
                'next_heartbeat' => $body['next_heartbeat'] ?? now()->addMinute()->toISOString(),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Could not send heartbeat.'];
        }
    }

    public function getLicenseInfo(string $licenseKey): array
    {
        try {
            $response = $this->client()->get($this->licenseServer . '/info', [
                'license_key' => $licenseKey,
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'License info not found.'];
            }

            $body = (array) ($response->json() ?? []);

            if (!($body['success'] ?? false)) {
                return ['success' => false, 'message' => $body['error'] ?? $body['message'] ?? 'License info not found.'];
            }

            return ['success' => true, 'data' => $body['data'] ?? $body];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Could not fetch license information.'];
        }
    }

    public function getLicenseLimits(): array
    {
        $license = $this->currentLicense();

        if (!$license || !is_array($license->license_data)) {
            return ['max_rooms' => -1];
        }

        $max = $license->license_data['max_rooms']
            ?? $license->license_data['rooms_limit']
            ?? $license->max_rooms
            ?? -1;

        return ['max_rooms' => (int) $max];
    }

    public function syncRooms(int $roomCount): bool
    {
        $license = License::where('status', 'active')->latest('id')->first();

        if (!$license || !is_array($license->license_data)) {
            return false;
        }

        $token = $license->license_data['token'] ?? $this->jwtToken;
        if (!$token) {
            return false;
        }

        try {
            $response = $this->client()->post($this->licenseServer . '/sync-rooms', [
                'token' => $token,
                'rooms' => $roomCount,
                'device_id' => $license->license_data['device_id'] ?? $this->getDeviceId(),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Room sync failed: ' . $e->getMessage());
            return false;
        }
    }

    public function removeLicense(): void
    {
        License::whereIn('status', ['active', 'trial'])->update(['status' => 'inactive']);
        Cache::forget('license_valid');
        $this->jwtToken = null;
    }

    private function licenseExpired(License $license): bool
    {
        return (bool) ($license->expires_at && now()->greaterThan($license->expires_at));
    }
}
