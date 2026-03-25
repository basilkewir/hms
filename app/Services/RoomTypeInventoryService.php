<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationHold;
use App\Models\Room;
use App\Models\RoomTypeDailyInventory;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoomTypeInventoryService
{
    public function availabilitySummary(int $roomTypeId, $checkInDate, $checkOutDate): array
    {
        $checkIn = Carbon::parse($checkInDate)->startOfDay();
        $checkOut = Carbon::parse($checkOutDate)->startOfDay();

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return ['available' => 0, 'by_date' => []];
        }

        $this->cleanupExpiredHolds();
        $this->refreshRange($roomTypeId, $checkIn, $checkOut);

        $rows = RoomTypeDailyInventory::where('room_type_id', $roomTypeId)
            ->whereBetween('inventory_date', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()])
            ->orderBy('inventory_date')
            ->get();

        return [
            'available' => (int) ($rows->min('available_count') ?? 0),
            'by_date' => $rows->map(fn ($row) => [
                'date' => $row->inventory_date->toDateString(),
                'total_inventory' => (int) $row->total_inventory,
                'reserved_count' => (int) $row->reserved_count,
                'hold_count' => (int) $row->hold_count,
                'overbooking_allowance' => (int) $row->overbooking_allowance,
                'available_count' => (int) $row->available_count,
            ])->values()->all(),
        ];
    }

    public function createHold(
        int $roomTypeId,
        $checkInDate,
        $checkOutDate,
        int $quantity = 1,
        int $minutesToExpire = 15,
        ?string $ipAddress = null,
        array $metadata = []
    ): ReservationHold {
        $checkIn = Carbon::parse($checkInDate)->startOfDay();
        $checkOut = Carbon::parse($checkOutDate)->startOfDay();

        if ($quantity < 1) {
            throw new \RuntimeException('Hold quantity must be at least 1.');
        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            throw new \RuntimeException('Invalid date range for hold.');
        }

        return DB::transaction(function () use ($roomTypeId, $checkIn, $checkOut, $quantity, $minutesToExpire, $ipAddress, $metadata) {
            $this->cleanupExpiredHolds();
            $this->lockDateRange($roomTypeId, $checkIn, $checkOut);
            $this->assertAvailability($roomTypeId, $checkIn, $checkOut, $quantity);

            $hold = ReservationHold::create([
                'hold_token' => (string) Str::uuid(),
                'room_type_id' => $roomTypeId,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'quantity' => $quantity,
                'status' => 'active',
                'expires_at' => now()->addMinutes($minutesToExpire),
                'created_from_ip' => $ipAddress,
                'metadata' => $metadata ?: null,
            ]);

            $this->refreshRange($roomTypeId, $checkIn, $checkOut);

            return $hold;
        });
    }

    public function consumeHold(string $holdToken, int $roomTypeId, $checkInDate, $checkOutDate, int $quantity = 1): ReservationHold
    {
        $checkIn = Carbon::parse($checkInDate)->startOfDay();
        $checkOut = Carbon::parse($checkOutDate)->startOfDay();

        $hold = ReservationHold::where('hold_token', $holdToken)->lockForUpdate()->first();

        if (!$hold || $hold->status !== 'active') {
            throw new \RuntimeException('Hold is invalid or no longer active.');
        }

        if ($hold->expires_at->isPast()) {
            $hold->update(['status' => 'expired']);
            throw new \RuntimeException('Hold has expired.');
        }

        if (
            (int) $hold->room_type_id !== $roomTypeId ||
            $hold->check_in_date->toDateString() !== $checkIn->toDateString() ||
            $hold->check_out_date->toDateString() !== $checkOut->toDateString() ||
            (int) $hold->quantity !== $quantity
        ) {
            throw new \RuntimeException('Hold details do not match booking details.');
        }

        $hold->update([
            'status' => 'consumed',
            'consumed_at' => now(),
        ]);

        return $hold->fresh();
    }

    public function reserveNow(int $roomTypeId, $checkInDate, $checkOutDate, int $quantity = 1): void
    {
        $checkIn = Carbon::parse($checkInDate)->startOfDay();
        $checkOut = Carbon::parse($checkOutDate)->startOfDay();

        $this->cleanupExpiredHolds();
        $this->lockDateRange($roomTypeId, $checkIn, $checkOut);
        $this->assertAvailability($roomTypeId, $checkIn, $checkOut, $quantity);
    }

    public function lockDateRange(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): void
    {
        $this->ensureDailyRows($roomTypeId, $checkIn, $checkOut);

        RoomTypeDailyInventory::where('room_type_id', $roomTypeId)
            ->whereBetween('inventory_date', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()])
            ->lockForUpdate()
            ->get();

        $this->refreshRange($roomTypeId, $checkIn, $checkOut);
    }

    public function refreshRange(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): void
    {
        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return;
        }

        $this->ensureDailyRows($roomTypeId, $checkIn, $checkOut);

        $overbookingPercent = (float) Setting::get('overbooking_limit', 0);
        $totalInventory = Room::where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean')
            ->count();
        $overbookingAllowance = (int) floor($totalInventory * ($overbookingPercent / 100));

        $rowsToUpsert = [];
        $cursor = $checkIn->copy();

        while ($cursor->lt($checkOut)) {
            $date = $cursor->toDateString();

            $reservationBaseQuery = Reservation::where('room_type_id', $roomTypeId)
                ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'modified'])
                ->whereDate('check_in_date', '<=', $date)
                ->whereDate('check_out_date', '>', $date);

            $reservedCount = (int) (Schema::hasColumn('reservations', 'number_of_rooms')
                ? (clone $reservationBaseQuery)->sum(DB::raw('COALESCE(number_of_rooms, 1)'))
                : (clone $reservationBaseQuery)->count());

            $holdCount = (int) ReservationHold::where('room_type_id', $roomTypeId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->whereDate('check_in_date', '<=', $date)
                ->whereDate('check_out_date', '>', $date)
                ->sum('quantity');

            $availableCount = max(0, ($totalInventory + $overbookingAllowance) - $reservedCount - $holdCount);

            $rowsToUpsert[] = [
                'room_type_id' => $roomTypeId,
                'inventory_date' => $date,
                'total_inventory' => $totalInventory,
                'reserved_count' => $reservedCount,
                'hold_count' => $holdCount,
                'overbooking_allowance' => $overbookingAllowance,
                'available_count' => $availableCount,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            $cursor->addDay();
        }

        if (!empty($rowsToUpsert)) {
            DB::table('room_type_daily_inventories')->upsert(
                $rowsToUpsert,
                ['room_type_id', 'inventory_date'],
                ['total_inventory', 'reserved_count', 'hold_count', 'overbooking_allowance', 'available_count', 'updated_at']
            );
        }
    }

    public function cleanupExpiredHolds(): int
    {
        return ReservationHold::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired', 'updated_at' => now()]);
    }

    private function assertAvailability(int $roomTypeId, Carbon $checkIn, Carbon $checkOut, int $quantity): void
    {
        if ($quantity < 1) {
            throw new \RuntimeException('Quantity must be at least 1.');
        }

        $rows = RoomTypeDailyInventory::where('room_type_id', $roomTypeId)
            ->whereBetween('inventory_date', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()])
            ->orderBy('inventory_date')
            ->get(['available_count']);

        if ($rows->isEmpty() || (int) $rows->min('available_count') < $quantity) {
            throw new \RuntimeException('No inventory available for one or more selected dates.');
        }
    }

    private function ensureDailyRows(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): void
    {
        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return;
        }

        $rows = [];
        $cursor = $checkIn->copy();
        while ($cursor->lt($checkOut)) {
            $rows[] = [
                'room_type_id' => $roomTypeId,
                'inventory_date' => $cursor->toDateString(),
                'total_inventory' => 0,
                'reserved_count' => 0,
                'hold_count' => 0,
                'overbooking_allowance' => 0,
                'available_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $cursor->addDay();
        }

        DB::table('room_type_daily_inventories')->upsert(
            $rows,
            ['room_type_id', 'inventory_date'],
            ['updated_at']
        );
    }
}
