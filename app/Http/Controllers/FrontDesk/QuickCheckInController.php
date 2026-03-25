<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\DashboardController;
use App\Models\Guest;
use App\Models\KeyCard;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QuickCheckInController extends CheckInController
{
    public function create(Request $request)
    {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'front_desk';
        $routePrefix = $this->routePrefixForUser($user);
        $selectedRoomId = $request->query('room_id') ? (int) $request->query('room_id') : null;
        $selectedGuestId = $request->query('guest_id') ? (int) $request->query('guest_id') : null;

        $availableRooms = Room::with('roomType')
            ->where('status', 'available')
            ->where('housekeeping_status', 'clean')
            ->orderBy('room_number')
            ->get()
            ->map(fn (Room $room) => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->roomType?->name ?? 'Standard',
                'room_type_id' => $room->room_type_id,
                'room_rate' => (float) ($room->roomType?->base_price ?? 0),
                'max_adults' => $room->roomType?->max_adults,
                'max_children' => $room->roomType?->max_children,
            ])
            ->values();

        $availableKeyCards = KeyCard::available()
            ->orderBy('card_number')
            ->get(['id', 'card_number', 'card_type'])
            ->map(fn ($card) => [
                'id' => $card->id,
                'card_number' => $card->card_number,
                'card_type' => $card->card_type,
            ])
            ->values();

        $selectedGuest = $selectedGuestId
            ? Guest::find($selectedGuestId, ['id', 'first_name', 'last_name', 'email', 'phone'])
            : null;

        return Inertia::render('Shared/QuickCheckIn', [
            'user' => $user,
            'navigation' => app(DashboardController::class)->getNavigationForRole($role),
            'routePrefix' => $routePrefix,
            'availableRooms' => $availableRooms,
            'availableKeyCards' => $availableKeyCards,
            'selectedRoomId' => $selectedRoomId,
            'selectedGuest' => $selectedGuest ? [
                'id' => $selectedGuest->id,
                'name' => trim(($selectedGuest->first_name ?? '') . ' ' . ($selectedGuest->last_name ?? '')),
                'email' => $selectedGuest->email,
                'phone' => $selectedGuest->phone,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_mode' => 'required|in:existing,new',
            'guest_id' => 'nullable|required_if:guest_mode,existing|exists:guests,id',
            'first_name' => 'nullable|required_if:guest_mode,new|string|max:255',
            'last_name' => 'nullable|required_if:guest_mode,new|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'id_type' => 'nullable|string|in:passport,national_id,drivers_license,other',
            'id_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_adults' => 'required|integer|min:1|max:20',
            'number_of_children' => 'nullable|integer|min:0|max:20',
            'room_rate' => 'nullable|numeric|min:0',
            'special_requests' => 'nullable|string',
            'key_card_id' => 'nullable|exists:key_cards,id',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,bank_transfer',
        ]);

        if (($validated['guest_mode'] ?? null) === 'new' && empty($validated['email']) && empty($validated['phone'])) {
            return back()->withErrors([
                'phone' => 'Provide at least a phone number or email for the guest.',
            ])->withInput();
        }

        $room = Room::with('roomType')->findOrFail($validated['room_id']);
        if ($room->status !== 'available' || $room->housekeeping_status !== 'clean') {
            return back()->withErrors([
                'room_id' => 'Selected room is no longer available for quick check-in.',
            ])->withInput();
        }

        $routePrefix = $this->routePrefixForUser(auth()->user());

        $outcome = DB::transaction(function () use ($validated, $room) {
            $guest = $this->resolveGuest($validated);

            $checkInDate = now()->parse($validated['check_in_date']);
            $checkOutDate = now()->parse($validated['check_out_date']);
            $nights = max(1, $checkInDate->diffInDays($checkOutDate));
            $roomRate = isset($validated['room_rate']) && $validated['room_rate'] !== null
                ? (float) $validated['room_rate']
                : (float) ($room->roomType?->base_price ?? 0);

            $reservation = Reservation::create([
                'reservation_number' => 'RES' . strtoupper(Str::random(10)),
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'room_type_id' => $room->room_type_id,
                'check_in_date' => $checkInDate->toDateString(),
                'check_out_date' => $checkOutDate->toDateString(),
                'nights' => $nights,
                'number_of_adults' => (int) $validated['number_of_adults'],
                'number_of_children' => (int) ($validated['number_of_children'] ?? 0),
                'adults' => (int) $validated['number_of_adults'],
                'children' => (int) ($validated['number_of_children'] ?? 0),
                'status' => 'confirmed',
                'booking_source' => 'walk_in',
                'room_rate' => $roomRate,
                'total_room_charges' => $roomRate * $nights,
                'total_amount' => $roomRate * $nights,
                'balance_amount' => $roomRate * $nights,
                'paid_amount' => 0,
                'special_requests' => $validated['special_requests'] ?? null,
                'police_report_status' => 'new',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return $this->performCheckIn($reservation, $room, [
                'reservation_id' => $reservation->id,
                'room_number' => $room->room_number,
                'key_card_id' => $validated['key_card_id'] ?? null,
                'payment_amount' => $validated['payment_amount'] ?? 0,
                'payment_method' => $validated['payment_method'] ?? 'cash',
            ]);
        });

        return $this->buildCheckInRedirect(
            $outcome['reservation'],
            $outcome['payment_amount'],
            $outcome['success_message'],
            $routePrefix . '.quick-checkin'
        );
    }

    private function resolveGuest(array $validated): Guest
    {
        if (($validated['guest_mode'] ?? null) === 'existing') {
            return Guest::findOrFail($validated['guest_id']);
        }

        $guestAttributes = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'nationality' => $validated['nationality'] ?? 'Unknown',
            'address' => $validated['address'] ?? null,
            'id_type' => $validated['id_type'] ?? 'other',
            'id_number' => $validated['id_number'] ?? null,
            'police_verification_status' => 'pending',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if (!empty($validated['email'])) {
            return Guest::updateOrCreate(
                ['email' => $validated['email']],
                $guestAttributes
            );
        }

        do {
            $guestId = 'GST-' . Str::upper(Str::random(8));
        } while (Guest::where('guest_id', $guestId)->exists());

        $guestAttributes['guest_id'] = $guestId;

        return Guest::create($guestAttributes);
    }

    private function routePrefixForUser($user): string
    {
        if ($user->hasRole('admin')) {
            return 'admin';
        }

        if ($user->hasRole('manager')) {
            return 'manager';
        }

        return 'front-desk';
    }
}