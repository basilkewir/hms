<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Room;
use App\Models\IptvDevice;
use App\Models\IptvPackage;
use App\Models\VodContent;
use App\Models\Reservation;
use App\Models\IptvUsageLog;
use App\Models\VodViewingHistory;
use App\Models\Setting;
use Carbon\Carbon;

class IptvController extends Controller
{
    /**
     * Authenticate IPTV device and get room configuration
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'mac_address' => 'required|string',
            'room_number' => 'required|string',
        ]);

        try {
            // Find the room
            $room = Room::where('room_number', $request->room_number)->first();
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            // Update or create IPTV device
            $device = IptvDevice::updateOrCreate(
                ['device_id' => $request->device_id],
                [
                    'mac_address' => $request->mac_address,
                    'room_id' => $room->id,
                    'ip_address' => $request->ip(),
                    'status' => 'online',
                    'last_seen' => now(),
                    'last_heartbeat' => now(),
                    'device_info' => [
                        'android_version' => $request->android_version,
                        'app_version' => $request->app_version,
                        'device_name' => $request->device_name,
                    ],
                    'is_active' => true,
                ]
            );

            // Update room IPTV info
            $room->update([
                'iptv_device_id' => $device->device_id,
                'iptv_mac_address' => $request->mac_address,
                'iptv_ip_address' => $request->ip(),
                'iptv_last_seen' => now(),
            ]);

            // Get current reservation
            $currentReservation = $room->reservations()
                ->where('status', 'checked_in')
                ->where('check_in_date', '<=', now())
                ->where('check_out_date', '>=', now())
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'data' => [
                    'room' => [
                        'id' => $room->id,
                        'number' => $room->room_number,
                        'type' => $room->roomType->name,
                        'floor' => $room->floor,
                    ],
                    'device' => [
                        'id' => $device->id,
                        'device_id' => $device->device_id,
                        'status' => $device->status,
                    ],
                    'guest' => $currentReservation ? [
                        'name' => $currentReservation->guest->full_name,
                        'check_in' => $currentReservation->check_in_date,
                        'check_out' => $currentReservation->check_out_date,
                    ] : null,
                    'settings' => $this->getRoomIptvSettings($room),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available channels for the room
     */
    public function getChannels(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        try {
            $device = IptvDevice::where('device_id', $request->device_id)->first();
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            $room = $device->room;
            $roomSettings = $room->iptvSettings;
            
            // Get room's IPTV package
            $packageId = $roomSettings ? $roomSettings->iptv_package_id : null;
            if (!$packageId) {
                // Default to basic package
                $package = IptvPackage::where('code', 'basic')->first();
                $packageId = $package ? $package->id : null;
            }

            if (!$packageId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No IPTV package configured'
                ], 400);
            }

