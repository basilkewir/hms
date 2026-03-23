<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * HMS License Validation Service
 *
 * Validates licenses against kewirdev.com online license server.
 * No offline mode — real licenses require online validation.
 * New installations get a 14-day trial (local check only).
 */
class LicenseValidationService
{
    private string  $licenseServer;
    private ?string $jwtToken = null;
    private ?string $deviceId = null;

    public function __construct()
    {
        $this->licenseServer = env('LICENSE_SERVER_URL', 'https://kewirdev.com/api/license');
        $this->loadStoredToken();
    }

    // ─── Device Identity ──────────────────────────────────────────────────────

    /**
     * Returns a stable, persistent device ID for this HMS installation.
     * Generated once as a UUID and stored in storage/app/hms_device_id.
     * Using a UUID avoids fingerprint collisions from unreliable MAC address reads.
     */
    private function getDeviceId(): string
    {
        if ($this->deviceId) {
            return $this->deviceId;
        }

        $path = storage_path('app/hms_device_id');

        if (file_exists($path)) {
            $stored = trim(file_get_contents($path));
            if (!empty($stored)) {
                $this->deviceId = $stored;
                return $stored;
            }
        }

        $deviceId = 'hms-' . Str::uuid()->toString();

        try {
            file_put_contents($path, $deviceId);
        } catch (\Throwable $e) {
            Log::warning('Could not persist HMS device ID: ' . $e->getMessage());
        }

        $this->deviceId = $deviceId;
        return $deviceId;
    }

    // ─── Request Signature ────────────────────────────────────────────────────

