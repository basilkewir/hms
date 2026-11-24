<?php

namespace App\Services;

use App\Models\Room;

class RoomLimitService
{
    public function __construct(private LicenseValidationService $licenses) {}

    public function getMaxRooms(): int
    {
        return $this->licenses->getLicenseLimits()['max_rooms'] ?? 0;
    }

    public function canCreateRoom(): bool
    {
        $maxRooms = $this->getMaxRooms();

        if ($maxRooms <= 0) {
            return false;
        }

        return Room::count() < $maxRooms;
    }

    public function getRoomUsage(): array
    {
        $maxRooms = $this->getMaxRooms();
        $currentRooms = Room::count();

        return [
            'current' => $currentRooms,
            'maximum' => $maxRooms,
            'available' => max(0, $maxRooms - $currentRooms),
            'percentage' => $maxRooms > 0 ? round(($currentRooms / $maxRooms) * 100, 1) : 0,
        ];
    }
}
