<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class RoomLimitService
{
    public function getMaxRooms(): int
    {
        if (!Storage::exists('license/status.json')) {
            return 0; // No license = no rooms allowed
        }

        $licenseStatus = json_decode(Storage::get('license/status.json'), true);
        return $licenseStatus['max_rooms'] ?? 0;
    }

    public function canCreateRoom(): bool
    {
        $maxRooms = $this->getMaxRooms();
        if ($maxRooms === 0) {
            return false;
        }

        // Count existing rooms (you'll need to implement this based on your Room model)
        // For now, returning true if max_rooms > 0
        return $maxRooms > 0;
    }

    public function getRoomUsage(): array
    {
        $maxRooms = $this->getMaxRooms();
        // You'll need to implement actual room counting
        $currentRooms = 0; // Replace with actual count from Room model
        
        return [
            'current' => $currentRooms,
            'maximum' => $maxRooms,
            'available' => max(0, $maxRooms - $currentRooms),
            'percentage' => $maxRooms > 0 ? round(($currentRooms / $maxRooms) * 100, 1) : 0
        ];
    }
}