    /**
     * Compute HMAC-SHA256 signature for kewirdev.com request verification.
     *
     * The key (LICENSE_SIGNATURE_SECRET in HMS .env) must equal
     * kewirdev.com's LICENSE_JWT_SECRET — used as-is (no base64 decode).
     *
     * kewirdev server computes:  hash_hmac('sha256', json_encode($request->all()), config('license.jwt_secret'))
     * HMS client computes:       hash_hmac('sha256', json_encode($payload), $secret)
     * These match when the payload order and values are identical.
     */
    private function computeSignature(array $payload): string
    {
        $secret = config('services.license.signature_secret', '');
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    // ─── Token Storage ────────────────────────────────────────────────────────

    private function loadStoredToken(): void
    {
        $license = \App\Models\License::where('status', 'active')->latest('id')->first();
        if ($license && isset($license->license_data['token'])) {
            $this->jwtToken = $license->license_data['token'];
            $this->deviceId = $license->license_data['device_id'] ?? null;
        }
    }

    private function storeToken(string $token, string $deviceId, ?string $expiresAt): void
    {
        $license = \App\Models\License::where('status', 'active')->first();
        if ($license) {
            $ld = $license->license_data ?? [];
            $ld['token']            = $token;
            $ld['device_id']        = $deviceId;
            $ld['token_expires_at'] = $expiresAt;
            $license->update(['license_data' => $ld]);
        }
        $this->jwtToken = $token;
        $this->deviceId = $deviceId;
    }

    // ─── Shared Payload Builder ───────────────────────────────────────────────

    /**
     * Build the standard validation payload sent to kewirdev /validate.
     * Keys are ordered consistently so the JSON matches the server's re-encode.
     */
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

    // ─── License Activation ───────────────────────────────────────────────────

    /**
     * Validate and activate a license key against kewirdev.com.
     *
     * No offline fallback — the HTTP call must succeed.
     * On network failure, an actionable error is returned to the user.
     */
    public function validateLicense(string $licenseKey, ?string $hotelName = null, array $deviceInfo = []): array
    {
        $payload = $this->buildValidatePayload($licenseKey, $hotelName);

        try {
            $response = Http::withOptions([
                'verify'          => false,
                'timeout'         => 20,
                'connect_timeout' => 10,
            ])->asJson()->withHeaders([
                'User-Agent'          => 'HotelManagementSystem/1.0.0',
                'Accept'              => 'application/json',
                'X-License-Signature' => $this->computeSignature($payload),
            ])->post($this->licenseServer . '/validate', $payload);

            if (!$response->successful()) {
                Log::warning('License server error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $this->mapHttpError($response->status(), $response->json() ?? []);
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                return [
                    'valid'   => false,
                    'message' => $data['message'] ?? $data['error'] ?? 'License validation failed.',
                ];
            }

            return $this->persistValidatedLicense($licenseKey, $hotelName, $data);

        } catch (\Exception $e) {
            Log::error('License validation network error: ' . $e->getMessage());
            return [
                'valid'   => false,
                'message' => 'Could not reach the license server. Please check your internet connection and try again.',
            ];
        }
    }

    private function mapHttpError(int $code, array $body): array
    {
        return match (true) {
            $code === 429 => [
                'valid'   => false,
                'message' => 'Too many validation attempts. Please wait a minute and try again.',
            ],
            $code === 404 => [
                'valid'   => false,
                'message' => 'License key not found. Please verify your license key at kewirdev.com.',
            ],
            $code === 401 => [
                'valid'   => false,
                'message' => 'Invalid license key.',
            ],
            $code === 400 => [
                'valid'   => false,
                'message' => self::extract400Message($body),
            ],
            $code === 403 => [
                'valid'   => false,
                'message' => match ($body['reason'] ?? '') {
                    'device_mismatch', 'device_limit_exceeded', 'machine_conflict'
                        => 'This license is already activated on another machine. Only one installation is allowed per license. Please deactivate it on the other machine first or contact KewirDev support.',
                    default
                        => 'License rejected by server (' . ($body['reason'] ?? 'access_denied') . '). Contact KewirDev support.',
                },
            ],
            default => [
                'valid'   => false,
                'message' => ($body['error'] ?? $body['message'] ?? null)
                    ? ($body['error'] ?? $body['message'])
                    : 'License server returned an error (HTTP ' . $code . '). Please try again later.',
            ],
        };
    }

    private static function extract400Message(array $body): string
    {
        // Server returns field-level validation errors in $body['details']
        if (!empty($body['details']) && is_array($body['details'])) {
            $messages = [];
            foreach ($body['details'] as $field => $errors) {
                $messages[] = is_array($errors) ? implode(' ', $errors) : (string) $errors;
            }
            return implode(' ', $messages);
        }
        return $body['error'] ?? $body['message'] ?? 'Invalid request. Please check your license key and try again.';
    }

    private function persistValidatedLicense(string $licenseKey, ?string $hotelName, array $srv): array
    {
        $features = $srv['features'] ?? [];
        $maxRooms = (int) ($features['max_users'] ?? -1);
        $deviceId = $this->getDeviceId();

        $licenseData = [
            'license_key'      => $licenseKey,
            'hotel_name'       => $hotelName ?: ($srv['hotel_name'] ?? config('app.name')),
            'license_type'     => strtoupper($srv['license_type'] ?? 'BASIC'),
            'status'           => 'ACTIVE',
            'expires_at'       => isset($srv['expires_at']) ? date('n/j/Y', strtotime($srv['expires_at'])) : 'Never',
            'features'         => $features,
            'max_rooms'        => $maxRooms,
            'device_limits'    => ['rooms' => $maxRooms],
            'device_allocation' => [
                ['type' => 'Rooms', 'used' => \App\Models\Room::count(), 'limit' => $maxRooms],
            ],
            'total_used'       => \App\Models\Room::count(),
            'total_limit'      => $maxRooms,
            'validated_at'     => now()->toISOString(),
            'is_valid'         => true,
            'token'            => $srv['token'] ?? null,
            'device_id'        => $deviceId,
            'token_expires_at' => $srv['expires_at'] ?? null,
            'timestamp'        => $srv['timestamp'] ?? now()->toISOString(),
        ];

        // Supersede any active trial license
        \App\Models\License::where('status', 'trial')->update(['status' => 'superseded']);

        \App\Models\License::updateOrCreate(
            ['license_key' => $licenseKey],
            [
                'license_data'         => $licenseData,
                'product_name'         => 'Hotel Management System',
                'status'               => 'active',
                'customer_name'        => $licenseData['hotel_name'],
                'customer_email'       => 'admin@hotel.com',
                'license_type'         => $licenseData['license_type'],
                'expires_at'           => $srv['expires_at'] ?? null,
                'issued_at'            => now(),
                'activated_at'         => now(),
                'last_validated_at'    => now(),
                'hardware_fingerprint' => $deviceId,
            ]
        );

        if (!empty($srv['token'])) {
            $this->storeToken($srv['token'], $deviceId, $srv['expires_at'] ?? null);
        }

        Cache::forget('license_valid');

        return [
            'valid'   => true,
            'license' => $licenseData,
            'message' => 'License activated successfully',
            'token'   => $srv['token'] ?? null,
        ];
    }

    // ─── System Licensed Check ────────────────────────────────────────────────

    /**
     * Determine whether the system is licensed to operate.
     *
     * - TRIAL license: checked locally (no network, 14-day window)
     * - Real license:  validated online against kewirdev.com
     *                  No offline fallback — if the server is unreachable the
     *                  CheckLicense middleware's 10-minute cache provides a
     *                  brief grace window for transient connectivity issues.
     */
    public function isSystemLicensed(): bool
    {
        // Prefer a real active license over a trial
        $real = \App\Models\License::whereNotNull('license_key')
            ->where('status', 'active')
            ->whereNotIn('license_type', ['TRIAL'])
            ->latest('id')
            ->first();

        if ($real) {
            try {
                if ($this->validateOnline($real)) {
                    return true;
                }
            } catch (\RuntimeException $e) {
                // Allow middleware grace handling, but still try valid trial fallback first.
                if (!$this->hasValidTrial()) {
                    throw $e;
                }
                return true;
            }

            // Real license is not valid right now — allow access if trial is still valid.
            if ($this->hasValidTrial()) {
                return true;
            }

            return false;
        }

        // Fall back to trial license
        return $this->hasValidTrial();
    }

    private function hasValidTrial(): bool
    {
        $trial = \App\Models\License::where('status', 'trial')->latest('id')->first();

        if (!$trial || !$trial->expires_at) {
            return false;
        }

        return Carbon::now()->lt(Carbon::parse($trial->expires_at));
    }

    /**
     * Validate a real (non-trial) license online.
     *
     * Tier 1: Use stored JWT token → GET /info
     * Tier 2: Token expired (401) → re-validate via POST /validate
     * Failure: return false — no offline fallback.
     */
    private function validateOnline(\App\Models\License $license): bool
    {
        $token = $license->license_data['token'] ?? $this->jwtToken;

        // ── Tier 1: Bearer token → GET /info ──────────────────────────────────
        if ($token) {
            try {
                $response = Http::withOptions([
                    'verify'          => false,
                    'timeout'         => 8,
                    'connect_timeout' => 4,
                ])->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'User-Agent'    => 'HotelManagementSystem/1.0.0',
                    'Accept'        => 'application/json',
                ])->get($this->licenseServer . '/info');

                if ($response->successful()) {
                    $data = $response->json();

                    if (!($data['success'] ?? false)) {
                        return false;
                    }

                    $status = strtoupper($data['data']['status'] ?? '');
                    if ($status !== 'ACTIVE') {
                        $license->update(['status' => 'inactive']);
                        Log::info('License not ACTIVE on kewirdev.com', ['status' => $status]);
                        return false;
                    }

                    $expiresAt = $data['data']['expires_at'] ?? null;
                    if ($expiresAt && $expiresAt !== 'Never' && strtotime($expiresAt) < time()) {
                        $license->update(['status' => 'inactive']);
                        return false;
                    }

                    $license->update(['status' => 'active', 'last_validated_at' => now()]);
                    Cache::put('license_last_known_valid', now()->timestamp, 86400);
                    return true;
                }

                $code = $response->status();

                if ($code === 404) {
                    $license->update(['status' => 'inactive']);
                    Log::warning('License not found on kewirdev.com (GET /info)');
                    return false;
                }

                if ($code !== 401) {
                    // Unexpected HTTP error — use grace period (throw to trigger null path)
                    Log::warning('License GET /info returned ' . $code . ' — using grace period');
                    throw new \RuntimeException('License server HTTP ' . $code);
                }

                // 401 = JWT expired → fall through to Tier 2

            } catch (\RuntimeException $e) {
                throw $e; // re-throw grace-period signals
            } catch (\Throwable $e) {
                Log::warning('License GET /info network error: ' . $e->getMessage());
                throw new \RuntimeException('network_error'); // bubble up to trigger grace period
            }
        }

        // ── Tier 2: No token or expired token → POST /validate ────────────────
        try {
            $deviceId = $license->license_data['device_id'] ?? $this->getDeviceId();
            $payload  = $this->buildValidatePayload($license->license_key);

            $response = Http::withOptions([
                'verify'          => false,
                'timeout'         => 12,
                'connect_timeout' => 6,
            ])->asJson()->withHeaders([
                'User-Agent'          => 'HotelManagementSystem/1.0.0',
                'Accept'              => 'application/json',
                'X-License-Signature' => $this->computeSignature($payload),
            ])->post($this->licenseServer . '/validate', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (!($data['success'] ?? false)) {
                    return false;
                }
                if (!empty($data['token'])) {
                    $this->storeToken($data['token'], $deviceId, $data['expires_at'] ?? null);
                }
                $license->update(['status' => 'active', 'last_validated_at' => now()]);
                Cache::put('license_last_known_valid', now()->timestamp, 86400);
                return true;
            }

            $code = $response->status();
            if (in_array($code, [401, 404, 403])) {
                $reason = $response->json()['reason'] ?? '';
                $license->update(['status' => 'inactive']);
                if ($code === 403) {
                    Log::warning('License rejected — possible multi-machine conflict.', [
                        'license_key' => $license->license_key,
                        'device_id'   => $license->license_data['device_id'] ?? null,
                        'reason'      => $reason,
                    ]);
                }
                return false; // definitive rejection — no grace
            }
            // Other HTTP errors (5xx, etc.) — use grace period
            Log::warning('License POST /validate returned ' . $code . ' — using grace period');
            throw new \RuntimeException('License server HTTP ' . $code);

        } catch (\RuntimeException $e) {
            throw $e; // re-throw grace-period signals
        } catch (\Throwable $e) {
            Log::warning('License POST /validate network error: ' . $e->getMessage());
            throw new \RuntimeException('network_error'); // bubble up to trigger grace period
        }
    }

