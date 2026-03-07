<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseValidationService
{
    private $licenseServer = 'https://kewirdev.com/api/license';
    private $jwtToken = null;
    private $deviceId = null;

    public function __construct()
    {
        $this->loadStoredToken();
    }

    private function loadStoredToken()
    {
        $license = \App\Models\License::where('status', 'active')->first();
        if ($license && isset($license->license_data['token'])) {
            $this->jwtToken = $license->license_data['token'];
            $this->deviceId = $license->license_data['device_id'] ?? null;
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
            // Generate device ID if not provided
            $deviceId = $deviceInfo['device_id'] ?? $this->generateDeviceId();

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
                'User-Agent' => 'HotelManagement/1.0.0 (Windows; Hotel Management System)'
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

    public function getLicenseStatus()
    {
        $license = \App\Models\License::where('status', 'active')->first();
        
        if (!$license || !$license->license_data) {
            return ['licensed' => false, 'status' => null];
        }

        return ['licensed' => true, 'status' => $license->license_data];
    }

    public function isSystemLicensed()
    {
        $status = $this->getLicenseStatus();
        return $status['licensed'];
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
                'device_id' => $this->deviceId
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
                'device_id' => $this->deviceId
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
}
