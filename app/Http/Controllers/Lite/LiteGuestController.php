<?php

namespace App\Http\Controllers\Lite;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\IptvDevice;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Lite Guest Controller
 *
 * A minimal, front-desk-only guest name input used to show a guest's name on
 * the in-room TV (IPTV) app. It deliberately does NOT run the full hotel
 * reservation/billing flow. To keep every existing IPTV display reader
 * working unchanged, a guest name is stored by creating a minimal Guest row
 * plus a minimal Reservation with status = 'checked_in' spanning today.
 */
class LiteGuestController extends Controller
{
    /**
     * Lite dashboard: rooms + their current guest + their IPTV/Android device.
     */
    public function dashboard()
    {
        $role = auth()->user()->roles->first()?->name ?? 'admin';

        $rooms = Room::with(['roomType', 'currentReservation.guest', 'iptvDevices'])
            ->orderBy('room_number')
            ->get()
            ->map(function (Room $room) {
                $res = $room->currentReservation;
                return [
                    'id'                 => $room->id,
                    'room_number'        => $room->room_number,
                    'room_type'          => $room->roomType?->name ?? 'Standard',
                    'guest_name'         => $this->guestDisplayName($res),
                    'guest_id'           => $res?->guest_id,
                    'reservation_id'     => $res?->id,
                    'device_id'          => $room->iptvDevices->first()?->device_id,
                    'device_name'        => $room->iptvDevices->first()?->device_name,
                    'device_id_field'    => $room->iptvDevices->first()?->id,
                    'iptv_device_count'  => $room->iptvDevices->count(),
                ];
            });

        $devices = IptvDevice::with('room')
            ->orderBy('last_heartbeat', 'desc')
            ->get()
            ->map(function (IptvDevice $d) {
                return [
                    'id'               => $d->id,
                    'device_id'        => $d->device_id,
                    'device_name'      => $d->device_name ?? $d->device_id,
                    'status'           => $d->computedStatus(),
                    'room_id'          => $d->room_id,
                    'room_number'      => $d->room?->room_number ?? 'Unassigned',
                ];
            });

        return Inertia::render('Lite/Dashboard', [
            'user'           => auth()->user()->load('roles'),
            'navigation'     => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'rooms'          => $rooms->values(),
            'devices'        => $devices->values(),
            'roomTypes'      => RoomType::orderBy('name')->get(['id', 'name', 'code']),
            'unassignedRooms' => Room::orderBy('room_number')->get(['id', 'room_number'])->values(),
        ]);
    }

    // ── Manual guest input (name → room → shows on TV) ─────────────────────

    /**
     * Set a guest's name for a room. Creates a minimal Guest + checked-in
     * Reservation so the existing IPTV display readers show the name. Any
     * previous checked-in reservation on the room is checked out first.
     */
    public function storeGuest(Request $request)
    {
        $data = $request->validate([
            'room_id'    => 'required|exists:rooms,id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
        ]);

        $room     = Room::findOrFail($data['room_id']);
        $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $parts    = preg_split('/\s+/', $fullName) ?: [];
        $firstName = array_shift($parts) ?? '';
        $lastName  = implode(' ', $parts);

        // Check out any existing checked-in reservation on this room
        $this->checkoutRoom($room);

        // Minimal guest row
        $guest = $this->createMinimalGuest($firstName, $lastName);

        // Determine a room_type_id for the reservation FK (required column)
        $roomTypeId = $room->room_type_id ?? RoomType::value('id');
        if (!$roomTypeId) {
            $roomTypeId = $this->ensureRoomType()->id;
        }

        $reservationNumber = 'LITE-' . strtoupper(Str::random(10));

        Reservation::create([
            'reservation_number'   => $reservationNumber,
            'guest_id'             => $guest->id,
            'room_id'              => $room->id,
            'room_type_id'         => $roomTypeId,
            'check_in_date'        => now()->toDateString(),
            'check_out_date'       => now()->addDays(1)->toDateString(),
            'nights'               => 1,
            'adults'               => 1,
            'children'             => 0,
            'infants'              => 0,
            'status'               => 'checked_in',
            'room_rate'            => 0,
            'total_room_charges'   => 0,
            'taxes'                => 0,
            'service_charges'      => 0,
            'discount_amount'      => 0,
            'total_amount'         => 0,
            'paid_amount'          => 0,
            'balance_amount'       => 0,
            'actual_check_in'      => now(),
            'booking_source'       => 'walk_in',
            'created_by'           => auth()->id(),
            'updated_by'           => auth()->id(),
        ]);

        // Make the room "occupied" so their status reflects the guest
        $room->update(['status' => 'occupied']);

        // Notify the device so the TV refreshes the welcome screen promptly
        $this->refreshRoomDevices($room);

        return redirect()->route('lite.dashboard')
            ->with('success', 'Guest name added to room ' . $room->room_number . ' — it will show on the TV.');
    }

