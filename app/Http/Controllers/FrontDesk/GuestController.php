<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index()
    {
        $guests = Guest::all();
        
        return Inertia::render('FrontDesk/Guests/Index', [
            'user' => auth()->user()->load('roles'),
            'guests' => $guests,
        ]);
    }
    
    public function current()
    {
        $currentGuests = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'guest_name' => $r->guest->full_name ?? 'N/A',
                'room_number' => $r->room->room_number ?? 'N/A',
                'check_in_date' => $r->check_in_date,
                'check_out_date' => $r->check_out_date,
                'nights' => $r->nights,
            ]);
        
        return Inertia::render('FrontDesk/Guests/Current', [
            'user' => auth()->user()->load('roles'),
            'currentGuests' => $currentGuests,
        ]);
    }
    
    public function create()
    {
        // Get guest types for dropdown
        $guestTypes = \App\Models\GuestType::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return Inertia::render('FrontDesk/Guests/Create', [
            'user' => auth()->user()->load('roles'),
            'guestTypes' => $guestTypes,
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:guests,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'nationality' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'guest_type_id' => 'nullable|exists:guest_types,id',
            'is_vip' => 'nullable|boolean',
            'id_type' => 'nullable|string|in:passport,national_id,drivers_license,other',
            'id_number' => 'nullable|string|max:255',
            'id_issue_date' => 'nullable|date',
            'id_expiry_date' => 'nullable|date|after:id_issue_date',
            'purpose_of_visit' => 'nullable|string|in:business,leisure,medical,education,transit,other',
        ]);
        
        Guest::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'nationality' => $validated['nationality'],
            'country' => $validated['country'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'guest_type_id' => $validated['guest_type_id'],
            'is_vip' => $validated['is_vip'] ?? false,
            'id_type' => $validated['id_type'],
            'id_number' => $validated['id_number'],
            'id_issue_date' => $validated['id_issue_date'],
            'id_expiry_date' => $validated['id_expiry_date'],
            'purpose_of_visit' => $validated['purpose_of_visit'],
            'police_verification_status' => 'pending',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        
        return redirect()->route('front-desk.reservations.create')
            ->with('success', 'Guest created successfully! You can now select them for reservation.');
    }
    
    public function requests()
    {
        return Inertia::render('FrontDesk/Guests/Requests', [
            'user' => auth()->user()->load('roles'),
            'requests' => [],
        ]);
    }
}
