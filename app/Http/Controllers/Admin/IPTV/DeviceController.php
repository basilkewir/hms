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
                'time'            => $h->recorded_at?->format('H:i'),
                'status'          => $h->status,
                'current_channel' => $h->current_channel,
                'ip_address'      => $h->ip_address,
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
                'dispatched_at' => $c->dispatched_at?->diffForHumans(),
                'executed_at'   => $c->executed_at?->diffForHumans(),
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
        $cmd = $device->dispatchCommand($request->type, $request->payload ?? [], auth()->user()?->name);
        return response()->json(['success' => true, 'message' => 'Command queued', 'command_id' => $cmd->id]);
    }

    // ── Push settings to one device ───────────────────────────────────────

    public function pushSettings(Request $request, IptvDevice $device)
    {
        $request->validate([
            'xtream_url'      => 'nullable|url|max:500',
            'xtream_username' => 'nullable|string|max:128',
            'xtream_password' => 'nullable|string|max:128',
            'admin_pin'       => 'nullable|string|max:16',
        ]);
        $settings = array_filter($request->only(['xtream_url', 'xtream_username', 'xtream_password', 'admin_pin']));
        $device->update([
            'pushed_settings'  => array_merge($device->pushed_settings ?? [], $settings),
            'settings_version' => ($device->settings_version ?? 0) + 1,
        ]);
        $device->dispatchCommand('push_settings', ['settings_version' => $device->settings_version], auth()->user()?->name);
        return response()->json(['success' => true, 'message' => 'Settings queued for delivery']);
    }

    // ── Push global settings to ALL devices ──────────────────────────────

    public function pushSettingsAll(Request $request)
    {
        $request->validate([
            'xtream_url'      => 'nullable|url|max:500',
            'xtream_username' => 'nullable|string|max:128',
            'xtream_password' => 'nullable|string|max:128',
            'admin_pin'       => 'nullable|string|max:16',
        ]);
        $settings = array_filter($request->only(['xtream_url', 'xtream_username', 'xtream_password', 'admin_pin']));
        $count = 0;
        IptvDevice::where('is_active', true)->each(function (IptvDevice $device) use ($settings, &$count) {
            $device->update([
                'pushed_settings'  => array_merge($device->pushed_settings ?? [], $settings),
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
        return response()->json([
            'success'            => true,
            'registration_token' => $token,
            'server_url'         => url('/'),
        ]);
    }

    // ── Live status (AJAX polling every 10s) ──────────────────────────────

    public function statusRefresh()
    {
        $devices = IptvDevice::with('room')->orderBy('last_heartbeat', 'desc')->get()->map(fn($d) => [
            'id'              => $d->id,
            'device_id'       => $d->device_id,
            'computed_status' => $d->computedStatus(),
            'last_heartbeat'  => $d->last_heartbeat?->diffForHumans() ?? 'Never',
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

    // ── Helpers ────────────────────────────────────────────────────────────

    private function deviceResource(IptvDevice $d): array
    {
        return [
            'id'               => $d->id,
            'device_id'        => $d->device_id,
            'device_name'      => $d->device_name ?? $d->device_id,
            'device_type'      => $d->device_type ?? 'android_tv',
            'mac_address'      => $d->mac_address ?? '—',
            'ip_address'       => $d->ip_address ?? '—',
            'room_number'      => $d->room?->room_number ?? 'Unassigned',
            'room_type'        => $d->room?->roomType?->name ?? '—',
            'computed_status'  => $d->computedStatus(),
            'package'          => $d->package ?? 'basic',
            'last_heartbeat'   => $d->last_heartbeat?->diffForHumans() ?? 'Never',
            'last_heartbeat_raw' => $d->last_heartbeat?->toIso8601String(),
            'app_version'      => $d->app_version ?? '—',
            'android_version'  => $d->android_version ?? '—',
            'settings_version' => $d->settings_version ?? 0,
            'registered_at'    => $d->registered_at?->format('Y-m-d') ?? '—',
            'notes'            => $d->notes ?? '',
            'is_active'        => (bool)($d->is_active ?? true),
            'pending_commands' => $d->commands()->where('status', 'pending')->count(),
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
            $keys = ['xtream_url', 'xtream_username', 'xtream_password', 'hotel_name',
                     'hotel_logo', 'hotel_primary_color', 'hotel_welcome_message',
                     'admin_pin', 'iptv_ui_theme', 'iptv_show_epg', 'iptv_auto_launch_seconds'];
            return Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
