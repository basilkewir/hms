<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\IptvDevice;
use App\Models\IptvPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IptvController extends Controller
{
    public function devices(Request $request)
    {
        $devices = $this->deviceQuery()
            ->orderByDesc('last_activity')
            ->paginate(25)
            ->through(fn($device) => $this->mapDevice($device));

        return Inertia::render('Maintenance/IPTV/Devices', [
            'user' => $request->user()->load('roles'),
            'devices' => $devices,
        ]);
    }

    public function channels(Request $request)
    {
        $packages = IptvPackage::orderBy('name')->get()->map(fn($package) => [
            'id' => $package->id,
            'name' => $package->name,
            'code' => $package->code,
            'price' => $package->price ?? 0,
            'is_active' => (bool) $package->is_active,
        ]);

        return Inertia::render('Maintenance/IPTV/Channels', [
            'user' => $request->user()->load('roles'),
            'packages' => $packages,
        ]);
    }

    public function troubleshoot(Request $request)
    {
        $devices = $this->deviceQuery()
            ->get()
            ->map(fn($device) => $this->mapDevice($device))
            ->filter(fn($device) => !$device['online'])
            ->values();

        return Inertia::render('Maintenance/IPTV/Troubleshoot', [
            'user' => $request->user()->load('roles'),
            'devices' => $devices,
        ]);
    }

    public function installation(Request $request)
    {
        $devices = $this->deviceQuery()
            ->whereNull('room_id')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($device) => $this->mapDevice($device));

        return Inertia::render('Maintenance/IPTV/Installation', [
            'user' => $request->user()->load('roles'),
            'devices' => $devices,
        ]);
    }

    private function deviceQuery()
    {
        return IptvDevice::with('room');
    }

    private function mapDevice(IptvDevice $device): array
    {
        $lastActivity = $device->last_activity;
        $online = $device->is_active && (
            $device->status === 'online'
            || ($lastActivity && $lastActivity->gte(Carbon::now()->subMinutes(15)))
        );

        return [
            'id' => $device->id,
            'room_number' => $device->room?->room_number,
            'device_name' => $device->device_name,
            'device_type' => $device->device_type,
            'ip_address' => $device->ip_address,
            'status' => $device->status,
            'online' => $online,
            'last_seen' => $lastActivity?->diffForHumans() ?? 'N/A',
        ];
    }
}
