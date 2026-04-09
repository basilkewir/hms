<?php

namespace App\Http\Controllers\Admin\IPTV;

use App\Http\Controllers\Controller;
use App\Models\DeviceCommand;
use App\Models\DeviceHeartbeat;
use App\Models\IptvDevice;
use App\Models\Room;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DeviceController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────

    public function index()
    {
        // Guard: if migration hasn't run yet, show empty state gracefully
        if (!Schema::hasTable('iptv_devices')) {
            return Inertia::render('Admin/IPTV/Devices/Index', [
                'user'           => auth()->user()->load('roles'),
                'navigation'     => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole('admin'),
                'devices'        => [],
                'stats'          => ['total' => 0, 'online' => 0, 'idle' => 0, 'offline' => 0, 'issues' => 0, 'uptime' => 0],
                'availableRooms' => [],
                'globalSettings' => $this->getGlobalSettings(),
                'serverUrl'      => url('/'),
                'migrationNeeded' => true,
            ]);
        }

        $all = IptvDevice::with(['room.roomType'])->orderBy('last_heartbeat', 'desc')->get();

        $devices = $all->map(fn($d) => $this->deviceResource($d));

        $stats = [
            'total'   => $all->count(),
            'online'  => $all->filter(fn($d) => $d->computedStatus() === 'online')->count(),
            'idle'    => $all->filter(fn($d) => $d->computedStatus() === 'idle')->count(),
            'offline' => $all->filter(fn($d) => $d->computedStatus() === 'offline')->count(),
            'issues'  => $all->filter(fn($d) => $d->commands()->where('status', 'failed')->exists())->count(),
            'uptime'  => $this->calculateUptime(),
        ];

        $availableRooms = Room::orderBy('room_number')->get(['id', 'room_number']);

        return Inertia::render('Admin/IPTV/Devices/Index', [
            'user'           => auth()->user()->load('roles'),
            'navigation'     => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole('admin'),
            'devices'        => $devices,
            'stats'          => $stats,
            'availableRooms' => $availableRooms->values(),
            'globalSettings' => $this->getGlobalSettings(),
            'serverUrl'      => url('/'),
        ]);
    }

    // ── Show ───────────────────────────────────────────────────────────────

    public function show(IptvDevice $device)
    {
        $device->load('room.roomType');

        $heartbeats = DeviceHeartbeat::where('iptv_device_id', $device->id)
            ->orderBy('recorded_at', 'desc')
            ->limit(48)
            ->get()
            ->map(fn($h) => [
                'id'              => $h->id,
                'recorded_at'     => $h->recorded_at?->toIso8601String(),
                'status'          => $h->status,
                'current_channel' => $h->current_channel,
                'ip_address'      => $h->ip_address,
                'app_version'     => $h->app_version,
            ]);

        $commandHistory = DeviceCommand::where('iptv_device_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'type'          => $c->type,
                'status'        => $c->status,
                'dispatched_by' => $c->dispatched_by,
                'dispatched_at' => $c->dispatched_at?->toIso8601String(),
                'executed_at'   => $c->executed_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/IPTV/Devices/Show', [
            'user'       => auth()->user()->load('roles'),
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole('admin'),
            'device'     => $this->deviceResource($device),
            'heartbeats' => $heartbeats,
            'commands'   => $commandHistory,
            'serverUrl'  => url('/'),
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'   => 'required|string|unique:iptv_devices,device_id|max:64',
            'mac_address' => 'nullable|string|max:64',
            'device_name' => 'nullable|string|max:128',
            'room_id'     => 'nullable|exists:rooms,id',
            'device_type' => 'nullable|in:android_tv,android_box,firestick,other',
            'notes'       => 'nullable|string|max:500',
        ]);

        $device = IptvDevice::create(array_merge($validated, [
            'status'           => 'offline',
            'is_active'        => true,
            'registered_at'    => now(),
            'settings_version' => 0,
        ]));

        $token = $device->generateRegistrationToken();

        return redirect()->back()->with([
            'success'  => 'Device registered. Enter the token in the TV app.',
            'newToken' => $token,
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, IptvDevice $device)
    {
        $validated = $request->validate([
            'device_name' => 'nullable|string|max:128',
            'room_id'     => 'nullable|exists:rooms,id',
            'device_type' => 'nullable|in:android_tv,android_box,firestick,other',
            'notes'       => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $device->update($validated);
        return redirect()->back()->with('success', 'Device updated');
    }

    // ── Destroy ────────────────────────────────────────────────────────────

    public function destroy(IptvDevice $device)
    {
        $device->delete();
        return redirect()->back()->with('success', 'Device removed');
    }

    // ── Remote command dispatch ────────────────────────────────────────────

    public function sendCommand(Request $request, IptvDevice $device)
    {
        $request->validate([
            'type'    => 'required|in:reboot,refresh_channels,push_settings,set_channel,message,lock,unlock,reload_app',
            'payload' => 'nullable|array',
        ]);
        $device->dispatchCommand($request->type, $request->payload ?? [], auth()->user()?->name);

        $label = str_replace('_', ' ', $request->type);
        return redirect()->back()->with('success', ucfirst($label) . ' command queued for delivery.');
    }

    // ── Push settings to one device ───────────────────────────────────────

    public function pushSettings(Request $request, IptvDevice $device)
    {
        $request->validate([
            // Per-device Xtream credentials (override global)
            'xtream_username' => 'nullable|string|max:128',
            'xtream_password' => 'nullable|string|max:128',
            // Optional override for Xtream URL on this device
            'xtream_url'      => 'nullable|url|max:500',
            // Security
            'admin_pin'       => 'nullable|string|max:16',
            // TV UI overrides
            'ui_theme'               => 'nullable|in:dark,light',
            'auto_launch_seconds'    => 'nullable|integer|min:0|max:300',
            'show_epg'               => 'nullable|boolean',
            'show_clock'             => 'nullable|boolean',
            'show_room_number'       => 'nullable|boolean',
            'enable_vod'             => 'nullable|boolean',
            'enable_series'          => 'nullable|boolean',
            'enable_radio'           => 'nullable|boolean',
            'parental_pin'           => 'nullable|string|max:8',
        ]);

        $overrides = array_filter($request->only([
            'xtream_username', 'xtream_password', 'xtream_url',
            'admin_pin', 'ui_theme', 'auto_launch_seconds',
            'show_epg', 'show_clock', 'show_room_number',
            'enable_vod', 'enable_series', 'enable_radio', 'parental_pin',
        ]), fn($v) => $v !== null && $v !== '');

        $device->update([
            'pushed_settings'  => array_merge($device->pushed_settings ?? [], $overrides),
            'settings_version' => ($device->settings_version ?? 0) + 1,
        ]);
        $device->dispatchCommand('push_settings', ['settings_version' => $device->settings_version], auth()->user()?->name);
        return redirect()->back()->with('success', 'Settings pushed — device will apply on next heartbeat.');
    }

    // ── Push global settings to ALL devices ──────────────────────────────

    public function pushSettingsAll(Request $request)
    {
        $request->validate([
            'xtream_url'             => 'nullable|url|max:500',
            'xtream_username'        => 'nullable|string|max:128',
            'xtream_password'        => 'nullable|string|max:128',
            'admin_pin'              => 'nullable|string|max:16',
            'ui_theme'               => 'nullable|in:dark,light',
            'auto_launch_seconds'    => 'nullable|integer|min:0|max:300',
            'show_epg'               => 'nullable|boolean',
            'show_clock'             => 'nullable|boolean',
            'show_room_number'       => 'nullable|boolean',
            'enable_vod'             => 'nullable|boolean',
            'enable_series'          => 'nullable|boolean',
            'enable_radio'           => 'nullable|boolean',
            'parental_pin'           => 'nullable|string|max:8',
        ]);

        $overrides = array_filter($request->only([
            'xtream_url', 'xtream_username', 'xtream_password',
            'admin_pin', 'ui_theme', 'auto_launch_seconds',
            'show_epg', 'show_clock', 'show_room_number',
            'enable_vod', 'enable_series', 'enable_radio', 'parental_pin',
        ]), fn($v) => $v !== null && $v !== '');

        $count = 0;
        IptvDevice::where('is_active', true)->each(function (IptvDevice $device) use ($overrides, &$count) {
            $device->update([
                'pushed_settings'  => array_merge($device->pushed_settings ?? [], $overrides),
                'settings_version' => ($device->settings_version ?? 0) + 1,
            ]);
            $device->dispatchCommand('push_settings', ['settings_version' => $device->settings_version], auth()->user()?->name);
            $count++;
        });
        return response()->json(['success' => true, 'message' => "Settings pushed to {$count} devices"]);
    }

    // ── Regenerate registration token ──────────────────────────────────────

    public function regenerateToken(IptvDevice $device)
    {
        $token = $device->generateRegistrationToken();
        return redirect()->back()->with([
            'success'  => 'New registration token generated.',
            'newToken' => $token,
        ]);
    }

    // ── Live status (AJAX polling every 10s) ──────────────────────────────

    public function statusRefresh()
    {
        $devices = IptvDevice::with('room')->orderBy('last_heartbeat', 'desc')->get()->map(fn($d) => [
            'id'              => $d->id,
            'device_id'       => $d->device_id,
            'computed_status' => $d->computedStatus(),
            'last_heartbeat'  => $d->last_heartbeat?->toIso8601String(),
            'ip_address'      => $d->ip_address ?? '—',
            'pending_commands' => $d->commands()->where('status', 'pending')->count(),
        ]);
        $stats = [
            'total'   => $devices->count(),
            'online'  => $devices->where('computed_status', 'online')->count(),
            'idle'    => $devices->where('computed_status', 'idle')->count(),
            'offline' => $devices->where('computed_status', 'offline')->count(),
        ];
        return response()->json(['devices' => $devices, 'stats' => $stats]);
    }

    // ── Per-device live poll (used by Show.vue every 8s) ──────────────────

    public function devicePoll(IptvDevice $device)
    {
        $commands = DeviceCommand::where('iptv_device_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'type'          => $c->type,
                'status'        => $c->status,
                'dispatched_by' => $c->dispatched_by,
                'dispatched_at' => $c->dispatched_at?->toIso8601String(),
                'executed_at'   => $c->executed_at?->toIso8601String(),
            ]);

        $heartbeats = DeviceHeartbeat::where('iptv_device_id', $device->id)
            ->orderBy('recorded_at', 'desc')
            ->limit(48)
            ->get()
            ->map(fn($h) => [
                'id'              => $h->id,
                'recorded_at'     => $h->recorded_at?->toIso8601String(),
                'status'          => $h->status,
                'current_channel' => $h->current_channel,
                'ip_address'      => $h->ip_address,
                'app_version'     => $h->app_version,
            ]);

        return response()->json([
            'device'     => $this->deviceResource($device->fresh()),
            'commands'   => $commands,
            'heartbeats' => $heartbeats,
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function deviceResource(IptvDevice $d): array
    {
        return [
            'id'                 => $d->id,
            'device_id'          => $d->device_id,
            'device_name'        => $d->device_name ?? $d->device_id,
            'device_type'        => $d->device_type ?? 'android_tv',
            'mac_address'        => $d->mac_address ?? '—',
            'ip_address'         => $d->ip_address ?? '—',
            'room_number'        => $d->room?->room_number ?? 'Unassigned',
            'room_type'          => $d->room?->roomType?->name ?? '—',
            'computed_status'    => $d->computedStatus(),
            'package'            => $d->package ?? 'basic',
            // ISO strings — the Vue ago() helper parses these with new Date()
            'last_heartbeat'     => $d->last_heartbeat?->toIso8601String(),
            'registered_at'      => $d->registered_at?->toIso8601String(),
            'app_version'        => $d->app_version ?? '—',
            'android_version'    => $d->android_version ?? '—',
            'settings_version'   => $d->settings_version ?? 0,
            'notes'              => $d->notes ?? '',
            'is_active'          => (bool)($d->is_active ?? true),
            'registration_token' => $d->registration_token,
            'pending_commands'   => $d->commands()->where('status', 'pending')->count(),
        ];
    }

    private function calculateUptime(): float
    {
        $total = IptvDevice::count();
        if ($total === 0) return 0.0;
        $online = IptvDevice::get()->filter(fn($d) => $d->computedStatus() === 'online')->count();
        return round(($online / $total) * 100, 1);
    }

    private function getGlobalSettings(): array
    {
        try {
            $keys = [
                // Xtream Codes
                'xtream_url', 'xtream_username', 'xtream_password', 'xtream_use_https',
                // Hotel branding for TV
                'hotel_name', 'hotel_logo', 'hotel_address', 'hotel_phone',
                'hotel_primary_color', 'hotel_welcome_message',
                // Weather widget
                'weather_api_key', 'weather_city', 'weather_units', 'weather_enabled',
                // TV UI & behaviour
                'iptv_ui_theme', 'iptv_show_epg', 'iptv_auto_launch_seconds',
                'iptv_show_clock', 'iptv_show_room_number',
                'iptv_enable_vod', 'iptv_enable_series', 'iptv_enable_radio',
                'iptv_parental_pin', 'admin_pin',
            ];
            return Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