            // Get channels from Xtream Codes API
            $package = IptvPackage::find($packageId);
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'IPTV package not found'
                ], 404);
            }

            // Get channels from Xtream Codes API based on package configuration
            $channels = $this->getXtreamChannels($package, $roomSettings);

            // Update device heartbeat
            $device->update(['last_heartbeat' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'channels' => $channels,
                    'total_channels' => count($channels),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get channels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get channels from Xtream Codes API based on package configuration
     */
    private function getXtreamChannels($package, $roomSettings = null)
    {
        // Get Xtream Codes API credentials from settings
        $xtreamUrl = Setting::getValue('xtream_api_url');
        $xtreamUsername = Setting::getValue('xtream_username');
        $xtreamPassword = Setting::getValue('xtream_password');

        if (!$xtreamUrl || !$xtreamUsername || !$xtreamPassword) {
            return [];
        }

        try {
            // Get live streams from Xtream Codes API
            $apiUrl = rtrim($xtreamUrl, '/') . '/player_api.php';
            $params = [
                'username' => $xtreamUsername,
                'password' => $xtreamPassword,
                'action' => 'get_live_streams'
            ];

            $response = Http::timeout(30)->get($apiUrl, $params);

            if (!$response->successful()) {
                return [];
            }

            $streams = $response->json();
            if (!is_array($streams)) {
                return [];
            }

            // Filter channels based on package configuration
            $filteredChannels = [];
            foreach ($streams as $stream) {
                // Check if channel belongs to allowed categories
                if ($package->xtream_categories && !empty($package->xtream_categories)) {
                    if (!in_array($stream['category_id'] ?? null, $package->xtream_categories)) {
                        continue;
                    }
                }

                // Check if channel is in specific channel groups
                if ($package->xtream_channel_groups && !empty($package->xtream_channel_groups)) {
                    if (!in_array($stream['stream_id'] ?? null, $package->xtream_channel_groups)) {
                        continue;
                    }
                }

                // Filter adult content if not enabled
                if ($roomSettings && !$roomSettings->adult_content_enabled) {
                    $isAdult = stripos($stream['name'] ?? '', 'adult') !== false ||
                              stripos($stream['category_name'] ?? '', 'adult') !== false ||
                              stripos($stream['category_name'] ?? '', 'xxx') !== false;
                    if ($isAdult) {
                        continue;
                    }
                }

                // Check blocked channels
                if ($roomSettings && $roomSettings->xtream_blocked_channels) {
                    if (in_array($stream['stream_id'] ?? null, $roomSettings->xtream_blocked_channels)) {
                        continue;
                    }
                }

                // Check blocked categories
                if ($roomSettings && $roomSettings->xtream_blocked_categories) {
                    if (in_array($stream['category_id'] ?? null, $roomSettings->xtream_blocked_categories)) {
                        continue;
                    }
                }

                // Format channel data for client
                $filteredChannels[] = [
                    'id' => $stream['stream_id'] ?? null,
                    'number' => $stream['num'] ?? count($filteredChannels) + 1,
                    'name' => $stream['name'] ?? 'Unknown Channel',
                    'logo' => $stream['stream_icon'] ?? null,
                    'category' => $stream['category_name'] ?? 'General',
                    'category_id' => $stream['category_id'] ?? null,
                    'stream_url' => $this->buildXtreamStreamUrl($xtreamUrl, $xtreamUsername, $xtreamPassword, $stream['stream_id'] ?? null),
                    'is_hd' => stripos($stream['name'] ?? '', 'hd') !== false || stripos($stream['name'] ?? '', '1080') !== false,
                    'epg_channel_id' => $stream['epg_channel_id'] ?? null,
                ];
            }

            return $filteredChannels;

        } catch (\Exception $e) {
            \Log::error('Failed to get Xtream channels: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build Xtream Codes stream URL
     */
    private function buildXtreamStreamUrl($baseUrl, $username, $password, $streamId)
    {
        if (!$streamId) {
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . $username . '/' . $password . '/' . $streamId;
    }

    /**
     * Get VOD content
     */
    public function getVodContent(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'category' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $device = IptvDevice::where('device_id', $request->device_id)->first();
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            $room = $device->room;
            $roomSettings = $room->iptvSettings;

            $query = VodContent::where('is_active', true);

            // Filter by category if specified
            if ($request->category) {
                $query->where('genre', $request->category);
            }

            // Filter adult content if not enabled
            if (!$roomSettings || !$roomSettings->adult_content_enabled) {
                $query->where('is_adult_content', false);
            }

            $perPage = $request->per_page ?? 20;
            $vodContent = $query->orderBy('title')
                               ->paginate($perPage);

            // Update device heartbeat
            $device->update(['last_heartbeat' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'movies' => $vodContent->items(),
                    'pagination' => [
                        'current_page' => $vodContent->currentPage(),
                        'last_page' => $vodContent->lastPage(),
                        'per_page' => $vodContent->perPage(),
                        'total' => $vodContent->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get VOD content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log IPTV usage
     */
    public function logUsage(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'action' => 'required|string|in:channel_change,volume_change,power_on,power_off,vod_start,vod_stop',
            'xtream_channel_id' => 'nullable|string',
            'xtream_stream_id' => 'nullable|string',
            'channel_name' => 'nullable|string',
            'vod_content_id' => 'nullable|exists:vod_content,id',
            'action_data' => 'nullable|array',
            'duration_seconds' => 'nullable|integer|min:0',
        ]);

        try {
            $device = IptvDevice::where('device_id', $request->device_id)->first();
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            $room = $device->room;
            $currentReservation = $room->reservations()
                ->where('status', 'checked_in')
                ->where('check_in_date', '<=', now())
                ->where('check_out_date', '>=', now())
                ->first();

            // Log IPTV usage
            $usageLog = IptvUsageLog::create([
                'room_id' => $room->id,
                'reservation_id' => $currentReservation ? $currentReservation->id : null,
                'iptv_device_id' => $device->id,
                'xtream_channel_id' => $request->xtream_channel_id,
                'xtream_stream_id' => $request->xtream_stream_id,
                'channel_name' => $request->channel_name,
                'action' => $request->action,
                'action_data' => $request->action_data,
                'started_at' => now(),
                'duration_seconds' => $request->duration_seconds,
                'guest_ip' => $request->ip(),
            ]);

            // Log VOD viewing if applicable
            if ($request->vod_content_id && in_array($request->action, ['vod_start', 'vod_stop'])) {
                $vodContent = VodContent::find($request->vod_content_id);
                
                if ($request->action === 'vod_start') {
                    VodViewingHistory::create([
                        'room_id' => $room->id,
                        'reservation_id' => $currentReservation ? $currentReservation->id : null,
                        'vod_content_id' => $request->vod_content_id,
                        'started_at' => now(),
                        'total_duration_seconds' => $vodContent ? $vodContent->duration_minutes * 60 : 0,
                        'rental_charge' => $vodContent ? $vodContent->rental_price : 0,
                    ]);
                } elseif ($request->action === 'vod_stop') {
                    $viewing = VodViewingHistory::where('room_id', $room->id)
                        ->where('vod_content_id', $request->vod_content_id)
                        ->whereNull('ended_at')
                        ->latest()
                        ->first();
                    
                    if ($viewing) {
                        $watchDuration = $request->duration_seconds ?? 0;
                        $completionPercentage = $viewing->total_duration_seconds > 0 
                            ? ($watchDuration / $viewing->total_duration_seconds) * 100 
                            : 0;
                        
                        $viewing->update([
                            'ended_at' => now(),
                            'watch_duration_seconds' => $watchDuration,
                            'completion_percentage' => $completionPercentage,
                            'was_completed' => $completionPercentage >= 90,
                        ]);
                    }
                }
            }

            // Update device heartbeat
            $device->update(['last_heartbeat' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Usage logged successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Device heartbeat
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'status' => 'nullable|string|in:online,offline,error',
        ]);

        try {
            $device = IptvDevice::where('device_id', $request->device_id)->first();
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            $device->update([
                'status' => $request->status ?? 'online',
                'last_heartbeat' => now(),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Heartbeat received'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Heartbeat failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get room IPTV settings
     */
    private function getRoomIptvSettings($room)
    {
        $settings = $room->iptvSettings;
        
        if (!$settings) {
            // Return default settings
            return [
                'adult_content_enabled' => false,
                'volume_limit' => 100,
                'auto_power_off' => false,
                'language_preferences' => ['English'],
                'parental_control_pin' => null,
            ];
        }

        return [
            'adult_content_enabled' => $settings->adult_content_enabled,
            'volume_limit' => $settings->volume_limit,
            'auto_power_off' => $settings->auto_power_off,
            'auto_power_off_time' => $settings->auto_power_off_time,
            'quiet_hours_start' => $settings->quiet_hours_start,
            'quiet_hours_end' => $settings->quiet_hours_end,
            'language_preferences' => $settings->language_preferences ?? ['English'],
            'parental_control_pin' => $settings->parental_control_pin,
            'blocked_channels' => $settings->blocked_channels ?? [],
        ];
    }
}
