<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseValidationService
{
    private $licenseServer;
    private $jwtToken = null;
    private $deviceId = null;

    public function __construct()
    {
        $this->licenseServer = env('LICENSE_SERVER_URL', 'https://kewirdev.com/api/license');
        $this->loadStoredToken();
    }

    /**
     * Compute the HMAC-SHA256 request signature expected by kewirdev.com.
     * The key is taken from LICENSE_SIGNATURE_SECRET in .env (= kewirdev APP_KEY).
     */
    private function computeSignature(array $payload): string
    {
        $secret = config('services.license.signature_secret', '');

        // Laravel APP_KEY is stored as "base64:<base64data>" — decode it
        if (str_starts_with($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }

        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    private function loadStoredToken()
    {
        // Load from ANY license record that has a token — do not restrict to status='active'
        // because isSystemLicensed() may temporarily set status='inactive' during an online check
        $license = \App\Models\License::whereNotNull('license_key')->latest('id')->first();
        if ($license && isset($license->license_data['token'])) {
            $this->jwtToken = $license->license_data['token'];
            // Cast to string — old records may have stored the integer kewirdev device row-ID
            $this->deviceId = isset($license->license_data['device_id'])
                ? (string) $license->license_data['device_id']
                : null;
        }
    }

    private function storeToken($token, $deviceId, $expiresAt)
    {
        $license = \App\Models\License::where('status', 'active')->first();
        if ($license) {
            $licenseData = $license->license_data ?? [];
            $licenseData['token'] = $token;
            $licenseData['device_id'] = $deviceId;
            $licenseData['token_expires_at'] = $expiresAt;
            $license->update(['license_data' => $licenseData]);
        }
        $this->jwtToken = $token;
        $this->deviceId = $deviceId;
    }

    private function clearToken()
    {
        $this->jwtToken = null;
        $this->deviceId = null;
        $license = \App\Models\License::where('status', 'active')->first();
        if ($license) {
            $licenseData = $license->license_data ?? [];
            unset($licenseData['token']);
            unset($licenseData['device_id']);
            unset($licenseData['token_expires_at']);
            $license->update(['license_data' => $licenseData]);
        }
    }

    private function generateDeviceId()
    {
        if ($this->deviceId) {
            return $this->deviceId;
        }

        // Generate a unique device ID based on system information
        $systemInfo = [
            'domain' => php_uname('n'), // Use php_uname('n') instead of gethostname()
            'mac_address' => $this->getMacAddress(),
            'os' => php_uname(),
            'php_version' => PHP_VERSION,
            'app_version' => config('app.version', '1.0.0')
        ];

        return 'device-' . substr(md5(json_encode($systemInfo)), 0, 16);
    }

    private function getMacAddress()
    {
        // Try to get MAC address - this is platform dependent
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows implementation
            try {
                $mac = shell_exec('getmac');
                if ($mac && preg_match('/([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})/', $mac, $matches)) {
                    return str_replace(['-', ':'], '', $matches[0]);
                }
            } catch (\Exception $e) {
                // Fallback
            }
        } else {
            // Linux/Mac implementation
            try {
                $interfaces = shell_exec('ifconfig -a');
                if ($interfaces && preg_match('/ether ([0-9a-f:]{17})/i', $interfaces, $matches)) {
                    return str_replace(':', '', $matches[1]);
                }
            } catch (\Exception $e) {
                // Fallback
            }
        }

        // Fallback - generate a consistent ID
        return substr(md5(php_uname() . php_uname('n')), 0, 12);
    }

    public function validateLicense($licenseKey, $hotelName = null, $deviceInfo = [])
    {
        // For demo licenses only, use offline mode immediately
        if (str_contains($licenseKey, 'DEMO')) {
            return $this->getOfflineLicense($licenseKey, $hotelName);
        }

        try {
            // Generate device ID if not provided — always a string
            $deviceId = (string) ($deviceInfo['device_id'] ?? $this->generateDeviceId());

            // Prepare device information
            $deviceData = [
                'license_key' => $licenseKey,
                'device_id' => $deviceId,
                'device_type' => $deviceInfo['device_type'] ?? 'management_backend',
                'device_name' => $deviceInfo['device_name'] ?? 'Hotel Management System',
                'device_model' => $deviceInfo['device_model'] ?? 'Server',
                'device_os' => $deviceInfo['device_os'] ?? php_uname('s'),
                'device_os_version' => $deviceInfo['device_os_version'] ?? php_uname('r'),
                'app_version' => $deviceInfo['app_version'] ?? config('app.version', '1.0.0'),
                'mac_address' => $deviceInfo['mac_address'] ?? $this->getMacAddress(),
                'metadata' => [
                    'hotel_name' => $hotelName,
                    'system_info' => php_uname(),
                    'php_version' => PHP_VERSION
                ]
            ];

            // Call the validate endpoint
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 10,
                'connect_timeout' => 5
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'HotelManagement/1.0.0 (Windows; Hotel Management System)',
                'X-License-Signature' => $this->computeSignature($deviceData),
            ])->post($this->licenseServer . '/validate', $deviceData);

            if (!$response->successful()) {
                $statusCode = $response->status();
                if ($statusCode === 404) {
                    return ['valid' => false, 'message' => 'License not found'];
                } elseif ($statusCode === 401) {
                    return ['valid' => false, 'message' => 'Invalid license key'];
                } elseif ($statusCode === 429) {
                    return ['valid' => false, 'message' => 'Too many validation attempts. Please try again later.'];
                } else {
                    return ['valid' => false, 'message' => 'License validation failed: ' . $response->body()];
                }
            }

            $responseData = $response->json();

            if (!$responseData['success']) {
                return ['valid' => false, 'message' => $responseData['message'] ?? 'License validation failed'];
            }

            // Extract features and device limits from response
            $features = $responseData['features'] ?? [];
            $deviceLimits = [
                'android_tv' => $features['max_channels'] ?? 50,
                'smart_tv' => $features['max_channels'] ?? 50,
                'rooms' => $features['max_users'] ?? 25,
                'admin_panel' => 3
            ];

            $totalRooms = \App\Models\Room::count() ?: 0;
            $maxRooms = $deviceLimits['rooms'];

            // Prepare license data
            $licenseData = [
                'license_key' => $licenseKey,
                'hotel_name' => $hotelName ?: ($responseData['hotel_name'] ?? 'Hotel Management System'),
                'license_type' => strtoupper($responseData['license_type'] ?? 'BASIC'),
                'status' => 'ACTIVE',
                'expires_at' => isset($responseData['expires_at']) ? date('n/j/Y', strtotime($responseData['expires_at'])) : 'Never',
                'created_at' => now()->format('n/j/Y'),
                'features' => $features,
                'device_limits' => $deviceLimits,
                'device_allocation' => [
                    ['type' => 'Rooms', 'used' => $totalRooms, 'limit' => $maxRooms]
                ],
                'total_used' => $totalRooms,
                'total_limit' => $maxRooms,
                'max_rooms' => $maxRooms,
                'validated_at' => now(),
                'is_valid' => true,
                'token' => $responseData['token'] ?? null,
                'device_id' => $deviceId,
                'token_expires_at' => $responseData['expires_at'] ?? null,
                'processing_time' => $responseData['processing_time'] ?? null,
                'timestamp' => $responseData['timestamp'] ?? now()->toISOString()
            ];

            // Store JWT token
            if (isset($responseData['token'])) {
                $this->storeToken($responseData['token'], $deviceId, $responseData['expires_at']);
            }

            // Store in database
            \App\Models\License::updateOrCreate(
                ['license_key' => $licenseKey],
                [
                    'license_data' => $licenseData,
                    'product_name' => 'Hotel Management System',
                    'status' => 'active',
                    'customer_name' => $licenseData['hotel_name'],
                    'customer_email' => 'admin@' . str_replace(' ', '', strtolower($licenseData['hotel_name'])) . '.com',
                    'license_type' => $licenseData['license_type'],
                    'expires_at' => isset($responseData['expires_at']) ? $responseData['expires_at'] : null,
                    'issued_at' => now(),
                    'activated_at' => now(),
                    'last_validated_at' => now(),
                    'device_info' => $deviceInfo,
                    'hardware_fingerprint' => $deviceId
                ]
            );

            return [
                'valid' => true,
                'license' => $licenseData,
                'message' => 'License activated successfully',
                'token' => $responseData['token'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('License validation error: ' . $e->getMessage());

            // Check if this is a network-related error
            if (str_contains($e->getMessage(), 'cURL error') ||
                str_contains($e->getMessage(), 'Connection refused') ||
                str_contains($e->getMessage(), 'Failed to connect') ||
                str_contains($e->getMessage(), 'Name or service not known') ||
                str_contains($e->getMessage(), 'Connection timed out')) {
                return $this->getOfflineLicense($licenseKey, $hotelName);
            }

            return ['valid' => false, 'message' => 'License validation failed: ' . $e->getMessage()];
        }
    }

    private function getOfflineLicense($licenseKey, $hotelName = null)
    {
        $totalRooms = \App\Models\Room::count() ?: 0;
        $maxRooms = 30; // Default for demo
        $licenseData = [
            'license_key' => $licenseKey,
            'hotel_name' => $hotelName ?: 'Grand Hotel Cameroon',
            'license_type' => 'ENTERPRISE',
            'status' => 'ACTIVE',
            'expires_at' => '6/19/2026',
            'created_at' => now()->format('n/j/Y'),
            'features' => [
                'max_channels' => -1,
                'max_users' => -1,
                'analytics' => true,
                'custom_branding' => true,
                'api_access' => true
            ],
            'device_limits' => [
                'rooms' => $maxRooms
            ],
            'device_allocation' => [
                ['type' => 'Rooms', 'used' => $totalRooms, 'limit' => $maxRooms]
            ],
            'total_used' => $totalRooms,
            'total_limit' => $maxRooms,
            'max_rooms' => $maxRooms,
            'validated_at' => now(),
            'is_valid' => true,
            'offline_mode' => true
        ];

        \App\Models\License::updateOrCreate(
            ['license_key' => $licenseKey],
            [
                'license_data' => $licenseData,
                'status' => 'active',
                'customer_name' => $licenseData['hotel_name'],
                'customer_email' => 'demo@hotel.com',
                'license_type' => 'ENTERPRISE',
                'product_name' => 'Hotel Management System',
                'max_devices' => $licenseData['total_limit'],
                'max_rooms' => 100,
                'max_channels' => -1
            ]
        );

        return [
            'valid' => true,
            'license' => $licenseData,
            'message' => 'License activated in offline mode'
        ];
    }

    /**
     * Return the room/user limits from the active license features.
     * -1 means unlimited.
     */
    public function getLicenseLimits(): array
    {
        $license = \App\Models\License::where('status', 'active')->first();
        if (!$license || !$license->license_data) {
            return ['max_rooms' => -1, 'max_channels' => -1]; // no license = unlimited (demo/dev)
        }
        $features = $license->license_data['features'] ?? [];
        return [
            'max_rooms'    => (int) ($features['max_users']    ?? -1), // kewirdev: max_users = room count
            'max_channels' => (int) ($features['max_channels'] ?? -1),
        ];
    }

    public function getLicenseStatus()
    {
        $license = \App\Models\License::where('status', 'active')->first();

        if (!$license || !$license->license_data) {
            return ['licensed' => false, 'status' => null];
        }

        $ld = $license->license_data;

        // If license_data is missing key display fields (e.g. created by check_license.php or
        // a bare upsert), enrich it live from GET /info using the stored token.
        $needsEnrichment = empty($ld['hotel_name']) || empty($ld['license_type']) || empty($ld['features']);

        if ($needsEnrichment && !empty($ld['token'])) {
            try {
                $response = Http::withOptions([
                    'verify'          => false,
                    'timeout'         => 8,
                    'connect_timeout' => 4,
                ])->withHeaders([
                    'Authorization' => 'Bearer ' . $ld['token'],
                    'User-Agent'    => 'HotelManagement/1.0.0 (Hotel Management System)',
                    'Accept'        => 'application/json',
                ])->get($this->licenseServer . '/info');

                if ($response->successful()) {
                    $info = $response->json('data', []);
                    $features = $info['features'] ?? [];
                    $deviceUsage = $info['device_usage'] ?? [];

                    $ld['hotel_name']   = $info['hotel_name'] ?? $license->customer_name ?? 'Hotel';
                    $ld['license_type'] = strtoupper($info['license_type'] ?? $license->license_type ?? 'PERPETUAL');
                    $ld['status']       = strtoupper($info['status'] ?? 'ACTIVE');
                    $ld['expires_at']   = $info['expires_at'] ?? 'Never';
                    $ld['features']     = $features;

                    $license->update([
                        'license_data'      => $ld,
                        'customer_name'     => $ld['hotel_name'],
                        'license_type'      => $ld['license_type'],
                        'last_validated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('getLicenseStatus enrichment failed: ' . $e->getMessage());
            }
        }

        // Always compute live room counts from DB so the page is always accurate
        $features    = $ld['features'] ?? [];
        $maxRooms    = (int) ($features['max_users']    ?? -1); // -1 = unlimited
        $maxChannels = (int) ($features['max_channels'] ?? -1);
        $totalRooms  = \App\Models\Room::count();

        $ld['rooms_used']  = $totalRooms;
        $ld['rooms_limit'] = $maxRooms;
        $ld['device_allocation'] = [
            ['type' => 'Rooms',    'used' => $totalRooms,                           'limit' => $maxRooms],
            ['type' => 'Channels', 'used' => $ld['total_used'] ?? 0,                'limit' => $maxChannels],
        ];
        $ld['total_used']  = $totalRooms;
        $ld['total_limit'] = $maxRooms;

        return ['licensed' => true, 'status' => $ld];
    }

    /**
     * Check whether the system has a valid, active license.
     *
     * Strategy (3-tier):
     *  1. If a JWT token is stored → call GET /info with Authorization: Bearer <token>
     *     - 200 + ACTIVE  → licensed ✓ (update DB to active)
     *     - 200 + not ACTIVE → not licensed ✗ (revoked/expired on server)
     *     - 401 → token expired, fall through to tier 2
     *     - 4xx other / network error → fall through to offline fallback
     *  2. No token (or token expired) → re-validate via POST /validate (same as activation)
     *     - 200 + success → licensed ✓, store new token
     *     - 4xx / failure → not licensed ✗
     *  3. Network unreachable → offline grace: trust local DB record
     */
    public function isSystemLicensed(): bool
    {
        // Load the most recent license record regardless of status
        $license = \App\Models\License::whereNotNull('license_key')->latest('id')->first();

        if (!$license || !$license->license_key) {
            return false;
        }

        $licenseKey = $license->license_key;

        // Skip online check for DEMO keys
        if (str_contains(strtoupper($licenseKey), 'DEMO')) {
            return true;
        }

        // --- Tier 1: Bearer token → GET /info ---
        $token = $license->license_data['token'] ?? $this->jwtToken;
        if ($token) {
            try {
                $response = Http::withOptions([
                    'verify'          => false,
                    'timeout'         => 8,
                    'connect_timeout' => 4,
                ])->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'User-Agent'    => 'HotelManagement/1.0.0 (Hotel Management System)',
                    'Accept'        => 'application/json',
                ])->get($this->licenseServer . '/info');

                if ($response->successful()) {
                    $data = $response->json();
                    if (!($data['success'] ?? false)) {
                        Log::warning('License /info returned success=false', ['key' => substr($licenseKey, 0, 8)]);
                        return $this->offlineFallback($license);
                    }
                    $remoteStatus = strtoupper($data['data']['status'] ?? '');
                    if ($remoteStatus !== 'ACTIVE') {
                        // Revoked or suspended on the server
                        $license->update(['status' => 'inactive']);
                        Log::warning('License is not ACTIVE on kewirdev.com', ['status' => $remoteStatus]);
                        return false;
                    }
                    // Check server-side expiry
                    $expiresAt = $data['data']['expires_at'] ?? null;
                    if ($expiresAt && $expiresAt !== 'Never' && strtotime($expiresAt) < time()) {
                        $license->update(['status' => 'inactive']);
                        Log::warning('License has expired', ['expires_at' => $expiresAt]);
                        return false;
                    }
                    // All good — ensure DB reflects active
                    $license->update(['status' => 'active', 'last_validated_at' => now()]);
                    return true;
                }

                $statusCode = $response->status();
                if ($statusCode === 401) {
                    // Token expired — fall through to tier 2
                    Log::info('Bearer token expired, will re-validate via POST /validate');
                } elseif ($statusCode === 404) {
                    // License not found on server — definitive revocation
                    $license->update(['status' => 'inactive']);
                    Log::warning('License not found on kewirdev.com (GET /info)', ['http' => $statusCode]);
                    return false;
                } elseif ($statusCode === 403) {
                    // Could be security check — use offline fallback
                    Log::warning('kewirdev.com /info returned 403, using offline fallback');
                    return $this->offlineFallback($license);
                } else {
                    // 5xx or unexpected — fall through to offline
                    Log::warning('kewirdev.com /info returned unexpected status', ['http' => $statusCode]);
                    return $this->offlineFallback($license);
                }
            } catch (\Throwable $e) {
                Log::warning('License /info network error: ' . $e->getMessage());
                return $this->offlineFallback($license);
            }
        }

        // --- Tier 2: No token (or token expired) → POST /validate ---
        try {
            $deviceId = (string) ($license->license_data['device_id'] ?? $this->generateDeviceId());

            $validatePayload = [
                'license_key'      => $licenseKey,
                'device_id'        => $deviceId,
                'device_type'      => 'management_backend',
                'device_name'      => 'Hotel Management System',
                'device_model'     => 'Server',
                'device_os'        => php_uname('s'),
                'device_os_version'=> php_uname('r'),
                'app_version'      => config('app.version', '1.0.0'),
                'mac_address'      => $this->getMacAddress(),
                'metadata'         => [
                    'system_info'  => php_uname(),
                    'php_version'  => PHP_VERSION,
                ],
            ];

            $response = Http::withOptions([
                'verify'          => false,
                'timeout'         => 10,
                'connect_timeout' => 5,
            ])->withHeaders([
                'Content-Type'       => 'application/json',
                'User-Agent'         => 'HotelManagement/1.0.0 (Hotel Management System)',
                'Accept'             => 'application/json',
                'X-License-Signature'=> $this->computeSignature($validatePayload),
            ])->post($this->licenseServer . '/validate', $validatePayload);

            if ($response->successful()) {
                $data = $response->json();
                if (!($data['success'] ?? false)) {
                    Log::warning('License POST /validate returned success=false', ['key' => substr($licenseKey, 0, 8)]);
                    return $this->offlineFallback($license);
                }
                // Store new token if returned
                if (!empty($data['token'])) {
                    $this->storeToken($data['token'], $deviceId, $data['expires_at'] ?? null);
                }
                $license->update(['status' => 'active', 'last_validated_at' => now()]);
                return true;
            }

            $statusCode = $response->status();
            if (in_array($statusCode, [401, 404])) {
                // Definitive rejection — invalid key or not found
                $license->update(['status' => 'inactive']);
                Log::warning('License rejected by POST /validate', ['http' => $statusCode, 'body' => substr($response->body(), 0, 200)]);
                return false;
            }

            // 403 = security check failure (signature, fingerprint, rate-limit, etc.)
            // or 5xx — use offline fallback rather than marking inactive
            Log::warning('POST /validate returned ' . $statusCode . ', using offline fallback', ['body' => substr($response->body(), 0, 200)]);
            return $this->offlineFallback($license);

        } catch (\Throwable $e) {
            Log::warning('License POST /validate network error: ' . $e->getMessage());
        }

        // --- Tier 3: Offline fallback ---
        return $this->offlineFallback($license);
    }

    /**
     * Offline grace-period fallback.
     * Trusts the locally stored license_data when the server is unreachable.
     * Does NOT mark the DB record as inactive (avoids false lockouts on network hiccups).
     */
    private function offlineFallback(\App\Models\License $license): bool
    {
        $storedStatus = strtoupper($license->license_data['status'] ?? '');
        if ($storedStatus !== 'ACTIVE') {
            return false;
        }

        $localExpiry = $license->license_data['expires_at'] ?? null;
        if ($localExpiry && $localExpiry !== 'Never') {
            $ts = strtotime($localExpiry);
            if ($ts && $ts < time()) {
                return false;
            }
        }

        // Restore DB status to active so cache stores 'true' (network was just down)
        if ($license->status !== 'active') {
            $license->update(['status' => 'active']);
        }
        return true;
    }

    public function removeLicense()
    {
        \App\Models\License::where('status', 'active')->delete();
        return true;
    }

    /**
     * Validate JWT token
     */
    public function validateToken()
    {
        if (!$this->jwtToken) {
            return ['valid' => false, 'message' => 'No token available'];
        }

        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 5
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'HotelManagement/1.0.0 (Windows; Hotel Management System)'
            ])->post($this->licenseServer . '/validate-token', [
                'token' => $this->jwtToken
            ]);

            if (!$response->successful()) {
                return ['valid' => false, 'message' => 'Token validation failed'];
            }

            $responseData = $response->json();

            if (!$responseData['success']) {
                return ['valid' => false, 'message' => $responseData['message'] ?? 'Token validation failed'];
            }

            return [
                'valid' => true,
                'license_type' => $responseData['license_type'] ?? null,
                'features' => $responseData['features'] ?? [],
                'expires_at' => $responseData['expires_at'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Token validation error: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Token validation failed'];
        }
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken()
    {
        if (!$this->jwtToken || !$this->deviceId) {
            return ['success' => false, 'message' => 'No token available to refresh'];
        }

        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 5
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'HotelManagement/1.0.0 (Windows; Hotel Management System)'
            ])->post($this->licenseServer . '/refresh-token', [
                'token' => $this->jwtToken,
                'device_id' => (string) $this->deviceId
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Token refresh failed'];
            }

            $responseData = $response->json();

            if (!$responseData['success']) {
                return ['success' => false, 'message' => $responseData['message'] ?? 'Token refresh failed'];
            }

            // Store the new token
            $this->storeToken($responseData['token'], $this->deviceId, $responseData['expires_at']);

            return [
                'success' => true,
                'token' => $responseData['token'],
                'expires_at' => $responseData['expires_at']
            ];

        } catch (\Exception $e) {
            Log::error('Token refresh error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Token refresh failed'];
        }
    }

    /**
     * Send heartbeat to keep connection alive
     */
    public function sendHeartbeat()
    {
        if (!$this->jwtToken || !$this->deviceId) {
            return ['success' => false, 'message' => 'No active license to send heartbeat'];
        }

        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 5
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'HotelManagement/1.0.0 (Windows; Hotel Management System)'
            ])->post($this->licenseServer . '/heartbeat', [
                'token' => $this->jwtToken,
                'device_id' => (string) $this->deviceId
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Heartbeat failed'];
            }

            $responseData = $response->json();

            if (!$responseData['success']) {
                return ['success' => false, 'message' => $responseData['message'] ?? 'Heartbeat failed'];
            }

            return [
                'success' => true,
                'message' => $responseData['message'] ?? 'Heartbeat received',
                'server_time' => $responseData['server_time'] ?? null,
                'next_heartbeat' => $responseData['next_heartbeat'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Heartbeat error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Heartbeat failed'];
        }
    }

    /**
     * Get license information
     */
    public function getLicenseInfo($licenseKey)
    {
        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 5
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'HotelManagement/1.0.0 (Windows; Hotel Management System)'
            ])->get($this->licenseServer . '/info', [
                'license_key' => $licenseKey
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Failed to get license information'];
            }

            $responseData = $response->json();

            if (!$responseData['success']) {
                return ['success' => false, 'message' => $responseData['message'] ?? 'Failed to get license information'];
            }

            return [
                'success' => true,
                'data' => $responseData['data']
            ];

        } catch (\Exception $e) {
            Log::error('License info error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get license information'];
        }
    }

    /**
     * Check if token is expired or about to expire
     */
    public function shouldRefreshToken()
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

        // Refresh if token expires in less than 5 minutes
        return ($expiresAt - time()) < 300;
    }

    /**
     * Report the current room count to the kewirdev license server.
     * The server validates against the license limit and stores the count.
     * Returns ['success'=>true, 'room_count'=>N, 'room_limit'=>M, 'allowed'=>true]
     * or ['success'=>false, 'error'=>'...', 'allowed'=>false] when limit exceeded.
     */
    public function syncRooms(int $roomCount): array
    {
        // Always reload the freshest token from DB — the constructor token may be stale
        // if the token was refreshed after construction (e.g. in the same request cycle).
        $this->loadStoredToken();

        if (!$this->jwtToken || !$this->deviceId) {
            // No active license — allow freely (offline/dev mode)
            return ['success' => true, 'room_count' => $roomCount, 'room_limit' => -1, 'allowed' => true];
        }

        try {
            $response = Http::withOptions([
                'verify'  => false,
                'timeout' => 8,
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent'   => 'HotelManagement/1.0.0 (Windows; Hotel Management System)',
            ])->post($this->licenseServer . '/sync-rooms', [
                'token'      => $this->jwtToken,
                'device_id'  => (string) $this->deviceId,
                'room_count' => $roomCount,
            ]);

            $data = $response->json() ?? [];

            if (!$response->successful() || empty($data['success'])) {
                // On network errors or server-side limit exceeded:
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
            Log::warning('syncRooms error (non-fatal): ' . $e->getMessage());
            // Network failure — do not block room creation, just log
            return ['success' => true, 'room_count' => $roomCount, 'room_limit' => -1, 'allowed' => true];
        }
    }
}