    // ─── Trial Status ─────────────────────────────────────────────────────────

    /**
     * Return trial license status for display on the license activation page.
     */
    public function getTrialStatus(): array
    {
        $trial = \App\Models\License::where('status', 'trial')->latest('id')->first();

        if (!$trial || !$trial->expires_at) {
            return ['in_trial' => false, 'days_remaining' => 0, 'expires_at' => null, 'expired' => true];
        }

        $expiresAt     = Carbon::parse($trial->expires_at);
        $expired       = Carbon::now()->gt($expiresAt);
        $daysRemaining = $expired ? 0 : (int) Carbon::now()->diffInDays($expiresAt, false);

        return [
            'in_trial'       => !$expired,
            'days_remaining' => max(0, $daysRemaining),
            'expires_at'     => $expiresAt->format('Y-m-d'),
            'expired'        => $expired,
        ];
    }

    // ─── License Status (for Settings page) ──────────────────────────────────

    public function getLicenseStatus(): array
    {
        $license = \App\Models\License::where('status', 'active')->first();

        if (!$license || !$license->license_data) {
            $trial = $this->getTrialStatus();
            return [
                'licensed' => false,
                'status'   => null,
                'trial'    => $trial,
            ];
        }

        $ld          = $license->license_data;
        $features    = $ld['features'] ?? [];
        $maxRooms    = (int) ($features['max_users'] ?? -1);
        $maxChannels = (int) ($features['max_channels'] ?? -1);
        $totalRooms  = \App\Models\Room::count();

        $ld['rooms_used']  = $totalRooms;
        $ld['rooms_limit'] = $maxRooms;
        $ld['device_allocation'] = [
            ['type' => 'Rooms',    'used' => $totalRooms,              'limit' => $maxRooms],
            ['type' => 'Channels', 'used' => $ld['total_used'] ?? 0,   'limit' => $maxChannels],
        ];
        $ld['total_used']  = $totalRooms;
        $ld['total_limit'] = $maxRooms;

        return ['licensed' => true, 'status' => $ld];
    }

