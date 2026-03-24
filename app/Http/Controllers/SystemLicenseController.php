<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LicenseValidationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SystemLicenseController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseValidationService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function showActivation()
    {
        // Check if license already exists
        $license = \App\Models\License::where('status', 'active')->first();
        
        if ($license) {
            // Redirect to settings page if license exists
            return redirect()->route('admin.settings')->with('message', 'License is already activated');
        }
        
        return Inertia::render('License/Activate');
    }

    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'hotel_name' => 'nullable|string',
            'device_info' => 'required|array'
        ]);

        $deviceInfo = $request->device_info;
        $deviceInfo['hotel_name'] = $request->hotel_name;

        $result = $this->licenseService->validateLicense(
            $request->license_key,
            $request->hotel_name,
            $deviceInfo
        );

        if ($result['valid']) {
            return response()->json([
                'success' => true,
                'message' => 'License activated successfully',
                'license' => $result['license'],
                'token' => $result['token'] ?? null
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    public function status()
    {
        $license = \App\Models\License::where('status', 'active')->first();
        
        if (!$license) {
            return response()->json([
                'licensed' => false,
                'status' => null
            ]);
        }

        try {
            $licensed = $this->licenseService->isSystemLicensed();
        } catch (\Throwable $e) {
            Log::warning('License status verification failed (online check required): ' . $e->getMessage());
            $licensed = false;
        }

        if (!$licensed) {
            return response()->json([
                'licensed' => false,
                'status' => null,
                'message' => 'Online license verification failed'
            ]);
        }

        $licenseStatus = $this->licenseService->getLicenseStatus();

        return response()->json([
            'licensed' => true,
            'status' => $licenseStatus['status'] ?? $license->license_data,
        ]);
    }

    private function verifyLicenseOnline($licenseKey)
    {
        try {
            // For demo licenses only, return stored data
            if (str_contains($licenseKey, 'DEMO')) {
                return null; // Use stored data
            }

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 5
            ])->withHeaders([
                'User-Agent' => 'HotelManagement/1.0.0 (Windows; Hotel Management System)'
            ])->get('https://kewirdev.com/api/license/info', [
                'license_key' => $licenseKey
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            if (!$data['success']) {
                return null;
            }

            $info = $data['data'];
            
            // Get actual device usage from API or calculate from database
            $deviceUsage = $info['device_usage'] ?? ['current' => 1, 'maximum' => 3];
            $totalRooms = \App\Models\Room::count() ?: 0;
            $maxRooms = $info['device_limits']['backend'] ?? 100;
            
            return [
                'license_key' => $licenseKey,
                'hotel_name' => $info['hotel_name'] ?? 'Hotel Management System',
                'license_type' => strtoupper($info['license_type'] ?? 'BASIC'),
                'status' => strtoupper($info['status'] ?? 'ACTIVE'),
                'expires_at' => isset($info['expires_at']) ? date('n/j/Y', strtotime($info['expires_at'])) : 'Never',
                'created_at' => date('n/j/Y'),
                'features' => $info['features'] ?? [],
                'device_limits' => [
                    'android_tv' => $info['device_limits']['android_tv'] ?? 20,
                    'smart_tv' => $info['device_limits']['smart_tv'] ?? 15,
                    'rooms' => $maxRooms,
                    'admin_panel' => $info['device_limits']['admin_panel'] ?? 3
                ],
                'device_allocation' => [
                    ['type' => 'Rooms', 'used' => $totalRooms, 'limit' => $maxRooms]
                ],
                'total_used' => $totalRooms,
                'total_limit' => $maxRooms,
                'max_rooms' => $maxRooms,
                'validated_at' => now(),
                'is_valid' => $info['is_valid'] ?? true,
                'online_verified' => true
            ];

        } catch (\Exception $e) {
            Log::error('Online license verification failed: ' . $e->getMessage());
            return null;
        }
    }

    public function removeLicense()
    {
        try {
            // Delete from database
            \App\Models\License::where('status', 'active')->delete();

            // Clear any cached license data
            Cache::forget('license_status');
            Cache::forget('system_licensed');

            return response()->json([
                'success' => true,
                'message' => 'License removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('License removal failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove license'
            ], 500);
        }
    }

    /**
     * Validate JWT token
     */
    public function validateToken(Request $request)
    {
        $result = $this->licenseService->validateToken();

        if ($result['valid']) {
            return response()->json([
                'success' => true,
                'license_type' => $result['license_type'],
                'features' => $result['features'],
                'expires_at' => $result['expires_at']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 401);
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken(Request $request)
    {
        $result = $this->licenseService->refreshToken();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'token' => $result['token'],
                'expires_at' => $result['expires_at']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Send heartbeat to keep connection alive
     */
    public function sendHeartbeat(Request $request)
    {
        $result = $this->licenseService->sendHeartbeat();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'server_time' => $result['server_time'],
                'next_heartbeat' => $result['next_heartbeat']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Get license information
     */
    public function getLicenseInfo(Request $request)
    {
        $licenseKey = $request->query('license_key');

        if (empty($licenseKey)) {
            return response()->json([
                'success' => false,
                'message' => 'License key is required'
            ], 400);
        }

        $result = $this->licenseService->getLicenseInfo($licenseKey);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 404);
    }
}
