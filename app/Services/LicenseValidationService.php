<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseDevice;
use App\Models\LicenseValidationLog;
use App\Models\Room;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseValidationService
{
    private KewirDevLicenseService $kewirDev;
    private ?string $deviceId = null;
    private bool $lastAttemptWasNetworkError = false;

    public function __construct()
    {
        $this->kewirDev = new KewirDevLicenseService();
    }

    private function currentLicense(): ?License
    {
        return License::query()
            ->whereIn('status', ['active', 'trial'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('last_validated_at')
            ->orderByDesc('activated_at')
            ->orderByDesc('id')
            ->first();
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

    private function buildDeviceInfo(string $licenseKey, ?string $hotelName = null): array
    {
        return [
            'license_key'       => $licenseKey,
            'device_id'         => $this->getDeviceId(),
            'device_type'       => License::DEVICE_TYPE_MANAGEMENT_BACKEND,
            'device_name'       => 'Hotel Management System',
            'device_model'      => 'Server',
            'device_os'         => php_uname('s'),
            'device_os_version' => php_uname('r'),
            'app_version'       => config('app.version', '1.0.0'),
            'mac_address'       => null,
            'ip_address'        => request()?->ip(),
            'metadata'          => [
                'hotel_name'  => $hotelName ?: config('app.name', 'Hotel Management System'),
                'php_version' => PHP_VERSION,
            ],
        ];
    }

    public function validateLicense(string $licenseKey, ?string $hotelName = null, array $extraDeviceInfo = []): array
    {
        $startTime   = microtime(true);
        $deviceInfo  = $this->buildDeviceInfo($licenseKey, $hotelName);

        if (!empty($extraDeviceInfo)) {
            $deviceInfo = array_merge($deviceInfo, array_intersect_key($extraDeviceInfo, array_flip([
                'device_name', 'device_model', 'device_os', 'device_os_version',
                'app_version', 'mac_address', 'metadata', 'device_type', 'device_id',
            ])));
        }

        $this->lastAttemptWasNetworkError = false;

        try {
            $remoteResult    = $this->kewirDev->validateLicense($licenseKey, $deviceInfo);
            $remoteReachable = !str_contains($remoteResult['message'] ?? '', 'unreachable')
                && !str_contains($remoteResult['message'] ?? '', 'error');

            if (!empty($remoteResult['success'])) {
                $license = $this->syncLicenseFromRemote($remoteResult, $licenseKey, $hotelName, $deviceInfo);

                $deviceFingerprint = LicenseDevice::generateFingerprint($deviceInfo);
                $device = $this->handleDeviceBinding($license, $deviceInfo, $deviceFingerprint);

                if (!$device) {
                    return $this->logAndBuild(
                        false,
                        'Device limit reached or device blocked',
                        null,
                        $licenseKey,
                        $deviceInfo,
                        $startTime,
                        LicenseValidationLog::STATUS_BLOCKED
                    );
                }

                $license->updateValidation();
                $device->updateLastSeen();
                Cache::forget('license_valid');

                return $this->logAndBuild(
                    true,
                    'License validated successfully.',
                    $license,
                    $licenseKey,
                    $deviceInfo,
                    $startTime,
                    LicenseValidationLog::STATUS_SUCCESS
                );
            }

            if ($remoteReachable) {
                return $this->logAndBuild(
                    false,
                    $remoteResult['message'] ?? 'Invalid license key.',
                    null,
                    $licenseKey,
                    $deviceInfo,
                    $startTime,
                    LicenseValidationLog::STATUS_INVALID
                );
            }

            $this->lastAttemptWasNetworkError = true;
            Log::info('License server unreachable, falling back to local validation.', [
                'license_key' => $licenseKey,
            ]);
        } catch (\Exception $e) {
            $this->lastAttemptWasNetworkError = true;
            Log::error('License validation error, falling back to local.', ['error' => $e->getMessage()]);
        }

        return $this->validateLocally($licenseKey, $deviceInfo, $startTime);
    }

    private function validateLocally(string $licenseKey, array $deviceInfo, float $startTime): array
    {
        $license = License::where('license_key', $licenseKey)->first();

        if (!$license || !$license->isValid()) {
            $status = $license && $license->isExpired()
                ? LicenseValidationLog::STATUS_EXPIRED
                : LicenseValidationLog::STATUS_INVALID;

            return $this->logAndBuild(
                false,
                'Invalid license key.',
                $license,
                $licenseKey,
                $deviceInfo,
                $startTime,
                $status
            );
        }

        $deviceFingerprint = LicenseDevice::generateFingerprint($deviceInfo);
        $device = $this->handleDeviceBinding($license, $deviceInfo, $deviceFingerprint);

        if (!$device) {
            return $this->logAndBuild(
                false,
                'Device limit reached or device blocked',
                $license,
                $licenseKey,
                $deviceInfo,
                $startTime,
                LicenseValidationLog::STATUS_BLOCKED
            );
        }

        $license->updateValidation();
        $device->updateLastSeen();
        Cache::forget('license_valid');

        return $this->logAndBuild(
            true,
            'License validated successfully (local fallback).',
            $license,
            $licenseKey,
            $deviceInfo,
            $startTime,
            LicenseValidationLog::STATUS_SUCCESS
        );
    }

    private function syncLicenseFromRemote(array $remote, string $licenseKey, ?string $hotelName, array $deviceInfo): License
    {
        $data       = $remote['license'] ?? $remote;
        $features   = (array) ($data['features'] ?? $remote['features'] ?? []);
        $rawType    = strtolower((string) ($data['license_type'] ?? $remote['license_type'] ?? 'basic'));
        $expiresAt  = $this->normalizeExpiry($data['expires_at'] ?? $remote['expires_at'] ?? null, $rawType);
        $maxRooms   = (int) ($features['max_users'] ?? $data['max_rooms'] ?? $remote['max_rooms'] ?? -1);
        $deviceId   = $this->getDeviceId();

        $license = License::updateOrCreate(
            ['license_key' => $licenseKey],
            [
                'hotel_id'             => $data['hotel_id'] ?? $licenseKey,
                'hotel_name'           => $hotelName ?: ($data['hotel_name'] ?? config('app.name')),
                'product_name'         => 'Hotel Management System',
                'customer_name'        => $hotelName ?: ($data['hotel_name'] ?? config('app.name')),
                'customer_email'       => $data['customer_email'] ?? 'admin@hotel.com',
                'license_type'         => $rawType,
                'status'               => License::STATUS_ACTIVE,
                'max_devices'          => (int) ($data['max_devices'] ?? 5),
                'max_rooms'            => $maxRooms,
                'features'             => $features ?: null,
                'expires_at'           => $expiresAt,
                'issued_at'            => $data['issued_at'] ?? now(),
                'activated_at'         => now(),
                'last_validated_at'    => now(),
                'hardware_fingerprint' => $deviceId,
                'license_data'         => [
                    'license_key'       => $licenseKey,
                    'hotel_name'        => $hotelName ?: ($data['hotel_name'] ?? config('app.name')),
                    'license_type'      => strtoupper($rawType),
                    'status'            => 'ACTIVE',
                    'expires_at'        => $expiresAt,
                    'features'          => $features,
                    'max_rooms'         => $maxRooms,
                    'rooms_used'        => Room::count(),
                    'rooms_limit'       => $maxRooms,
                    'total_used'        => Room::count(),
                    'total_limit'       => $maxRooms,
                    'validated_at'      => now()->toISOString(),
                    'token'             => $remote['token'] ?? $data['token'] ?? null,
                    'device_id'         => $deviceId,
                    'token_expires_at'  => $remote['expires_at'] ?? $data['expires_at'] ?? null,
                ],
            ]
        );

        return $license->fresh() ?? $license;
    }

    private function normalizeExpiry(?string $expiresAt, string $licenseType): ?string
    {
        if ($expiresAt === null || $expiresAt === '' || str_contains($expiresAt, 'Never')) {
            return null;
        }

        if (in_array(strtoupper($licenseType), ['PERPETUAL', 'LIFETIME'], true)) {
            return null;
        }

        return $expiresAt;
    }

    private function handleDeviceBinding(License $license, array $deviceInfo, string $deviceFingerprint): ?LicenseDevice
    {
        return DB::transaction(function () use ($license, $deviceInfo, $deviceFingerprint) {
            $license = License::lockForUpdate()->find($license->id);

            $device = LicenseDevice::where('license_id', $license->id)
                ->where('device_fingerprint', $deviceFingerprint)
                ->first();

            if ($device) {
                if ($device->status === LicenseDevice::STATUS_BLOCKED) {
                    return null;
                }
                if (!$device->isActive()) {
                    $device->activate();
                }
                return $device;
            }

            if (!$license->canAddDevice($deviceInfo['device_type'] ?? 'unknown')) {
                return null;
            }

            $device = LicenseDevice::create([
                'license_id'         => $license->id,
                'device_id'          => $deviceInfo['device_id'] ?? '',
                'device_fingerprint' => $deviceFingerprint,
                'device_name'        => $deviceInfo['device_name'] ?? 'Unknown Device',
                'device_type'        => $deviceInfo['device_type'] ?? 'unknown',
                'device_model'       => $deviceInfo['device_model'] ?? '',
                'device_os'          => $deviceInfo['device_os'] ?? '',
                'device_os_version'  => $deviceInfo['device_os_version'] ?? '',
                'app_version'        => $deviceInfo['app_version'] ?? '',
                'ip_address'         => $deviceInfo['ip_address'] ?? request()?->ip(),
                'mac_address'        => $deviceInfo['mac_address'] ?? '',
                'status'             => LicenseDevice::STATUS_ACTIVE,
                'first_activated_at' => now(),
                'last_seen_at'       => now(),
                'activation_count'   => 1,
                'metadata'           => $deviceInfo['metadata'] ?? [],
            ]);

            $license->incrementDeviceCount($deviceInfo['device_type'] ?? 'unknown');

            return $device;
        });
    }

    private function logAndBuild(
        bool $valid,
        string $message,
        ?License $license,
        string $licenseKey,
        array $deviceInfo,
        float $startTime,
        string $status
    ): array {
        $processingTime = microtime(true) - $startTime;

        try {
            LicenseValidationLog::create([
                'license_id'      => $license?->id,
                'device_id'       => $deviceInfo['device_id'] ?? null,
                'validation_type' => LicenseValidationLog::TYPE_INITIAL,
                'status'          => $status,
                'ip_address'      => request()?->ip(),
                'user_agent'      => request()?->userAgent(),
                'request_data'    => [
                    'license_key' => $licenseKey,
                    'device_info' => $deviceInfo,
                ],
                'response_data'   => [
                    'success' => $valid,
                    'message' => $message,
                ],
                'error_message'   => $valid ? null : $message,
                'processing_time' => $processingTime,
                'validated_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Unable to write license validation log: ' . $e->getMessage());
        }

        return [
            'valid'   => $valid,
            'message' => $message,
            'license' => $license && is_array($license->license_data) ? $license->license_data : null,
            'token'   => $license?->license_data['token'] ?? null,
        ];
    }

    public function getLicenseStatus(): array
    {
        $license = $this->currentLicense();

        if (!$license || !is_array($license->license_data)) {
            return ['licensed' => false, 'status' => null];
        }

        $lastValidated = $license->last_validated_at;
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

        return ['licensed' => !$this->isLicenseRowExpired($license), 'status' => $status];
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

        $expired = $this->isLicenseRowExpired($license);
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
        return License::where('status', License::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function periodicCheck(bool $force = false): bool
    {
        $license = $this->currentLicense();

        if (!$license) {
            return false;
        }

        if ($license->isExpired()) {
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

        if (!$force && $license->last_validated_at && $license->last_validated_at->gt(now()->subDays(7))) {
            return true;
        }

        $result = $this->validateLicense($license->license_key, $license->hotel_name);

        if ($result['valid']) {
            return true;
        }

        if ($this->lastAttemptWasNetworkError && $license->last_validated_at) {
            Log::info('License server unreachable; using stored verification (grace period active).');
            return true;
        }

        return false;
    }

    public function validateToken(): array
    {
        $license = License::where('status', 'active')->latest('id')->first();

        if (!$license || !is_array($license->license_data)) {
            return ['valid' => false, 'message' => 'No active license found.'];
        }

        $token = $license->license_data['token'] ?? null;

        if (!$token) {
            return ['valid' => false, 'message' => 'No license token available.'];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 20])
                ->asJson()
                ->post(rtrim(config('license.api.base_url', 'https://kewirdev.com/api/license'), '/') . '/validate-token', [
                    'token' => $token,
                ]);

            if (!$response->successful()) {
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

        $token = $license->license_data['token'] ?? null;

        if (!$token) {
            return ['success' => false, 'message' => 'No token to refresh.'];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 20])
                ->asJson()
                ->post(rtrim(config('license.api.base_url', 'https://kewirdev.com/api/license'), '/') . '/refresh-token', [
                    'token'     => $token,
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

        $token = $license->license_data['token'] ?? null;
        if (!$token) {
            return ['success' => false, 'message' => 'No token available for heartbeat.'];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 20])
                ->asJson()
                ->post(rtrim(config('license.api.base_url', 'https://kewirdev.com/api/license'), '/') . '/heartbeat', [
                    'token'     => $token,
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
            $response = Http::withOptions(['verify' => false, 'timeout' => 20])
                ->get(rtrim(config('license.api.base_url', 'https://kewirdev.com/api/license'), '/') . '/info', [
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

        if (!$license) {
            return false;
        }

        $license->update(['assigned_rooms' => $roomCount]);

        $token = $license->license_data['token'] ?? null;
        if (!$token) {
            return false;
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 20])
                ->asJson()
                ->post(rtrim(config('license.api.base_url', 'https://kewirdev.com/api/license'), '/') . '/sync-rooms', [
                    'token'     => $token,
                    'rooms'     => $roomCount,
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
    }

    private function isLicenseRowExpired(License $license): bool
    {
        if ($license->status === 'active') {
            return false;
        }

        return (bool) ($license->expires_at && now()->greaterThan($license->expires_at));
    }
}