    // ─── License Limits ───────────────────────────────────────────────────────

    public function getLicenseLimits(): array
    {
        $license = \App\Models\License::where('status', 'active')->first();

        if (!$license || !$license->license_data) {
            // Trial mode or no license: unlimited
            return ['max_rooms' => -1, 'max_channels' => -1];
        }

        $features = $license->license_data['features'] ?? [];
        return [
            'max_rooms'    => (int) ($features['max_users'] ?? -1),
            'max_channels' => (int) ($features['max_channels'] ?? -1),
        ];
    }

    // ─── Room Sync ────────────────────────────────────────────────────────────

    public function syncRooms(int $roomCount): array
    {
        // Reload freshest token from DB
        $this->loadStoredToken();

        if (!$this->jwtToken || !$this->deviceId) {
            // No active real license (trial or unlicensed) — allow freely
            return ['success' => true, 'room_count' => $roomCount, 'room_limit' => -1, 'allowed' => true];
        }

        try {
            $response = Http::withOptions([
                'verify'  => false,
                'timeout' => 8,
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent'   => 'HotelManagementSystem/1.0.0',
            ])->post($this->licenseServer . '/sync-rooms', [
                'token'      => $this->jwtToken,
                'device_id'  => (string) $this->deviceId,
                'room_count' => $roomCount,
            ]);

            $data = $response->json() ?? [];

            if (!$response->successful() || empty($data['success'])) {
                $allowed = !isset($data['allowed']) || $data['allowed'] !== false;
                Log::warning('syncRooms failed', ['http' => $response->status(), 'error' => $data['error'] ?? 'unknown']);
                return [
                    'success'    => false,
                    'error'      => $data['error'] ?? 'Room sync failed',
                    'room_count' => $roomCount,
                    'room_limit' => $data['room_limit'] ?? -1,
                    'allowed'    => $allowed,
                ];
            }

            return [
                'success'    => true,
                'room_count' => $data['room_count'] ?? $roomCount,
                'room_limit' => $data['room_limit'] ?? -1,
                'allowed'    => true,
            ];

        } catch (\Exception $e) {
            // Non-fatal: don't block room creation on sync failure
            Log::warning('syncRooms error (non-fatal): ' . $e->getMessage());
            return ['success' => true, 'room_count' => $roomCount, 'room_limit' => -1, 'allowed' => true];
        }
    }

