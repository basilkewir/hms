<?php

namespace App\Support;

use App\Models\IptvDevice;
use App\Models\Setting;

/**
 * Single source of truth for global IPTV settings keys and force-push
 * to Android TV devices after any global IPTV change.
 */
class IptvSettingsSync
{
    public const STRING_KEYS = [
        'xtream_url', 'xtream_username', 'xtream_password',
        'iptv_network_interface', 'iptv_stream_port',
        'hotel_welcome_message', 'hotel_primary_color', 'welcome_background_url',
        'weather_api_key', 'weather_city', 'weather_units',
        'iptv_ui_theme', 'iptv_parental_pin', 'admin_pin',
        'iptv_default_channel', 'iptv_server_url', 'default_channel_package',
        'iptv_auto_launch_seconds',
    ];

    public const BOOL_KEYS = [
        'xtream_use_https', 'weather_enabled', 'iptv_force_interface',
        'iptv_show_epg', 'iptv_show_clock', 'iptv_show_room_number',
        'iptv_enable_vod', 'iptv_enable_series', 'iptv_enable_radio',
        'enable_vod', 'enable_parental_controls', 'auto_provision_rooms',
    ];

    public const INT_KEYS = [
        // iptv_auto_launch_seconds handled as string in STRING_KEYS for UI round-trip
    ];

    public const ALL_KEYS = [];

    public static function allKeys(): array
    {
        return array_values(array_unique(array_merge(self::STRING_KEYS, self::BOOL_KEYS, self::INT_KEYS)));
    }

    public static function isIptvKey(string $key): bool
    {
        return in_array($key, self::allKeys(), true)
            || strpos($key, 'iptv_') === 0
            || strpos($key, 'xtream_') === 0;
    }

    /**
     * Persist only IPTV-related keys from a settings payload (group=iptv).
     * Returns true when at least one IPTV key was saved.
     */
    public static function saveIptvKeys(array $settings): bool
    {
        $saved = false;
        foreach ($settings as $key => $value) {
            if (!self::isIptvKey($key)) {
                continue;
            }
            if (in_array($key, self::BOOL_KEYS, true)) {
                Setting::set($key, $value ? '1' : '0', 'boolean', 'iptv');
            } elseif (in_array($key, self::INT_KEYS, true)) {
                Setting::set($key, (int) $value, 'integer', 'iptv');
            } else {
                Setting::set($key, $value, 'string', 'iptv');
            }
            $saved = true;
        }
        return $saved;
    }

    /**
     * Bump settings_version + queue push_settings on every active device
     * so clients re-pull the full DB-backed payload (GET /api/android/settings).
     *
     * Optionally merge known Xtream overrides into pushed_settings so devices
     * that already store per-device credentials pick up new global values.
     */
    public static function forcePushAll(array $xtreamOverrides = []): int
    {
        $overrides = array_filter($xtreamOverrides, fn($v) => $v !== null && $v !== '');
        $count = 0;

        IptvDevice::where('is_active', true)->each(function (IptvDevice $device) use ($overrides, &$count) {
            $data = [
                'settings_version' => ($device->settings_version ?? 0) + 1,
            ];
            if ($overrides) {
                $data['pushed_settings'] = array_merge($device->pushed_settings ?? [], $overrides);
            }
            $device->update($data);
            $device->dispatchCommand('push_settings', ['settings_version' => $device->settings_version]);
            $count++;
        });

        return $count;
    }

    /**
     * Persist IPTV keys from payload and force-push to all active devices.
     * Returns number of devices pushed (0 if no IPTV keys changed).
     */
    public static function saveAndForcePush(array $settings): int
    {
        if (!self::saveIptvKeys($settings)) {
            return 0;
        }

        $xtreamOverrides = array_intersect_key($settings, array_flip([
            'xtream_url', 'xtream_username', 'xtream_password',
        ]));

        return self::forcePushAll($xtreamOverrides);
    }
}
