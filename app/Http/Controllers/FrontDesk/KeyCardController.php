<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\KeyCard;
use App\Models\KeyCardAssignment;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Str;

class KeyCardController extends Controller
{
    public function index()
    {
        $keyCards = KeyCard::with(['reservation.guest', 'room', 'guest', 'issuedBy', 'returnedTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'total' => KeyCard::count(),
            'available' => KeyCard::available()->count(),
            'assigned' => KeyCard::assigned()->count(),
            'lost' => KeyCard::where('status', 'lost')->count(),
            'damaged' => KeyCard::where('status', 'damaged')->count(),
        ];

        // Get available key cards for assignment
        $availableKeyCards = KeyCard::available()
            ->orderBy('card_number')
            ->get(['id', 'card_number', 'card_type']);

        // Get checked-in reservations for assignment
        $checkedInReservations = \App\Models\Reservation::with(['guest', 'room'])
            ->whereNotNull('actual_check_in')
            ->whereNull('actual_check_out')
            ->orderBy('actual_check_in', 'desc')
            ->get(['id', 'reservation_number', 'guest_id', 'room_id', 'check_in_date', 'check_out_date', 'actual_check_in']);

        // Transform reservations for the frontend
        $checkedInReservations = $checkedInReservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'guest_name' => $reservation->guest ? $reservation->guest->full_name : 'Unknown',
                'room_number' => $reservation->room ? $reservation->room->room_number : 'Unassigned',
                'room_id' => $reservation->room_id,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
            ];
        });

        // Get available rooms for assignment
        $availableRooms = \App\Models\Room::with('roomType')
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'room_type_id']);

        return Inertia::render('FrontDesk/KeyCards/Index', [
            'user' => auth()->user()->load('roles'),
            'keyCards' => $keyCards->through(function($card) {
                return [
                    'id' => $card->id,
                    'card_number' => $card->card_number,
                    'card_type' => $card->card_type,
                    'status' => $card->status,
                    'room' => $card->room ? [
                        'id' => $card->room->id,
                        'number' => $card->room->room_number,
                    ] : null,
                    'guest' => $card->guest ? [
                        'id' => $card->guest->id,
                        'name' => $card->guest->full_name,
                    ] : ($card->reservation?->guest ? [
                        'id' => $card->reservation->guest->id,
                        'name' => $card->reservation->guest->full_name,
                    ] : null),
                    'reservation' => $card->reservation ? [
                        'id' => $card->reservation->id,
                        'number' => $card->reservation->reservation_number,
                    ] : null,
                    'issued_at' => $card->issued_at?->format('Y-m-d H:i'),
                    'returned_at' => $card->returned_at?->format('Y-m-d H:i'),
                    'expires_at' => $card->expires_at?->format('Y-m-d H:i'),
                    'issued_by' => $card->issuedBy?->full_name,
                    'notes' => $card->notes,
                ];
            }),
            'stats' => $stats,
            'availableKeyCards' => $availableKeyCards,
            'checkedInReservations' => $checkedInReservations,
            'availableRooms' => $availableRooms,
        ]);
    }

    public function create()
    {
        return Inertia::render('FrontDesk/KeyCards/Create', [
            'user' => auth()->user()->load('roles'),
        ]);
    }

    public function show(KeyCard $keyCard)
    {
        $keyCard->load(['reservation.guest', 'room', 'guest', 'issuedBy', 'returnedTo']);
        
        // Get assignment history
        $assignmentHistory = KeyCardAssignment::with(['guest', 'room', 'assignedBy', 'returnedTo'])
            ->where('key_card_id', $keyCard->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('FrontDesk/KeyCards/Show', [
            'user' => auth()->user()->load('roles'),
            'keyCard' => $keyCard,
            'assignmentHistory' => $assignmentHistory,
        ]);
    }

    public function edit(KeyCard $keyCard)
    {
        return Inertia::render('FrontDesk/KeyCards/Edit', [
            'user' => auth()->user()->load('roles'),
            'keyCard' => $keyCard,
        ]);
    }

    public function update(Request $request, KeyCard $keyCard)
    {
        $validated = $request->validate([
            'card_number' => 'required|string|max:255|unique:key_cards,card_number,' . $keyCard->id,
            'card_type' => 'required|in:standard,master,staff,maintenance',
            'notes' => 'nullable|string',
        ]);

        $keyCard->update($validated);

        return redirect()->route('front-desk.key-cards.show', $keyCard->id)
            ->with('success', 'Key card updated successfully');
    }

    public function assignment()
    {
        // Get available key cards
        $availableKeyCards = KeyCard::available()
            ->orderBy('card_number')
            ->get(['id', 'card_number', 'card_type']);

        // Get checked-in reservations
        $checkedInReservations = \App\Models\Reservation::with(['guest', 'room'])
            ->whereNotNull('actual_check_in')
            ->whereNull('actual_check_out')
            ->orderBy('actual_check_in', 'desc')
            ->get();

        // Get available rooms
        $availableRooms = \App\Models\Room::with('roomType')
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return Inertia::render('FrontDesk/KeyCards/Assignment', [
            'user' => auth()->user()->load('roles'),
            'availableKeyCards' => $availableKeyCards,
            'checkedInReservations' => $checkedInReservations,
            'availableRooms' => $availableRooms,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_number' => 'required|string|max:255|unique:key_cards,card_number',
            'card_type' => 'required|in:standard,master,staff,maintenance',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'available';
        $validated['is_active'] = true;

        KeyCard::create($validated);

        return redirect()->route('front-desk.key-cards.index')
            ->with('success', 'Key card created successfully');
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'key_card_id' => 'required|exists:key_cards,id',
            'reservation_id' => 'required|exists:reservations,id',
            'room_id' => 'required|exists:rooms,id',
        ]);

        $keyCard = KeyCard::findOrFail($validated['key_card_id']);
        $reservation = Reservation::findOrFail($validated['reservation_id']);
        $room = Room::findOrFail($validated['room_id']);

        // Check if key card is available
        if ($keyCard->status !== 'available') {
            return back()->with('error', 'Key card is not available for assignment');
        }

        // Check if reservation is checked in
        if (!$reservation->actual_check_in || $reservation->actual_check_out) {
            return back()->with('error', 'Reservation must be currently checked in to assign key card');
        }

        // Update key card
        $keyCard->update([
            'status' => 'assigned',
            'reservation_id' => $reservation->id,
            'room_id' => $room->id,
            'guest_id' => $reservation->guest_id,
            'issued_at' => now(),
            'issued_by' => auth()->id(),
        ]);

        // Create assignment record
        KeyCardAssignment::create([
            'key_card_id' => $keyCard->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $room->id,
            'reservation_id' => $reservation->id,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'action' => 'assigned',
            'notes' => 'Key card assigned during check-in',
        ]);

        Log::info('Key card assigned', [
            'key_card_id' => $keyCard->id,
            'card_number' => $keyCard->card_number,
            'reservation_id' => $reservation->id,
            'assigned_by' => auth()->id(),
        ]);

        return redirect()->route('front-desk.key-cards.index')
            ->with('success', 'Key card assigned successfully');
    }

    public function return(Request $request, KeyCard $keyCard)
    {
        if ($keyCard->status !== 'assigned') {
            return back()->withErrors(['error' => 'This key card is not currently assigned. Current status: ' . $keyCard->status]);
        }

        $guestName = $keyCard->guest ? $keyCard->guest->full_name : 'Unknown';
        $roomNumber = $keyCard->room ? $keyCard->room->room_number : 'Unknown';

        // Update key card
        $keyCard->update([
            'status' => 'available',
            'reservation_id' => null,
            'room_id' => null,
            'guest_id' => null,
            'returned_at' => now(),
            'returned_to' => auth()->id(),
        ]);

        // Create return assignment record
        KeyCardAssignment::create([
            'key_card_id' => $keyCard->id,
            'guest_id' => $keyCard->guest_id,
            'room_id' => $keyCard->room_id,
            'reservation_id' => $keyCard->reservation_id,
            'assigned_by' => $keyCard->issued_by,
            'assigned_at' => $keyCard->issued_at,
            'returned_to' => auth()->id(),
            'returned_at' => now(),
            'action' => 'returned',
            'notes' => 'Key card returned during check-out',
        ]);

        Log::info('Key card returned', [
            'key_card_id' => $keyCard->id,
            'card_number' => $keyCard->card_number,
            'returned_by' => auth()->id(),
            'guest_name' => $guestName,
            'room_number' => $roomNumber,
        ]);

        return redirect()->route('front-desk.key-cards.index')
            ->with('success', "Key card {$keyCard->card_number} returned successfully from {$guestName} (Room {$roomNumber})");
    }

    public function markLost(Request $request, KeyCard $keyCard)
    {
        if ($keyCard->status !== 'assigned') {
            return back()->withErrors(['error' => 'Only assigned key cards can be marked as lost']);
        }

        $guestName = $keyCard->guest ? $keyCard->guest->full_name : 'Unknown';
        $roomNumber = $keyCard->room ? $keyCard->room->room_number : 'Unknown';

        // Log the lost card
        \Log::warning('Key card marked as lost', [
            'key_card_id' => $keyCard->id,
            'card_number' => $keyCard->card_number,
            'marked_by' => auth()->id(),
            'guest' => $guestName,
            'room' => $roomNumber
        ]);

        $keyCard->markAsLost();

        // Create a notification for management
        // You might want to implement a notification system here

        return redirect()->back()->with('success', 'Key card ' . $keyCard->card_number . ' marked as lost. Guest: ' . $guestName . ', Room: ' . $roomNumber);
    }

    public function markDamaged(Request $request, KeyCard $keyCard)
    {
        if ($keyCard->status !== 'assigned') {
            return back()->withErrors(['error' => 'Only assigned key cards can be marked as damaged']);
        }

        $guestName = $keyCard->guest ? $keyCard->guest->full_name : 'Unknown';
        $roomNumber = $keyCard->room ? $keyCard->room->room_number : 'Unknown';

        // Log the damaged card
        \Log::warning('Key card marked as damaged', [
            'key_card_id' => $keyCard->id,
            'card_number' => $keyCard->card_number,
            'marked_by' => auth()->id(),
            'guest' => $guestName,
            'room' => $roomNumber
        ]);

        $keyCard->markAsDamaged();

        // Create a notification for maintenance team
        // You might want to implement a notification system here

        return redirect()->back()->with('success', 'Key card ' . $keyCard->card_number . ' marked as damaged. Guest: ' . $guestName . ', Room: ' . $roomNumber);
    }

    public function bulkReturn(Request $request)
    {
        $validated = $request->validate([
            'card_ids' => 'required|array',
            'card_ids.*' => 'exists:key_cards,id',
        ]);

        $returnedCards = [];
        $errors = [];

        foreach ($validated['card_ids'] as $cardId) {
            $keyCard = KeyCard::find($cardId);
            
            if (!$keyCard) {
                $errors[] = "Key card with ID {$cardId} not found";
                continue;
            }

            if ($keyCard->status !== 'assigned') {
                $errors[] = "Key card {$keyCard->card_number} is not currently assigned";
                continue;
            }

            $guestName = $keyCard->guest ? $keyCard->guest->full_name : 'Unknown';
            $roomNumber = $keyCard->room ? $keyCard->room->room_number : 'Unknown';

            // Log the return
            \Log::info('Key card bulk returned', [
                'key_card_id' => $keyCard->id,
                'card_number' => $keyCard->card_number,
                'returned_by' => auth()->id(),
                'previous_guest' => $guestName,
                'previous_room' => $roomNumber
            ]);

            $keyCard->returnCard(auth()->id());
            $returnedCards[] = $keyCard->card_number;
        }

        $message = count($returnedCards) > 0 
            ? 'Successfully returned ' . count($returnedCards) . ' key cards: ' . implode(', ', $returnedCards)
            : 'No key cards were returned';

        if (!empty($errors)) {
            $message .= '. Errors: ' . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    public function deactivate(KeyCard $keyCard)
    {
        $keyCard->deactivate();

        return redirect()->back()->with('success', 'Key card deactivated');
    }
}
