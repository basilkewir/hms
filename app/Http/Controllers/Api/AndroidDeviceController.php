<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceCommand;
use App\Models\DeviceHeartbeat;
use App\Models\IptvDevice;
use App\Models\Room;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AndroidDeviceController
 *
 * All endpoints consumed by the Android IPTV client.
 * No auth token needed â€” devices identify via device_id + registration_token.
 *
 * Endpoints:
 *   POST   /api/android/register          â†’ first-time registration by entering server URL
 *   POST   /api/android/heartbeat         â†’ periodic ping (every 30s), returns pending commands
 *   POST   /api/android/command-ack       â†’ device acks a command as executed or failed
 *   GET    /api/android/settings          â†’ pull latest pushed settings
 *   GET    /api/android/iptv-config       â†’ get Xtream credentials
 *   GET    /api/android/hotel-info        â†’ get hotel name, logo, etc.
 */
class AndroidDeviceController extends Controller
{
    // â”€â”€ Registration â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Called once when the user adds the server address in the Android app.
     * Creates or reclaims a device record and returns a device token.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'device_id'       => 'required|string|max:64',
            'mac_address'     => 'nullable|string|max:64',
            'device_name'     => 'nullable|string|max:128',
            'device_type'     => 'nullable|string|max:32',
            'android_version' => 'nullable|string|max:16',
            'app_version'     => 'nullable|string|max:16',
        ]);

        try {
            $device = IptvDevice::firstOrCreate(
                ['device_id' => $request->device_id],
                [
                    'mac_address'      => $request->mac_address,
                    'device_name'      => $request->device_name ?? $request->device_id,
                    'device_type'      => $request->device_type ?? 'android_tv',
                    'android_version'  => $request->android_version,
                    'app_version'      => $request->app_version,
                    'status'           => 'offline',
                    'is_active'        => true,
                    'registered_at'    => now(),
                    'settings_version' => 0,
                ]
            );

            // Update mutable fields on re-registration
            $device->update([
                'mac_address'     => $request->mac_address ?? $device->mac_address,
                'device_name'     => $request->device_name ?? $device->device_name,
                'android_version' => $request->android_version ?? $device->android_version,
                'app_version'     => $request->app_version ?? $device->app_version,
                'ip_address'      => $request->ip(),
                'last_seen'       => now(),
                'last_heartbeat'  => now(),
            ]);

            // Generate a fresh registration token
            $token = $device->generateRegistrationToken();

            // Build the settings payload to return immediately
            $settings = $this->buildSettingsPayload($device);

            return response()->json([
                'success'          => true,
                'message'          => 'Device registered successfully',
                'registration_token' => $token,
                'device' => [
                    'id'              => $device->id,
                    'device_id'       => $device->device_id,
                    'room_number'     => $device->room?->room_number,
                    'settings_version' => $device->settings_version,
                ],
                'settings'         => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Device registration failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    // â”€â”€ Heartbeat â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Called every 30 seconds by the Android app.
     * Updates device status and returns any pending commands.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $request->validate([
            'device_id'        => 'required|string',
            'registration_token' => 'required|string',
            'status'           => 'required|string|in:online,idle,buffering,error,playing',
            'current_channel'  => 'nullable|string',
            'app_version'      => 'nullable|string',
            'settings_version' => 'nullable|integer',
        ]);

        $device = $this->findDevice($request->device_id, $request->registration_token);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device not found or invalid token'], 401);
        }

        // Update device status
        $device->update([
            'status'          => $request->status,
            'ip_address'      => $request->ip(),
            'app_version'     => $request->app_version ?? $device->app_version,
            'last_heartbeat'  => now(),
            'last_seen'       => now(),
        ]);

        // Log heartbeat (keep last 288 per device = 24h at 5-min intervals)
        DeviceHeartbeat::create([
            'iptv_device_id'  => $device->id,
            'status'          => $request->status,
            'current_channel' => $request->current_channel,
            'app_version'     => $request->app_version,
            'ip_address'      => $request->ip(),
            'settings_version' => $request->settings_version ?? 0,
            'recorded_at'     => now(),
        ]);
        // Prune old heartbeats (keep last 500)
        DeviceHeartbeat::where('iptv_device_id', $device->id)
            ->orderBy('id', 'desc')
            ->skip(500)
            ->take(PHP_INT_MAX)
            ->delete();

        // Collect pending commands
        $commands = $device->pendingCommands()->map(function ($cmd) {
            $cmd->markDelivered();
            return [
                'id'      => $cmd->id,
                'type'    => $cmd->type,
                'payload' => $cmd->payload ?? [],
            ];
        })->values();

        // Check if device needs a settings update
        $needsSettingsUpdate = ($request->settings_version ?? 0) < $device->settings_version;

        return response()->json([
            'success'             => true,
            'commands'            => $commands,
            'needs_settings_update' => $needsSettingsUpdate,
            'server_time'         => now()->toIso8601String(),
        ]);
    }

    // â”€â”€ Command acknowledgement â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function commandAck(Request $request): JsonResponse
    {
        $request->validate([
            'device_id'        => 'required|string',
            'registration_token' => 'required|string',
            'command_id'       => 'required|integer',
            'result'           => 'required|in:executed,failed',
        ]);

        $device = $this->findDevice($request->device_id, $request->registration_token);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $cmd = DeviceCommand::where('id', $request->command_id)
                            ->where('iptv_device_id', $device->id)
                            ->first();

        if ($cmd) {
            $request->result === 'executed' ? $cmd->markExecuted() : $cmd->markFailed();
        }

        return response()->json(['success' => true]);
    }

    // â”€â”€ Settings pull â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Device pulls its full settings whenever heartbeat says needs_settings_update=true
     */
    public function getSettings(Request $request): JsonResponse
    {
        $request->validate([
            'device_id'        => 'required|string',
            'registration_token' => 'required|string',
        ]);

        $device = $this->findDevice($request->device_id, $request->registration_token);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $settings = $this->buildSettingsPayload($device);

        return response()->json([
            'success'          => true,
            'settings_version' => $device->settings_version,
            'settings'         => $settings,
        ]);
    }

    // â”€â”€ IPTV Config â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function getIptvConfig(Request $request): JsonResponse
    {
        $request->validate([
            'device_id'        => 'required|string',
            'registration_token' => 'required|string',
        ]);

        $device = $this->findDevice($request->device_id, $request->registration_token);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $settings = $this->getDbSettings([
            'xtream_url', 'xtream_username', 'xtream_password',
            'xtream_use_https',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'xtream_url'      => $settings['xtream_url'] ?? '',
                'xtream_username' => $settings['xtream_username'] ?? '',
                'xtream_password' => $settings['xtream_password'] ?? '',
                'use_https'       => (bool)($settings['xtream_use_https'] ?? false),
            ],
        ]);
    }

    // â”€â”€ Hotel Info (public, no token) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function hotelInfo(Request $request): JsonResponse
    {
        $settings = $this->getDbSettings([
            'hotel_name', 'hotel_address', 'hotel_phone', 'hotel_email',
            'hotel_logo', 'hotel_check_in_time', 'hotel_check_out_time',
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'name'           => $settings['hotel_name'] ?? config('app.name', 'Hotel'),
                'address'        => $settings['hotel_address'] ?? '',
                'phone'          => $settings['hotel_phone'] ?? '',
                'email'          => $settings['hotel_email'] ?? '',
                'logo_url'       => $settings['hotel_logo'] ?? '',
                'check_in_time'  => $settings['hotel_check_in_time'] ?? '14:00',
                'check_out_time' => $settings['hotel_check_out_time'] ?? '11:00',
                'server_time'    => now()->toIso8601String(),
                'timezone'       => config('app.timezone', 'UTC'),
            ],
        ]);
    }

    // â”€â”€ Weather (server-cached) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Returns weather data that was last fetched by the `weather:fetch` artisan
     * command (runs every 15 minutes via the scheduler).
     *
     * This endpoint is PUBLIC â€” no device token required â€” so devices can call
     * it even before registration, and the hotel logo / welcome screen shows
     * weather immediately.
     */
    public function weather(Request $request): JsonResponse
    {
        // 1. Try Laravel cache (fast, in-memory)
        $weather = Cache::get('weather_data');

        // 2. Fall back to the settings row (survives cache:clear)
        if (!$weather) {
            $raw = Setting::where('key', 'weather_cache')->value('value');
            if ($raw) {
                $weather = json_decode($raw, true);
                // Re-warm the cache so next request is fast
                if ($weather) {
                    Cache::put('weather_data', $weather, now()->addMinutes(30));
                }
            }
        }

        if (!$weather) {
            // No cached data yet â€” try to run the fetch command inline (first boot)
            try {
                Artisan::call('weather:fetch');
                $weather = Cache::get('weather_data');
            } catch (\Exception $e) {
                Log::warning('Inline weather:fetch failed: ' . $e->getMessage());
            }
        }

        if (!$weather) {
            return response()->json([
                'success' => false,
                'message' => 'Weather data not yet available. Run: php artisan weather:fetch',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'weather' => $weather,
        ]);
    }

    // â”€â”€ Private helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function findDevice(string $deviceId, string $token): ?IptvDevice
    {
        return IptvDevice::where('device_id', $deviceId)
                         ->where('registration_token', $token)
                         ->where('is_active', true)
                         ->first();
    }

    private function buildSettingsPayload(IptvDevice $device): array
    {
        $db = $this->getDbSettings([
            // Xtream Codes
            'xtream_url', 'xtream_username', 'xtream_password', 'xtream_use_https',
            // Hotel branding
            'hotel_name', 'hotel_logo', 'hotel_address', 'hotel_phone',
            'hotel_primary_color', 'hotel_welcome_message', 'welcome_background_url',
            // Weather widget
            'weather_api_key', 'weather_city', 'weather_units', 'weather_enabled',
            // TV UI behaviour
            'iptv_ui_theme', 'iptv_show_epg', 'iptv_auto_launch_seconds',
            'iptv_show_clock', 'iptv_show_room_number',
            'iptv_enable_vod', 'iptv_enable_series', 'iptv_enable_radio',
            'iptv_parental_pin',
            // Security
            'admin_pin',
        ]);

        // Per-device overrides take precedence over global settings.
        // Each device has its OWN xtream_username/password stored in pushed_settings.
        $pushed = $device->pushed_settings ?? [];

        return array_merge([
            // â”€â”€ Xtream Codes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            'xtream_url'              => $db['xtream_url'] ?? '',
            'xtream_username'         => $db['xtream_username'] ?? '',   // overridden per-device
            'xtream_password'         => $db['xtream_password'] ?? '',   // overridden per-device
            'xtream_use_https'        => (bool)($db['xtream_use_https'] ?? false),

            // â”€â”€ Hotel branding â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            'hotel_name'              => $db['hotel_name'] ?? '',
            'hotel_logo_url'          => $db['hotel_logo'] ?? '',
            'hotel_address'           => $db['hotel_address'] ?? '',
            'hotel_phone'             => $db['hotel_phone'] ?? '',
            'hotel_primary_color'     => $db['hotel_primary_color'] ?? '#FFD700',
            'hotel_welcome_message'   => $db['hotel_welcome_message'] ?? 'Welcome',
            'welcome_background_url'  => $db['welcome_background_url'] ?? '',

            // â”€â”€ Weather widget â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            'weather_enabled'         => (bool)($db['weather_enabled'] ?? true),
            'weather_api_key'         => $db['weather_api_key'] ?? '',
            'weather_city'            => $db['weather_city'] ?? '',
            'weather_units'           => $db['weather_units'] ?? 'metric',

            // â”€â”€ TV UI & behaviour â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            'ui_theme'                => $db['iptv_ui_theme'] ?? 'dark',
            'show_epg'                => (bool)($db['iptv_show_epg'] ?? true),
            'auto_launch_seconds'     => (int)($db['iptv_auto_launch_seconds'] ?? 15),
            'show_clock'              => (bool)($db['iptv_show_clock'] ?? true),
            'show_room_number'        => (bool)($db['iptv_show_room_number'] ?? true),
            'enable_vod'              => (bool)($db['iptv_enable_vod'] ?? true),
            'enable_series'           => (bool)($db['iptv_enable_series'] ?? true),
            'enable_radio'            => (bool)($db['iptv_enable_radio'] ?? true),
            'parental_pin'            => $db['iptv_parental_pin'] ?? '',

            // â”€â”€ Security â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            'admin_pin'               => $db['admin_pin'] ?? '1234',

            // â”€â”€ Device context â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            'room_number'             => $device->room?->room_number ?? '',
            'package'                 => $device->package ?? 'basic',
            'settings_version'        => $device->settings_version ?? 0,
        ], $pushed);  // pushed_settings (per-device username/password etc.) win
    }

    /**
     * Check if the system has an active license.
     * Called by Android TV apps to validate without per-device keys.
     */
    public function licenseCheck(Request $request): JsonResponse
    {
        try {
            // Try to find an active license record
            $license = null;
            if (class_exists(\App\Models\License::class)) {
                $license = \App\Models\License::where('status', 'active')->first();
            }

            // Try LicenseValidationService if available
            $isLicensed = false;
            if (class_exists(\App\Services\LicenseValidationService::class)) {
                $isLicensed = \App\Services\LicenseValidationService::isSystemLicensed();
            } elseif ($license) {
                $isLicensed = true;
            } else {
                // Fallback: if no license table exists, treat as licensed (self-hosted)
                $isLicensed = true;
            }

            $hotelName = '';
            try {
                $hotelName = \App\Models\Setting::where('key', 'hotel_name')->value('value') ?? '';
            } catch (\Exception $e) {}

            return response()->json([
                'licensed'    => $isLicensed,
                'plan'        => $license?->plan ?? 'standard',
                'expires_at'  => $license?->expires_at ?? null,
                'max_devices' => $license?->max_devices ?? 999,
                'hotel_name'  => $hotelName,
            ]);
        } catch (\Exception $e) {
            // If anything fails, grant access (don't lock out TVs due to server error)
            return response()->json([
                'licensed'    => true,
                'plan'        => 'standard',
                'expires_at'  => null,
                'max_devices' => 999,
                'hotel_name'  => '',
            ]);
        }
    }
    private function getDbSettings(array $keys): array
    {
        try {
            return Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}

