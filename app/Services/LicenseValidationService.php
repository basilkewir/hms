<?php

namespace App\Services;

use App\Models\License;
use App\Models\Room;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class LicenseValidationService
{
    private string $licenseServer;
    private ?string $jwtToken = null;
    private ?string $deviceId = null;
    private ?string $hardwareFingerprint = null;

    public function __construct()
    {
        $this->licenseServer = rtrim((string) config('services.license.server_url'), '/');
        $this->loadStoredToken();
    }

    private function verifiedServerTime(array $response, string $nonce): ?Carbon
    {
        $serverTime = $response['server_time'] ?? null;
        $responseNonce = $response['server_time_nonce'] ?? null;
        $signature = $response['server_time_signature'] ?? null;

        if (
            !is_string($serverTime)
            || !is_string($responseNonce)
            || !is_string($signature)
            || !hash_equals($nonce, $responseNonce)
        ) {
            return null;
        }

        $secret = $this->signatureSecret();
        if ($secret !== null) {
            $expectedSignature = hash_hmac('sha256', $serverTime . '|' . $nonce, $secret);
            if (!hash_equals($expectedSignature, $signature)) {
                return null;
            }
        }

        try {
            return Carbon::parse($serverTime);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function clockMatchesServer(Carbon $serverTime): bool
    {
        return abs(now()->diffInSeconds($serverTime)) <= 300;
    }

    protected function onlineVerificationData(Carbon $serverTime): array
    {
        return [
            'validated_at' => $serverTime->toIso8601String(),
            'online_verified_at' => $serverTime->toIso8601String(),
            'online_verified_boot_id' => $this->getBootId(),
            'online_verified_monotonic_ns' => (string) $this->monotonicNanoseconds(),
        ];
    }

    protected function getBootId(): ?string
    {
        $path = '/proc/sys/kernel/random/boot_id';
        if (is_readable($path)) {
            $bootId = trim((string) @file_get_contents($path));
            if ($bootId !== '') {
                return $bootId;
            }
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            try {
                $process = new Process(['sysctl', '-n', 'kern.boottime']);
                $process->setTimeout(5);
                $process->run();

                if ($process->isSuccessful() && preg_match('/\bsec\s*=\s*(\d+)\b/', $process->getOutput(), $matches)) {
                    return 'darwin-boot-' . $matches[1];
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to determine the macOS boot identity for offline license checks.');
            }
        }

        return null;
    }

    protected function monotonicNanoseconds(): int
    {
        return hrtime(true);
    }

    private function licenseMatchesCurrentMachine(License $license): bool
    {
        $licenseData = $license->license_data;
        if (!is_array($licenseData)) {
            return false;
        }

        $registeredDeviceId = $licenseData['device_id'] ?? null;
        $registeredFingerprint = $licenseData['hardware_fingerprint'] ?? null;

        try {
            $currentDeviceId = $this->getDeviceId();
            $currentFingerprint = $this->getHardwareFingerprint();
        } catch (\Throwable $e) {
            Log::error('Unable to verify the current license hardware identity: ' . $e->getMessage());
            return false;
        }

        $matchesDeviceId = is_string($registeredDeviceId)
            && hash_equals($registeredDeviceId, $currentDeviceId);

        // Existing installations predate hardware fingerprints. Permit them to
        // reconnect online once; successful validation stores the new binding.
        if ($matchesDeviceId && !is_string($registeredFingerprint)) {
            return true;
        }

        $matches = $matchesDeviceId
            && hash_equals((string) $registeredFingerprint, $currentFingerprint);

        if (!$matches) {
            Log::warning('Stored license is not bound to this server’s hardware. Reactivation is required.');
        }

        return $matches;
    }

    private function isTrialLicense(License $license): bool
    {
        $licenseType = strtoupper((string) ($license->license_type ?? ''));
        $dataType = strtoupper((string) ($license->license_data['license_type'] ?? ''));
        $dataStatus = strtoupper((string) ($license->license_data['status'] ?? ''));

        return $license->status === 'trial'
            || $licenseType === 'TRIAL'
            || $dataType === 'TRIAL'
            || $dataStatus === 'TRIAL';
    }

    private function trustedCurrentTime(License $license): Carbon
    {
        $licenseData = is_array($license->license_data) ? $license->license_data : [];
        $verifiedAt = $licenseData['online_verified_at'] ?? null;
        $verifiedBootId = $licenseData['online_verified_boot_id'] ?? null;
        $verifiedMonotonic = filter_var(
            $licenseData['online_verified_monotonic_ns'] ?? null,
            FILTER_VALIDATE_INT
        );
        $currentBootId = $this->getBootId();

        if (
            is_string($verifiedAt)
            && is_string($verifiedBootId)
            && $currentBootId !== null
            && hash_equals($verifiedBootId, $currentBootId)
            && $verifiedMonotonic !== false
        ) {
            try {
                $elapsedNanoseconds = $this->monotonicNanoseconds() - $verifiedMonotonic;
                if ($elapsedNanoseconds >= 0) {
                    return Carbon::parse($verifiedAt)
                        ->addMilliseconds((int) floor($elapsedNanoseconds / 1_000_000));
                }
            } catch (\Throwable $e) {
                Log::warning('Stored trusted license time is invalid.');
            }
        }

        return now();
    }

    private function isSystemClockConsistent(License $license): bool
    {
        $licenseData = is_array($license->license_data) ? $license->license_data : [];
        $verifiedBootId = $licenseData['online_verified_boot_id'] ?? null;
        $currentBootId = $this->getBootId();

        if (
            !is_string($verifiedBootId)
            || $currentBootId === null
            || !hash_equals($verifiedBootId, $currentBootId)
        ) {
            return true;
        }

        return abs(now()->diffInSeconds($this->trustedCurrentTime($license))) <= 300;
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

    private function normalizePersistedExpiry(array $serverResponse): ?string
    {
        $status = strtoupper((string) ($serverResponse['status'] ?? 'ACTIVE'));
        $licenseType = strtoupper((string) ($serverResponse['license_type'] ?? ''));
        $expiresAt = $serverResponse['expires_at'] ?? null;

        if ($expiresAt === null || $expiresAt === '' || $expiresAt === 'Never Expires') {
            return null;
        }

        if (in_array($licenseType, ['PERPETUAL', 'LIFETIME'], true)) {
            return null;
        }

        return $expiresAt;
    }

    private function loadStoredToken(): void
    {
        $license = $this->currentLicense();

        if ($license && is_array($license->license_data)) {
            $this->jwtToken = $license->license_data['token'] ?? null;
        }
    }

    protected function getHardwareFingerprint(): string
    {
        if ($this->hardwareFingerprint !== null) {
            return $this->hardwareFingerprint;
        }

        // Docker recreates containers with a new hostname and MAC address. Keep
        // the first hardware identity in the persistent storage volume so a
        // normal container restart does not invalidate an activated license.
        $identityPath = storage_path('app/hms_device_id');
        if (is_readable($identityPath)) {
            $stored = trim((string) @file_get_contents($identityPath));
            $parts = explode('|', $stored, 2);
            if (count($parts) === 2 && preg_match('/^[a-f0-9]{64}$/', $parts[0])) {
                $this->hardwareFingerprint = $parts[0];
                return $this->hardwareFingerprint;
            }
        }

        $fingerprintPath = storage_path('app/hms_hardware_fingerprint');
        if (is_readable($fingerprintPath)) {
            $stored = trim((string) @file_get_contents($fingerprintPath));
            if (preg_match('/^[a-f0-9]{64}$/', $stored)) {
                $this->hardwareFingerprint = $stored;
                return $this->hardwareFingerprint;
            }
        }

        $identifiers = [];
        $identifierFiles = [
            'machine_id' => ['/etc/machine-id', '/var/lib/dbus/machine-id'],
            'product_uuid' => ['/sys/class/dmi/id/product_uuid'],
            'board_serial' => ['/sys/class/dmi/id/board_serial'],
            'product_serial' => ['/sys/class/dmi/id/product_serial'],
        ];

        foreach ($identifierFiles as $name => $candidates) {
            foreach ($candidates as $candidate) {
                if (is_readable($candidate)) {
                    $value = strtolower(trim((string) @file_get_contents($candidate)));
                    if ($value !== '' && !in_array($value, ['none', 'unknown', 'not specified', 'to be filled by o.e.m.'], true)) {
                        $identifiers[$name] = $value;
                    }
                    break;
                }
            }
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            try {
                $process = new Process(['ioreg', '-rd1', '-c', 'IOPlatformExpertDevice']);
                $process->setTimeout(5);
                $process->run();

                if ($process->isSuccessful()) {
                    foreach ([
                        'platform_uuid' => 'IOPlatformUUID',
                        'platform_serial' => 'IOPlatformSerialNumber',
                    ] as $name => $property) {
                        if (preg_match('/"' . preg_quote($property, '/') . '"\s*=\s*"([^"]+)"/', $process->getOutput(), $matches)) {
                            $identifiers[$name] = strtolower(trim($matches[1]));
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to inspect macOS hardware identifiers for license binding.');
            }
        }

        $macAddresses = [];
        foreach (glob('/sys/class/net/*/address') ?: [] as $addressFile) {
            if (basename(dirname($addressFile)) === 'lo' || !is_readable($addressFile)) {
                continue;
            }

            $mac = strtolower(trim((string) @file_get_contents($addressFile)));
            if (preg_match('/^(?:[0-9a-f]{2}:){5}[0-9a-f]{2}$/', $mac) && $mac !== '00:00:00:00:00:00') {
                $macAddresses[] = $mac;
            }
        }

        if (function_exists('net_get_interfaces')) {
            try {
                foreach (net_get_interfaces() ?: [] as $interface) {
                    $mac = strtolower((string) ($interface['mac'] ?? ''));
                    if (preg_match('/^(?:[0-9a-f]{2}:){5}[0-9a-f]{2}$/', $mac) && $mac !== '00:00:00:00:00:00') {
                        $macAddresses[] = $mac;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to inspect network hardware for license binding.');
            }
        }

        if (PHP_OS_FAMILY === 'Darwin' && $macAddresses === []) {
            try {
                $process = new Process(['ifconfig', '-a']);
                $process->setTimeout(5);
                $process->run();

                if ($process->isSuccessful()) {
                    preg_match_all('/^\s*ether ((?:[0-9a-f]{2}:){5}[0-9a-f]{2})\s*$/im', $process->getOutput(), $matches);
                    $macAddresses = array_map('strtolower', $matches[1] ?? []);
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to inspect network hardware for license binding.');
            }
        }

        $macAddresses = array_values(array_unique($macAddresses));
        sort($macAddresses);

        if ($identifiers === [] && $macAddresses === []) {
            throw new \RuntimeException('Unable to identify this server hardware for license verification.');
        }

        $this->hardwareFingerprint = hash('sha256', json_encode([
            'identifiers' => $identifiers,
            'network_macs' => $macAddresses,
            'hostname' => strtolower(gethostname() ?: 'unknown-host'),
            'os' => strtolower(php_uname('s')),
            'architecture' => strtolower(php_uname('m')),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        @file_put_contents($fingerprintPath, $this->hardwareFingerprint, LOCK_EX);
        @chmod($fingerprintPath, 0600);

        return $this->hardwareFingerprint;
    }

    protected function getDeviceId(): string
    {
        $hardwareHash = $this->getHardwareFingerprint();
        if ($this->deviceId !== null) {
            return $this->deviceId;
        }

        $path = storage_path('app/hms_device_id');

        if (file_exists($path)) {
            $stored = trim((string) @file_get_contents($path));
            $parts = explode('|', $stored, 2);

            if (count($parts) === 2 && hash_equals($parts[0], $hardwareHash)) {
                $this->deviceId = $parts[1];
                return $this->deviceId;
            }

            if (
                count($parts) === 1
                && preg_match('/\Ahms-[A-Z0-9]{24}\z/', $stored)
            ) {
                $this->persistDeviceIdentity($path, $hardwareHash, $stored);
                $this->deviceId = $stored;
                return $this->deviceId;
            }
        }

        $legacyDeviceId = $this->currentLicense()?->license_data['device_id'] ?? null;
        $deviceId = is_string($legacyDeviceId)
            && preg_match('/\Ahms-[A-Z0-9]{24}\z/', $legacyDeviceId)
            ? $legacyDeviceId
            : 'hms-' . Str::upper(Str::random(24));

        try {
            $this->persistDeviceIdentity($path, $hardwareHash, $deviceId);
        } catch (\Throwable $e) {
            Log::error('Unable to persist the license device identity: ' . $e->getMessage());
            throw new \RuntimeException('Unable to persist the license device identity.', 0, $e);
        }

        $this->deviceId = $deviceId;
        return $deviceId;
    }

    private function persistDeviceIdentity(string $path, string $hardwareHash, string $deviceId): void
    {
        if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0700, true) && !is_dir(dirname($path))) {
            throw new \RuntimeException('Unable to create the license identity directory.');
        }

        if (@file_put_contents($path, $hardwareHash . '|' . $deviceId, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to save the license device identity.');
        }

        @chmod($path, 0600);
    }

    private function signatureSecret(): ?string
    {
        // Only the dedicated shared secret may sign/verify license traffic.
        // Never fall back to APP_KEY or a local JWT secret.
        $secret = config('services.license.signature_secret') ?: env('LICENSE_SIGNATURE_SECRET');
        return is_string($secret) && $secret !== '' ? $secret : null;
    }

    private function computeSignature(array $payload): ?string
    {
        $secret = $this->signatureSecret();
        return $secret !== null ? hash_hmac('sha256', json_encode($payload), $secret) : null;
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
                'hardware_fingerprint' => $this->getHardwareFingerprint(),
                'license_nonce' => Str::random(48),
            ],
        ];
    }

    private function client()
    {
        return Http::withOptions([
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
        $nonce = $payload['metadata']['license_nonce'];

        if (!empty($deviceInfo)) {
            $payload = array_merge($payload, array_intersect_key($deviceInfo, array_flip([
                'device_name', 'device_model', 'device_os', 'device_os_version', 'app_version', 'mac_address'
            ])));
            $payload['metadata'] = array_merge(
                $payload['metadata'],
                (array) ($deviceInfo['metadata'] ?? [])
            );
            $payload['metadata']['hardware_fingerprint'] = $this->getHardwareFingerprint();
            $payload['metadata']['license_nonce'] = $nonce;
        }

        try {
            $headers = [];
            $signature = $this->computeSignature($payload);
            if ($signature !== null) {
                $headers['X-License-Signature'] = $signature;
            }
            $response = $this->client()
                ->withHeaders($headers)
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

            $serverTime = $this->verifiedServerTime($body, $nonce);
            if (!$serverTime) {
                return ['valid' => false, 'message' => 'The license server time signature could not be verified.'];
            }

            if (!$this->clockMatchesServer($serverTime)) {
                return ['valid' => false, 'message' => 'Correct this server’s date and time before activating the license.'];
            }

            return $this->persistValidatedLicense($licenseKey, $hotelName, $body, $serverTime);
        } catch (ConnectionException $e) {
            Log::warning('License server connection failed during activation: ' . $e->getMessage());
            return [
                'valid' => false,
                'network_error' => true,
                'message' => 'Could not reach the license server. Please try again.',
            ];
        } catch (\Throwable $e) {
            Log::error('License activation could not be completed', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'network_error' => false,
                'message' => 'License validation could not be completed because of a local system error. Please check the server logs or contact support.',
            ];
        }
    }

    private function persistValidatedLicense(
        string $licenseKey,
        ?string $hotelName,
        array $serverResponse,
        Carbon $serverTime
    ): array
    {
        $features = (array) ($serverResponse['features'] ?? []);
        $maxRooms = (int) ($serverResponse['max_rooms']
            ?? $serverResponse['max_backend']
            ?? $features['max_rooms']
            ?? $features['max_users']
            ?? 0);
        $maxDevices = (int) ($serverResponse['max_devices']
            ?? $features['max_devices']
            ?? $features['max_tv_devices']
            ?? 0);
        $deviceAllocation = (array) ($serverResponse['device_allocation'] ?? []);
        $maxAndroidTv = (int) ($serverResponse['max_android_tv']
            ?? data_get($deviceAllocation, 'android_tv.max', 0));
        $maxSmartTv = (int) ($serverResponse['max_smart_tv']
            ?? data_get($deviceAllocation, 'smart_tv.max', 0));
        $maxBackend = (int) ($serverResponse['max_backend']
            ?? data_get($deviceAllocation, 'backend.max', 0));
        $maxAdminPanel = (int) ($serverResponse['max_admin_panel']
            ?? data_get($deviceAllocation, 'admin_panel.max', 0));
        $maxChannels = (int) ($serverResponse['max_channels']
            ?? $features['max_channels']
            ?? 0);
        $roomsUsed = Room::count();
        $currentDevices = array_sum(array_map(
            static fn ($allocation): int => is_array($allocation) ? (int) ($allocation['current'] ?? 0) : 0,
            $deviceAllocation
        ));
        $deviceId = $this->getDeviceId();
        $hardwareFingerprint = $this->getHardwareFingerprint();

        $persistedExpiry = $this->normalizePersistedExpiry($serverResponse);

        $licenseData = [
            'license_key' => $licenseKey,
            'hotel_name' => $hotelName ?: ($serverResponse['hotel_name'] ?? config('app.name')),
            'license_type' => strtoupper((string) ($serverResponse['license_type'] ?? 'BASIC')),
            'status' => strtoupper((string) ($serverResponse['status'] ?? 'ACTIVE')),
            'expires_at' => $serverResponse['expires_at'] ?? null,
            'features' => $features,
            'max_rooms' => $maxRooms,
            'max_devices' => $maxDevices,
            'max_android_tv' => $maxAndroidTv,
            'max_smart_tv' => $maxSmartTv,
            'max_backend' => $maxBackend,
            'max_admin_panel' => $maxAdminPanel,
            'max_channels' => $maxChannels,
            'rooms_used' => $roomsUsed,
            'rooms_limit' => $maxRooms,
            'device_limit_usage' => $deviceAllocation,
            'device_allocation' => $this->deviceAllocationRows($deviceAllocation, $roomsUsed, $maxRooms),
            'device_usage' => array_map(
                static fn ($allocation): int => is_array($allocation) ? (int) ($allocation['current'] ?? 0) : 0,
                $deviceAllocation
            ),
            'total_used' => $currentDevices,
            'total_limit' => $maxDevices,
            ...$this->onlineVerificationData($serverTime),
            'token' => $serverResponse['token'] ?? null,
            'device_id' => $deviceId,
            'hardware_fingerprint' => $hardwareFingerprint,
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
                'last_validated_at' => $serverTime,
                'expires_at' => $persistedExpiry,
                'hardware_fingerprint' => $hardwareFingerprint,
                'max_rooms' => $maxRooms,
                'max_devices' => $maxDevices,
                'max_android_tv' => $maxAndroidTv,
                'max_smart_tv' => $maxSmartTv,
                'max_backend' => $maxBackend,
                'max_admin_panel' => $maxAdminPanel,
                'max_channels' => $maxChannels,
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
        $onlineVerifiedAt = $license->license_data['online_verified_at'] ?? null;
        $verifiedOnline = false;

        if (is_string($onlineVerifiedAt) && $onlineVerifiedAt !== '') {
            try {
                $verifiedOnline = \Illuminate\Support\Carbon::parse($onlineVerifiedAt)
                    ->greaterThan(now()->subDays(30));
            } catch (\Throwable $e) {
                Log::warning('Stored online license verification timestamp is invalid.');
            }
        }

        $status = array_merge($license->license_data, [
            'license_key'       => $license->license_key,
            'license_type'      => $license->license_type,
            'status'            => strtoupper((string) ($license->license_data['status'] ?? $license->status ?? 'ACTIVE')),
            'expires_at'        => optional($license->expires_at)->toISOString() ?? ($license->license_data['expires_at'] ?? null),
            'validated_at'      => optional($lastValidated)->toISOString(),
            'verified_online'   => $verifiedOnline,
            'online_verified_at'=> $onlineVerifiedAt,
        ]);

        return [
            'licensed' => !$this->licenseExpired($license) && $this->licenseMatchesCurrentMachine($license),
            'status' => $status,
        ];
    }

    public function refreshLicenseStatus(): array
    {
        $validation = $this->validateToken();
        $status = $this->getLicenseStatus();

        if ($validation['valid']) {
            if (is_array($status['status'])) {
                $status['status']['offline_mode'] = false;
            }

            return $status;
        }

        if (!empty($validation['network_error'])) {
            $license = $this->currentLicense();
            $offlineAllowed = $license !== null
                && $status['licensed']
                && $this->withinOfflineGracePeriod($license);

            if (is_array($status['status'])) {
                $status['status']['offline_mode'] = $offlineAllowed;
            }
            $status['licensed'] = $offlineAllowed;

            return $status;
        }

        return ['licensed' => false, 'status' => null];
    }

    public function getTrialStatus(): array
    {
        $license = License::query()
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $query->whereRaw('LOWER(license_type) = ?', ['trial'])
                    ->orWhere('status', 'trial');
            })
            ->latest('id')
            ->first();

        if (!$license) {
            return [
                'in_trial' => false,
                'days_remaining' => 0,
                'expires_at' => null,
                'expired' => false,
            ];
        }

        $expired = $this->licenseExpired($license);
        $trustedNow = $this->trustedCurrentTime($license);
        $daysRemaining = $license->expires_at
            ? max(0, $trustedNow->startOfDay()->diffInDays($license->expires_at->copy()->startOfDay(), false))
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

        if (!$this->licenseMatchesCurrentMachine($license)) {
            return false;
        }

        if ($this->licenseExpired($license)) {
            if ($license->status !== 'expired') {
                $license->update(['status' => 'expired']);
            }
            return false;
        }

        return $this->periodicCheck();
    }

    public function periodicCheck(bool $force = false): bool
    {
        $license = $this->currentLicense();

        if (!$license) {
            return false;
        }

        if (!$this->licenseMatchesCurrentMachine($license)) {
            return false;
        }

        if ($this->licenseExpired($license)) {
            if ($license->status !== 'expired') {
                $license->update(['status' => 'expired']);
            }
            return false;
        }

        $result = $this->validateToken();

        if ($result['valid']) {
            return true;
        }

        if (!empty($result['network_error'])) {
            if ($this->withinOfflineGracePeriod($license)) {
                Log::warning('License server unreachable; allowing access during the configured offline grace period.');
                return true;
            }

            Log::warning('License server unreachable and offline grace period has expired; denying access.');
        }

        return false;
    }

    private function withinOfflineGracePeriod(License $license): bool
    {
        $graceHours = min(72, max(0, (int) config('services.license.offline_grace_hours', 72)));
        $licenseData = is_array($license->license_data) ? $license->license_data : [];
        $verifiedAt = $licenseData['online_verified_at'] ?? null;
        $verifiedBootId = $licenseData['online_verified_boot_id'] ?? null;
        $verifiedMonotonic = filter_var(
            $licenseData['online_verified_monotonic_ns'] ?? null,
            FILTER_VALIDATE_INT
        );
        $currentBootId = $this->getBootId();

        if (
            $graceHours === 0
            || $this->isTrialLicense($license)
            || !is_string($verifiedAt)
            || $verifiedAt === ''
            || !is_string($verifiedBootId)
            || $currentBootId === null
            || !hash_equals($verifiedBootId, $currentBootId)
            || $verifiedMonotonic === false
            || !$this->isSystemClockConsistent($license)
        ) {
            return false;
        }

        $elapsedNanoseconds = $this->monotonicNanoseconds() - $verifiedMonotonic;
        $graceNanoseconds = $graceHours * 60 * 60 * 1_000_000_000;

        return $elapsedNanoseconds >= 0 && $elapsedNanoseconds <= $graceNanoseconds;
    }

    public function validateToken(): array
    {
        $license = $this->currentLicense();

        if (!$license || !is_array($license->license_data)) {
            return ['valid' => false, 'message' => 'No active license found.'];
        }

        $token = $license->license_data['token'] ?? $this->jwtToken;

        if (!$token) {
            return ['valid' => false, 'message' => 'No license token available.'];
        }

        if (!$this->licenseMatchesCurrentMachine($license)) {
            return ['valid' => false, 'network_error' => false, 'message' => 'This license is registered to different server hardware.'];
        }

        $nonce = Str::random(48);

        try {
            $response = $this->client()->post($this->licenseServer . '/validate-token', [
                'token' => $token,
                'nonce' => $nonce,
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

            $serverTime = $this->verifiedServerTime($body, $nonce);
            if (!$serverTime) {
                return ['valid' => false, 'network_error' => false, 'message' => 'The license server time signature could not be verified.'];
            }

            if (!$this->clockMatchesServer($serverTime)) {
                return ['valid' => false, 'network_error' => false, 'message' => 'This server’s date and time do not match the license server.'];
            }

            $features = (array) ($body['features'] ?? ($license->license_data['features'] ?? []));
            $licenseData = $license->license_data;
            $licenseData['license_type'] = strtoupper((string) (
                $body['license_type'] ?? $licenseData['license_type'] ?? $license->license_type
            ));
            $licenseData['hotel_name'] = (string) (
                $body['hotel_name'] ?? $licenseData['hotel_name'] ?? $license->customer_name ?? config('app.name')
            );
            $licenseData['status'] = strtoupper((string) (
                $body['status'] ?? $licenseData['status'] ?? $license->status
            ));
            $expiresAt = array_key_exists('expires_at', $body)
                ? $body['expires_at']
                : ($licenseData['expires_at'] ?? null);
            $licenseData['expires_at'] = $expiresAt;
            $licenseData['features'] = $features;
            $licenseData['max_rooms'] = (int) ($body['max_rooms'] ?? $licenseData['max_rooms'] ?? 0);
            $licenseData['max_devices'] = (int) ($body['max_devices'] ?? $licenseData['max_devices'] ?? 0);
            foreach ([
                'max_android_tv',
                'max_smart_tv',
                'max_backend',
                'max_admin_panel',
            ] as $limit) {
                $licenseData[$limit] = (int) ($body[$limit] ?? $licenseData[$limit] ?? 0);
            }
            $licenseData['max_channels'] = (int) ($body['max_channels'] ?? $licenseData['max_channels'] ?? 0);
            $licenseData['max_tv_devices'] = (int) (
                $body['max_tv_devices']
                ?? ($licenseData['max_android_tv'] + $licenseData['max_smart_tv'])
            );
            $licenseData['device_limit_usage'] = (array) (
                $body['device_allocation']
                ?? $licenseData['device_limit_usage']
                ?? []
            );
            $licenseData['device_usage'] = array_map(
                static fn ($allocation): int => is_array($allocation) ? (int) ($allocation['current'] ?? 0) : 0,
                $licenseData['device_limit_usage']
            );
            $licenseData['rooms_used'] = Room::count();
            $licenseData['rooms_limit'] = $licenseData['max_rooms'];
            $licenseData['device_allocation'] = $this->deviceAllocationRows(
                $licenseData['device_limit_usage'],
                $licenseData['rooms_used'],
                $licenseData['max_rooms']
            );
            $licenseData['total_used'] = array_sum($licenseData['device_usage']);
            $licenseData['total_limit'] = $licenseData['max_devices'];
            $licenseData['device_id'] = $this->getDeviceId();
            $licenseData['hardware_fingerprint'] = $this->getHardwareFingerprint();
            $licenseData = array_merge($licenseData, $this->onlineVerificationData($serverTime));
            $license->update([
                'license_data' => $licenseData,
                'last_validated_at' => $serverTime,
                'license_type' => $licenseData['license_type'],
                'customer_name' => $licenseData['hotel_name'],
                'status' => strtolower($licenseData['status']),
                'expires_at' => array_key_exists('expires_at', $body)
                    ? $this->normalizePersistedExpiry($body)
                    : $license->expires_at,
                'max_rooms' => $licenseData['max_rooms'],
                'max_devices' => $licenseData['max_devices'],
                'max_android_tv' => $licenseData['max_android_tv'],
                'max_smart_tv' => $licenseData['max_smart_tv'],
                'max_backend' => $licenseData['max_backend'],
                'max_admin_panel' => $licenseData['max_admin_panel'],
                'max_channels' => $licenseData['max_channels'],
            ]);

            return [
                'valid' => true,
                'network_error' => false,
                'license_type' => $body['license_type'] ?? ($licenseData['license_type'] ?? null),
                'features' => $features,
                'expires_at' => $body['expires_at'] ?? ($licenseData['expires_at'] ?? null),
            ];
        } catch (\Throwable $e) {
            // Connection refused, timeout, DNS failure, etc. — not a deliberate rejection
            Log::warning('Token validation network error: ' . $e->getMessage());
            return ['valid' => false, 'network_error' => true, 'message' => 'Could not reach license server.'];
        }
    }

    public function refreshToken(): array
    {
        $license = $this->currentLicense();

        if (!$license || !is_array($license->license_data)) {
            return ['success' => false, 'message' => 'No active license found.'];
        }

        if (!$this->licenseMatchesCurrentMachine($license)) {
            return ['success' => false, 'message' => 'This license is registered to different server hardware.'];
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
        $license = $this->currentLicense();

        if (!$license || !is_array($license->license_data)) {
            return ['success' => false, 'message' => 'No active license found.'];
        }

        if (!$this->licenseMatchesCurrentMachine($license)) {
            return ['success' => false, 'message' => 'This license is registered to different server hardware.'];
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
            return ['max_rooms' => 0, 'max_devices' => 0, 'max_channels' => 0];
        }

        $data = $license->license_data;
        $features = (array) ($data['features'] ?? []);

        $maxRooms = (int) ($data['max_rooms']
            ?? $data['rooms_limit']
            ?? $license->max_rooms
            ?? 0);
        $maxDevices = (int) ($data['max_devices']
            ?? $features['max_devices']
            ?? $features['max_tv_devices']
            ?? $license->max_devices
            ?? 0);
        $maxChannels = (int) ($data['max_channels']
            ?? $features['max_channels']
            ?? $license->max_channels
            ?? 0);

        return [
            'max_rooms' => max(0, $maxRooms),
            'max_devices' => max(0, $maxDevices),
            'max_android_tv' => max(0, (int) ($data['max_android_tv'] ?? 0)),
            'max_smart_tv' => max(0, (int) ($data['max_smart_tv'] ?? 0)),
            'max_backend' => max(0, (int) ($data['max_backend'] ?? 0)),
            'max_admin_panel' => max(0, (int) ($data['max_admin_panel'] ?? 0)),
            'max_channels' => max(0, $maxChannels),
        ];
    }

    private function deviceAllocationRows(array $allocation, int $roomsUsed, int $roomsLimit): array
    {
        $rows = [];
        foreach ([
            'android_tv' => 'Android TV Devices',
            'smart_tv' => 'Smart TV Devices',
            'backend' => 'Backend Servers',
            'admin_panel' => 'Admin Panels',
        ] as $key => $label) {
            $usage = $allocation[$key] ?? [];
            $rows[] = [
                'type' => $label,
                'used' => is_array($usage) ? (int) ($usage['current'] ?? 0) : 0,
                'limit' => is_array($usage) ? (int) ($usage['max'] ?? 0) : 0,
            ];
        }

        $rows[] = [
            'type' => 'Rooms',
            'used' => $roomsUsed,
            'limit' => $roomsLimit,
        ];

        return $rows;
    }

    public function syncRooms(int $roomCount): bool
    {
        $license = License::whereIn('status', ['active', 'trial'])->latest('id')->first();

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
        $licenses = License::whereIn('status', ['active', 'trial'])->get();

        foreach ($licenses as $license) {
            $licenseData = is_array($license->license_data) ? $license->license_data : [];

            try {
                $this->client()->post($this->licenseServer . '/deregister-device', [
                    'license_key' => $license->license_key,
                    'device_id' => $licenseData['device_id'] ?? $this->getDeviceId(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Could not deregister HMS device from license server', [
                    'error' => $e->getMessage(),
                ]);
            }

            $license->update(['status' => 'inactive']);
        }

        Cache::forget('license_valid');
        $this->jwtToken = null;
    }

    private function licenseExpired(License $license): bool
    {
        if (!in_array($license->status, ['active', 'trial'], true)) {
            return true;
        }

        if ($this->isTrialLicense($license) && !$license->expires_at) {
            return true;
        }

        return (bool) (
            $license->expires_at
            && $this->trustedCurrentTime($license)->greaterThan($license->expires_at)
        );
    }
}