    /**
     * Remove the guest's name from a room's TV.
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        $reservation = Reservation::findOrFail($data['reservation_id']);
        $roomId      = $reservation->room_id;
        $room        = $roomId ? Room::find($roomId) : null;

        $reservation->update([
            'status'          => 'checked_out',
            'actual_check_out'=> now(),
            'checked_out_by'  => auth()->id(),
        ]);

        if ($room) {
            $room->update(['status' => 'available']);
            $this->refreshRoomDevices($room);
        }

        return redirect()->route('lite.dashboard')->with('success', 'Guest name cleared from the room\'s TV.');
    }

    // ── Rooms ──────────────────────────────────────────────────────────────

    /**
     * Create a room. Room type is optional here — falls back to a generic one
     * so the operator only needs to type a room number.
     */
    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'room_number'  => 'required|string|unique:rooms,room_number|max:64',
            'room_type_id' => 'nullable|exists:room_types,id',
            'floor'        => 'nullable|string|max:64',
        ]);

        $roomTypeId = $data['room_type_id'] ?? RoomType::value('id');
        if (!$roomTypeId) {
            $roomTypeId = $this->ensureRoomType()->id;
        }

        $attributes = [
            'room_number'  => $data['room_number'],
            'room_type_id' => $roomTypeId,
            'status'       => 'available',
            'is_active'    => true,
        ];

        if (Schema::hasColumn('rooms', 'floor_id')) {
            // New schema: floor lives in the floors table via nullable FK
            $attributes['floor_id']  = null;
            $attributes['building_wing_id'] = null;
        } elseif (Schema::hasColumn('rooms', 'floor')) {
            $attributes['floor'] = $data['floor'] ?? '';
        }

        Room::create($attributes);

        return redirect()->route('lite.dashboard')->with('success', 'Room ' . $data['room_number'] . ' created.');
    }

    // ── Device <-> room attribution ────────────────────────────────────────

    /**
     * Attribute an IPTV/Android device to a room (same mechanism the admin
     * IPTV device page uses).
     */
    public function setDeviceRoom(Request $request, IptvDevice $device)
    {
        $data = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $device->update(['room_id' => $data['room_id'] ?? null]);

        return redirect()->route('lite.dashboard')->with('success', 'Device ' . ($device->device_name ?? $device->device_id) . ' room updated.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function guestDisplayName(?Reservation $res): ?string
    {
        if (!$res || !$res->guest) {
            return null;
        }
        return trim(($res->guest->first_name ?? '') . ' ' . ($res->guest->last_name ?? ''));
    }

    private function checkoutRoom(Room $room): void
    {
        Reservation::where('room_id', $room->id)
            ->where('status', 'checked_in')
            ->update(['status' => 'checked_out', 'actual_check_out' => now()]);
    }

    private function createMinimalGuest(string $firstName, string $lastName): Guest
    {
        do {
            $guestId = 'GST-' . Str::upper(Str::random(8));
        } while (Guest::where('guest_id', $guestId)->exists());

        $now = now();
        $fullName = trim($firstName . ' ' . $lastName);

        return Guest::create([
            'guest_id'                 => $guestId,
            'title'                    => '',
            'first_name'               => $firstName,
            'last_name'                => $lastName,
            'date_of_birth'            => $now->copy()->subYears(30)->toDateString(),
            'gender'                   => 'other',
            'nationality'              => '',
            'email'                    => null,
            'phone'                    => '',
            'emergency_contact_name'   => $fullName,
            'emergency_contact_phone'  => '',
            'emergency_contact_relationship' => 'self',
            'id_type'                  => 'other',
            'id_number'                => 'N/A-' . Str::upper(Str::random(8)),
            'id_issuing_authority'     => '',
            'id_issue_date'            => $now->toDateString(),
            'id_expiry_date'           => $now->copy()->addYears(10)->toDateString(),
            'purpose_of_visit'         => 'Hotel Stay',
            'police_verification_status' => 'pending',
            'created_by'               => auth()->id(),
            'updated_by'               => auth()->id(),
            'is_blacklisted'           => false,
            'is_vip'                   => false,
            'total_companions'         => 0,
        ]);
    }

    private function ensureRoomType(): RoomType
    {
        $existing = RoomType::first();
        if ($existing) {
            return $existing;
        }
        return RoomType::create([
            'name'          => 'Standard',
            'code'          => 'STD',
            'description'   => 'Default room type',
            'max_occupancy' => 2,
            'max_adults'    => 2,
            'max_children'  => 0,
            'base_price'    => 0.00,
            'is_active'     => true,
        ]);
    }

    private function refreshRoomDevices(Room $room): void
    {
        $room->iptvDevices()->where('is_active', true)->each(function (IptvDevice $device) {
            $device->update(['settings_version' => ($device->settings_version ?? 0) + 1]);
            $device->dispatchCommand('reload_app', ['reason' => 'guest-name-update']);
        });
    }
}
