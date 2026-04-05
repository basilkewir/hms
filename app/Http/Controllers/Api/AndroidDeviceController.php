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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AndroidDeviceController
 *
 * All endpoints consumed by the Android IPTV client.
 * No auth token needed — devices identify via device_id + registration_token.
 *
 * Endpoints:
 *   POST   /api/android/register          → first-time registration by entering server URL
 *   POST   /api/android/heartbeat         → periodic ping (every 30s), returns pending commands
 *   POST   /api/android/command-ack       → device acks a command as executed or failed
 *   GET    /api/android/settings          → pull latest pushed settings
 *   GET    /api/android/iptv-config       → get Xtream credentials
 *   GET    /api/android/hotel-info        → get hotel name, logo, etc.
 */
class AndroidDeviceController extends Controller
{
    // ── Registration ───────────────────────────────────────────────────────

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

    // ── Heartbeat ──────────────────────────────────────────────────────────

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

    // ── Command acknowledgement ────────────────────────────────────────────

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

    // ── Settings pull ──────────────────────────────────────────────────────

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

    // ── IPTV Config ───────────────────────────────────────────────────────

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

    // ── Hotel Info (public, no token) ─────────────────────────────────────

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

    // ── Private helpers ────────────────────────────────────────────────────

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
            'xtream_url', 'xtream_username', 'xtream_password',
            'hotel_name', 'hotel_logo', 'hotel_primary_color',
            'hotel_welcome_message', 'admin_pin',
            'iptv_ui_theme', 'iptv_show_epg', 'iptv_auto_launch_seconds',
        ]);

        // Per-device overrides take precedence over global settings
        $pushed = $device->pushed_settings ?? [];

        return array_merge([
            'xtream_url'               => $db['xtream_url'] ?? '',
            'xtream_username'          => $db['xtream_username'] ?? '',
            'xtream_password'          => $db['xtream_password'] ?? '',
            'hotel_name'               => $db['hotel_name'] ?? '',
            'hotel_logo_url'           => $db['hotel_logo'] ?? '',
            'hotel_primary_color'      => $db['hotel_primary_color'] ?? '#1C88FF',
            'hotel_welcome_message'    => $db['hotel_welcome_message'] ?? '',
            'admin_pin'                => $db['admin_pin'] ?? '1234',
            'ui_theme'                 => $db['iptv_ui_theme'] ?? 'dark',
            'show_epg'                 => (bool)($db['iptv_show_epg'] ?? true),
            'auto_launch_seconds'      => (int)($db['iptv_auto_launch_seconds'] ?? 15),
            'room_number'              => $device->room?->room_number ?? '',
            'package'                  => $device->package ?? 'basic',
        ], $pushed);
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
