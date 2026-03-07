<?php

namespace App\Http\Controllers\Admin\IPTV;

use App\Http\Controllers\Controller;
use App\Models\IptvDevice;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = IptvDevice::with(['room.roomType'])
            ->orderBy('last_seen', 'desc')
            ->get()
            ->map(function($device) {
                return [
                    'id' => $device->id,
                    'device_id' => $device->device_id,
                    'mac_address' => $device->mac_address,
                    'ip_address' => $device->ip_address,
                    'room_number' => $device->room?->room_number ?? 'N/A',
                    'room_type' => $device->room?->roomType?->name ?? 'N/A',
                    'status' => $device->status,
                    'package' => $device->package ?? 'basic',
                    'last_heartbeat' => $device->last_seen,
                    'app_version' => $device->app_version ?? '1.0.0',
                    'android_version' => $device->android_version ?? '11',
                    'floor' => Schema::hasColumn('rooms', 'floor_id') 
                        ? ($device->room?->floor?->floor_number ?? null)
                        : ($device->room?->floor ?? null),
                ];
            });

        $stats = [
            'total' => IptvDevice::count(),
            'online' => IptvDevice::where('status', 'online')->count(),
            'offline' => IptvDevice::where('status', 'offline')->count(),
            'issues' => IptvDevice::where('status', 'error')->count(),
            'uptime' => $this->calculateUptime(),
        ];

        // Get available rooms for device registration
        $availableRooms = Room::orderBy('room_number')
            ->get(['id', 'room_number'])
            ->map(function($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                ];
            });

        return Inertia::render('Admin/IPTV/Devices/Index', [
            'user' => auth()->user()->load('roles'),
            'devices' => $devices,
            'deviceStats' => $stats,
            'availableRooms' => $availableRooms->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|unique:iptv_devices,device_id',
            'mac_address' => 'required|string|unique:iptv_devices,mac_address',
            'room_id' => 'required|exists:rooms,id',
            'package' => 'required|in:basic,premium,vip',
        ]);

        $validated['status'] = 'offline';
        $validated['registered_at'] = now();

        IptvDevice::create($validated);

        return redirect()->back()->with('success', 'Device registered successfully');
    }

    public function destroy(IptvDevice $device)
    {
        $device->delete();
        return redirect()->back()->with('success', 'Device removed successfully');
    }

    private function calculateUptime()
    {
        $total = IptvDevice::count();
        if ($total === 0) return 0;
        
        $online = IptvDevice::where('status', 'online')->count();
        return round(($online / $total) * 100, 1);
    }
}