    // ─── Token Management ─────────────────────────────────────────────────────

    public function validateToken(): array
    {
        if (!$this->jwtToken) {
            return ['valid' => false, 'message' => 'No token available'];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 5])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'HotelManagementSystem/1.0.0',
                ])->post($this->licenseServer . '/validate-token', ['token' => $this->jwtToken]);

            if (!$response->successful()) {
                return ['valid' => false, 'message' => 'Token validation failed'];
            }

            $data = $response->json();
            if (!($data['success'] ?? false)) {
                return ['valid' => false, 'message' => $data['message'] ?? 'Token validation failed'];
            }

            return [
                'valid'        => true,
                'license_type' => $data['license_type'] ?? null,
                'features'     => $data['features'] ?? [],
                'expires_at'   => $data['expires_at'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Token validation error: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Token validation failed'];
        }
    }

    public function refreshToken(): array
    {
        if (!$this->jwtToken || !$this->deviceId) {
            return ['success' => false, 'message' => 'No token available to refresh'];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 8])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'HotelManagementSystem/1.0.0',
                ])->post($this->licenseServer . '/refresh-token', [
                    'token'     => $this->jwtToken,
                    'device_id' => (string) $this->deviceId,
                ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Token refresh failed'];
            }

            $data = $response->json();
            if (!($data['success'] ?? false)) {
                return ['success' => false, 'message' => $data['message'] ?? 'Token refresh failed'];
            }

            $this->storeToken($data['token'], $this->deviceId, $data['expires_at'] ?? null);
            return [
                'success'    => true,
                'token'      => $data['token'],
                'expires_at' => $data['expires_at'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Token refresh error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Token refresh failed'];
        }
    }

    public function sendHeartbeat(): array
    {
        if (!$this->jwtToken || !$this->deviceId) {
            return ['success' => false, 'message' => 'No active license to send heartbeat'];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 5])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'HotelManagementSystem/1.0.0',
                ])->post($this->licenseServer . '/heartbeat', [
                    'token'     => $this->jwtToken,
                    'device_id' => (string) $this->deviceId,
                ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Heartbeat failed'];
            }

            $data = $response->json();
            return [
                'success'        => $data['success'] ?? false,
                'message'        => $data['message'] ?? 'Heartbeat received',
                'server_time'    => $data['server_time'] ?? null,
                'next_heartbeat' => $data['next_heartbeat'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Heartbeat error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Heartbeat failed'];
        }
    }

    public function getLicenseInfo(string $licenseKey): array
    {
        if (!$this->jwtToken) {
            return ['success' => false, 'message' => 'No active token'];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 8])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->jwtToken,
                    'User-Agent'    => 'HotelManagementSystem/1.0.0',
                    'Accept'        => 'application/json',
                ])->get($this->licenseServer . '/info');

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Failed to get license information'];
            }

            $data = $response->json();
            if (!($data['success'] ?? false)) {
                return ['success' => false, 'message' => $data['message'] ?? 'Failed to get license information'];
            }

            return ['success' => true, 'data' => $data['data']];

        } catch (\Exception $e) {
            Log::error('License info error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get license information'];
        }
    }

    public function shouldRefreshToken(): bool
    {
        if (!$this->jwtToken) {
            return false;
        }

        $license = \App\Models\License::where('status', 'active')->first();
        if (!$license || !isset($license->license_data['token_expires_at'])) {
            return false;
        }

        $expiresAt = $license->license_data['token_expires_at'];
        if (is_string($expiresAt)) {
            $expiresAt = strtotime($expiresAt);
        }

        return is_int($expiresAt) && ($expiresAt - time()) < 300;
    }

    public function removeLicense(): bool
    {
        // Notify the license server so the seat is freed (best-effort)
        $active = \App\Models\License::where('status', 'active')->first();
        if ($active && !empty($active->license_data['token'])) {
            try {
                Http::withOptions(['verify' => false, 'timeout' => 6])->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'HotelManagementSystem/1.0.0',
                    'Accept'       => 'application/json',
                ])->post($this->licenseServer . '/deactivate', [
                    'token'      => $active->license_data['token'],
                    'device_id'  => $active->license_data['device_id'] ?? $this->getDeviceId(),
                    'license_key'=> $active->license_key,
                ]);
            } catch (\Throwable $e) {
                Log::warning('License deactivation notification failed (non-fatal): ' . $e->getMessage());
            }
        }

        // Delete ALL license records (active + trial) so the system resets cleanly
        \App\Models\License::whereIn('status', ['active', 'trial', 'superseded', 'inactive'])->delete();

        Cache::forget('license_valid');
        $this->jwtToken = null;
        $this->deviceId = null;

        return true;
    }

    /**
     * Periodically verify the active license is still valid on the server.
     * Called by the scheduled command — updates local DB status.
     * Returns whether the license is still valid.
     */
    public function periodicCheck(): bool
    {
        $license = \App\Models\License::where('status', 'active')
            ->whereNotIn('license_type', ['TRIAL'])
            ->whereNotNull('license_key')
            ->latest('id')
            ->first();

        if (!$license) {
            // Only trial — check expiry
            $trial = \App\Models\License::where('status', 'trial')->latest('id')->first();
            if ($trial && $trial->expires_at) {
                $expired = Carbon::now()->gt(Carbon::parse($trial->expires_at));
                if ($expired) {
                    $trial->update(['status' => 'inactive']);
                    Cache::forget('license_valid');
                    Log::info('Trial license expired during periodic check.');
                    return false;
                }
            }
            return (bool) $trial;
        }

        $isValid = $this->validateOnline($license);

        if (!$isValid) {
            Log::warning('Periodic license check failed — license marked inactive.', [
                'license_key' => $license->license_key,
                'device_id'   => $license->license_data['device_id'] ?? null,
            ]);
        } else {
            Log::info('Periodic license check passed.', ['license_key' => $license->license_key]);
        }

        Cache::forget('license_valid');
        return $isValid;
    }
}
