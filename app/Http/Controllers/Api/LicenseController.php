<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseDevice;
use App\Models\LicenseValidationLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LicenseService
{
    private $jwtSecret;
    private $tokenExpiration;

    public function __construct()
    {
        $jwtSecret = config('license.jwt_secret');
        if (empty($jwtSecret)) {
            throw new \RuntimeException(
                'LICENSE_JWT_SECRET is not set. Run: php artisan tinker --execute="echo base64_encode(random_bytes(32));"'
            );
        }
        $this->jwtSecret = $jwtSecret;
        $this->tokenExpiration = (int) config('license.token_expiration', 3600);
    }

    /**
     * Validate license key with device binding
     */
    public function validateLicense(string $licenseKey, array $deviceInfo): array
    {
        $startTime = microtime(true);
        $validationLog = null;

        try {
            // Find license
            $license = License::where('license_key', $licenseKey)->first();
            
            if (!$license) {
                return $this->createValidationResponse(
                    false, 
                    'Invalid license key', 
                    null, 
                    $licenseKey, 
                    $deviceInfo, 
                    $startTime,
                    LicenseValidationLog::STATUS_INVALID
                );
            }

            // Check if license is valid
            if (!$license->isValid()) {
                $status = $license->isExpired() ? 
                    LicenseValidationLog::STATUS_EXPIRED : 
                    LicenseValidationLog::STATUS_INVALID;
                
                return $this->createValidationResponse(
                    false, 
                    'License is not valid or expired', 
                    $license, 
                    $licenseKey, 
                    $deviceInfo, 
                    $startTime,
                    $status
                );
            }

            // Generate device fingerprint
            $deviceFingerprint = LicenseDevice::generateFingerprint($deviceInfo);
            
            // Check device binding
            $device = $this->handleDeviceBinding($license, $deviceInfo, $deviceFingerprint);
            
            if (!$device) {
                return $this->createValidationResponse(
                    false, 
                    'Device limit reached or device blocked', 
                    $license, 
                    $licenseKey, 
                    $deviceInfo, 
                    $startTime,
                    LicenseValidationLog::STATUS_BLOCKED
                );
            }

            // Update validation counters
            $license->updateValidation();
            $device->updateLastSeen();

            // Generate JWT token
            $token = $this->generateJWTToken($license, $device);

            // Cache validation result
            $this->cacheValidationResult($licenseKey, $deviceFingerprint, true);

            return $this->createValidationResponse(
                true, 
                'License validated successfully', 
                $license, 
                $licenseKey, 
                $deviceInfo, 
                $startTime,
                LicenseValidationLog::STATUS_SUCCESS,
                [
                    'token' => $token,
                    'expires_at' => now()->addSeconds($this->tokenExpiration)->toISOString(),
                    'features' => $license->getAvailableFeatures(),
                    'device_id' => $device->id
                ]
            );

        } catch (\Exception $e) {
            Log::error('License validation error', [
                'license_key' => $licenseKey,
                'device_info' => $deviceInfo,
                'error' => $e->getMessage()
            ]);

            return $this->createValidationResponse(
                false, 
                'Validation error: ' . $e->getMessage(), 
                $license ?? null, 
                $licenseKey, 
                $deviceInfo, 
                $startTime,
                LicenseValidationLog::STATUS_FAILED
            );
        }
    }

    /**
     * Validate JWT token
     */
    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Check if license still exists and is valid
            $license = License::find($decoded->license_id);
            if (!$license || !$license->isValid()) {
                return ['valid' => false, 'error' => 'License no longer valid'];
            }

            // Check if device still exists and is active
            $device = LicenseDevice::find($decoded->device_id);
            if (!$device || !$device->isActive()) {
                return ['valid' => false, 'error' => 'Device no longer active'];
            }

            return [
                'valid' => true,
                'license' => $license,
                'device' => $device,
                'features' => $license->getAvailableFeatures()
            ];

        } catch (\Exception $e) {
            Log::warning('JWT decode failed', ['error' => $e->getMessage()]);
            return ['valid' => false, 'error' => 'Token is invalid or has expired. Please re-validate your license.'];
        }
    }

    /**
     * Handle device binding logic Ã¢â‚¬â€ wrapped in a DB transaction with row-level lock
     * to prevent TOCTOU race conditions on device-limit enforcement.
     */
    private function handleDeviceBinding(License $license, array $deviceInfo, string $deviceFingerprint): ?LicenseDevice
    {
        return DB::transaction(function () use ($license, $deviceInfo, $deviceFingerprint) {
            // Re-fetch the license with an exclusive row lock so concurrent
            // activations cannot both pass the canAddDevice() check.
            $license = License::lockForUpdate()->find($license->id);

            // Check if device already exists
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

            // New device Ã¢â‚¬â€ check limit under the lock (atomic check+create)
            $deviceType = $deviceInfo['device_type'] ?? 'unknown';
            if (!$license->canAddDevice($deviceType)) {
                return null;
            }

            $device = LicenseDevice::create([
                'license_id'       => $license->id,
                'device_id'        => $deviceInfo['device_id'] ?? '',
                'device_fingerprint' => $deviceFingerprint,
                'device_name'      => $deviceInfo['device_name'] ?? 'Unknown Device',
                'device_type'      => $deviceType,
                'device_model'     => $deviceInfo['device_model'] ?? '',
                'device_os'        => $deviceInfo['device_os'] ?? '',
                'device_os_version' => $deviceInfo['device_os_version'] ?? '',
                'app_version'      => $deviceInfo['app_version'] ?? '',
                'ip_address'       => $deviceInfo['ip_address'] ?? request()->ip(),
                'mac_address'      => $deviceInfo['mac_address'] ?? '',
                'status'           => LicenseDevice::STATUS_ACTIVE,
                'first_activated_at' => now(),
                'last_seen_at'     => now(),
                'activation_count' => 1,
                'metadata'         => $deviceInfo['metadata'] ?? [],
            ]);

            $license->incrementDeviceCount($deviceType);

            return $device;
        });
    }

    /**
     * Generate JWT token Ã¢â‚¬â€ public so controllers can use it directly (e.g. refreshToken)
     */
    public function generateToken(License $license, LicenseDevice $device): string
    {
        return $this->generateJWTToken($license, $device);
    }

    /**
     * Generate JWT token (internal)
     */
    private function generateJWTToken(License $license, LicenseDevice $device): string
    {
        $payload = [
            'iss' => config('app.url'),
            'aud' => 'hotel-iptv-app',
            'iat' => time(),
            'exp' => time() + $this->tokenExpiration,
            'license_id' => $license->id,
            'license_key' => $license->license_key,
            'device_id' => $device->id,
            'device_fingerprint' => $device->device_fingerprint,
            'hotel_id' => $license->hotel_id,
            'license_type' => $license->license_type,
            'features' => $license->getAvailableFeatures()
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * Create validation response
     */
    private function createValidationResponse(
        bool $success, 
        string $message, 
        ?License $license, 
        string $licenseKey, 
        array $deviceInfo, 
        float $startTime,
        string $status,
        array $additionalData = []
    ): array {
        $processingTime = microtime(true) - $startTime;

        // Log validation attempt
        LicenseValidationLog::create([
            'license_id' => $license?->id,
            'device_id' => $deviceInfo['device_id'] ?? null,
            'validation_type' => LicenseValidationLog::TYPE_INITIAL,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_data' => [
                'license_key' => $licenseKey,
                'device_info' => $deviceInfo
            ],
            'response_data' => array_merge([
                'success' => $success,
                'message' => $message
            ], $additionalData),
            'error_message' => $success ? null : $message,
            'processing_time' => $processingTime,
            'validated_at' => now()
        ]);

        return array_merge([
            'success' => $success,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'processing_time' => round($processingTime * 1000, 2) . 'ms'
        ], $additionalData);
    }

    /**
     * Cache validation result
     */
    private function cacheValidationResult(string $licenseKey, string $deviceFingerprint, bool $isValid): void
    {
        $cacheKey = "license_validation:{$licenseKey}:{$deviceFingerprint}";
        Cache::put($cacheKey, $isValid, now()->addMinutes(5));
    }

    /**
     * Check cached validation result
     */
    public function getCachedValidationResult(string $licenseKey, string $deviceFingerprint): ?bool
    {
        $cacheKey = "license_validation:{$licenseKey}:{$deviceFingerprint}";
        return Cache::get($cacheKey);
    }

    /**
     * Create new license
     */
    public function createLicense(array $data): License
    {
        return License::create(array_merge($data, [
            'license_key' => License::generateLicenseKey(),
            'status' => License::STATUS_ACTIVE,
            'current_devices' => 0,
            'validation_count' => 0
        ]));
    }

    /**
     * Revoke license
     */
    public function revokeLicense(string $licenseKey): bool
    {
        $license = License::where('license_key', $licenseKey)->first();
        
        if (!$license) {
            return false;
        }

        $license->update(['status' => License::STATUS_REVOKED]);
        
        // Deactivate all devices
        $license->devices()->update(['status' => LicenseDevice::STATUS_INACTIVE]);
        
        // Clear cache
        $license->devices->each(function ($device) use ($licenseKey) {
            $cacheKey = "license_validation:{$licenseKey}:{$device->device_fingerprint}";
            Cache::forget($cacheKey);
        });

        return true;
    }

    /**
     * Get license statistics
     */
    public function getLicenseStats(string $licenseKey): array
    {
        $license = License::where('license_key', $licenseKey)
            ->with(['devices', 'validationLogs'])
            ->first();

        if (!$license) {
            return [];
        }

        return [
            'license' => $license,
            'total_devices' => $license->devices->count(),
            'active_devices' => $license->activeDevices->count(),
            'total_validations' => $license->validation_count,
            'recent_validations' => $license->validationLogs()
                ->where('validated_at', '>=', now()->subDays(7))
                ->count(),
            'last_validation' => $license->last_validated_at,
            'is_valid' => $license->isValid(),
            'expires_at' => $license->expires_at,
            'features' => $license->getAvailableFeatures()
        ];
    }

    /**
     * Detect suspicious activity
     */
    public function detectSuspiciousActivity(License $license): array
    {
        $suspiciousActivities = [];

        // Check for too many devices
        if ($license->current_devices > $license->max_devices) {
            $suspiciousActivities[] = 'Device limit exceeded';
        }

        // Check for rapid validation attempts
        $recentValidations = $license->validationLogs()
            ->where('validated_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentValidations > 50) {
            $suspiciousActivities[] = 'Too many validation attempts';
        }

        // Check for multiple IPs
        $recentIPs = $license->validationLogs()
            ->where('validated_at', '>=', now()->subHours(1))
            ->distinct('ip_address')
            ->count();

        if ($recentIPs > 10) {
            $suspiciousActivities[] = 'Multiple IP addresses detected';
        }

        return $suspiciousActivities;
    }
    /**
     * Sync room count from an HMS installation.
     * HMS reports how many rooms it currently has; we store it in assigned_rooms
     * and validate it against the license limit (max_users = room limit, -1 = unlimited).
     *
     * @param  string $licenseKey
     * @param  string $deviceId    The HMS device_id (for auth)
     * @param  int    $roomCount   Current number of rooms on the HMS
     * @return array  ['success', 'room_count', 'room_limit', 'allowed'] or ['success'=>false, 'error']
     */
    public function syncRooms(string $licenseKey, string $deviceId, int $roomCount): array
    {
        $license = License::where('license_key', $licenseKey)->first();

        if (!$license) {
            return ['success' => false, 'error' => 'License not found'];
        }

        if (!$license->isValid()) {
            return ['success' => false, 'error' => 'License is not active'];
        }

        // Get the room limit from features (max_users = rooms in this system, -1 = unlimited)
        $features  = $license->getAvailableFeatures();
        $roomLimit = (int) ($features['max_users'] ?? -1);

        // Enforce limit if not unlimited
        if ($roomLimit !== -1 && $roomCount > $roomLimit) {
            return [
                'success'    => false,
                'error'      => "Room limit exceeded: license allows {$roomLimit} rooms, HMS reports {$roomCount}.",
                'room_count' => $roomCount,
                'room_limit' => $roomLimit,
                'allowed'    => false,
            ];
        }

        // Persist the live count into assigned_rooms
        $license->update(['assigned_rooms' => $roomCount]);

        return [
            'success'    => true,
            'room_count' => $roomCount,
            'room_limit' => $roomLimit,   // -1 = unlimited
            'allowed'    => true,
        ];
    }
    /**
     * Sync room count from HMS.
     * HMS calls this after every room create/delete to report its live room count.
     * We validate against the license room limit and store the count server-side.
     */
    public function syncRooms(Request $request): JsonResponse
    {
        $key = 'license-sync-rooms:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'success'     => false,
                'error'       => 'Too many requests.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $validator = Validator::make($request->all(), [
            'token'      => 'required|string',
            'device_id'  => 'required|string',
            'room_count' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid request data',
                'details' => $validator->errors()
            ], 400);
        }

        $tokenValidation = $this->licenseService->validateToken($request->token);

        if (!$tokenValidation['valid']) {
            return response()->json(['success' => false, 'error' => $tokenValidation['error']], 401);
        }

        $license = $tokenValidation['license'];
        $result  = $this->licenseService->syncRooms(
            $license->license_key,
            $request->device_id,
            (int) $request->room_count
        );

        $statusCode = $result['success'] ? 200 : (isset($result['allowed']) && $result['allowed'] === false ? 403 : 422);
        return response()->json($result, $statusCode);
    }

    /**
     * Sync room count from HMS.
     * HMS calls this after every room create/delete to report its live room count.
     * We validate against the license room limit and store the count server-side.
     */
    public function syncRooms(Request $request): JsonResponse
    {
        $key = 'license-sync-rooms:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'success'     => false,
                'error'       => 'Too many requests.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $validator = Validator::make($request->all(), [
            'token'      => 'required|string',
            'device_id'  => 'required|string',
            'room_count' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid request data',
                'details' => $validator->errors()
            ], 400);
        }

        $tokenValidation = $this->licenseService->validateToken($request->token);

        if (!$tokenValidation['valid']) {
            return response()->json(['success' => false, 'error' => $tokenValidation['error']], 401);
        }

        $license = $tokenValidation['license'];
        $result  = $this->licenseService->syncRooms(
            $license->license_key,
            $request->device_id,
            (int) $request->room_count
        );

        $statusCode = $result['success'] ? 200 : (isset($result['allowed']) && $result['allowed'] === false ? 403 : 422);
        return response()->json($result, $statusCode);
    }
}