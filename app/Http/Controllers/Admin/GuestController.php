<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        do {
            $guestId = 'GST-' . Str::upper(Str::random(8));
        } while (Guest::where('guest_id', $guestId)->exists());

        $now = now();

        $guest = Guest::create([
            'guest_id' => $guestId,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'date_of_birth' => $now->copy()->subYears(30)->toDateString(),
            'gender' => 'other',
            'nationality' => 'Unknown',
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => 'N/A',
            'city' => 'N/A',
            'state' => 'N/A',
            'country' => 'N/A',
            'emergency_contact_name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'emergency_contact_phone' => $validated['phone'],
            'emergency_contact_relationship' => 'self',
            'id_type' => 'other',
            'id_number' => 'N/A-' . Str::upper(Str::random(8)),
            'id_issuing_authority' => 'N/A',
            'id_issue_date' => $now->toDateString(),
            'id_expiry_date' => $now->copy()->addYears(10)->toDateString(),
            'purpose_of_visit' => 'Hotel Stay',
            'police_verification_status' => 'pending',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'is_blacklisted' => false,
            'is_vip' => false,
            'total_companions' => 0,
        ]);

        return redirect()->route('admin.guests.index')
            ->with('success', 'Guest added successfully.');
    }

    public function index()
    {
        $guests = Guest::with(['createdBy', 'reservations.room', 'guestType'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function($guest) {
                $currentReservation = $guest->reservations
                    ->filter(function ($reservation) {
                        return $reservation->status === 'checked_in'
                            && $reservation->actual_check_out === null
                            && $reservation->room_id !== null
                            && $reservation->room !== null;
                    })
                    ->first();
                return [
                    'id' => $guest->id,
                    'guest_id' => $guest->guest_id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'nationality' => $guest->nationality,
                    'id_number' => $guest->id_number,
                    'current_room' => $currentReservation?->room?->room_number ?? null,
                    'current_reservation_id' => $currentReservation?->id ?? null,
                    'current_reservation' => $currentReservation?->id ?? null,
                    'checkout_date' => $currentReservation?->check_out_date ? $currentReservation->check_out_date->format('Y-m-d') : null,
                    'type' => $guest->is_vip ? 'vip' : ($guest->guestType ? strtolower($guest->guestType->name) : 'regular'),
                    'guest_type' => $guest->guestType ? [
                        'id' => $guest->guestType->id,
                        'name' => $guest->guestType->name,
                        'code' => $guest->guestType->code,
                        'color' => $guest->guestType->color,
                    ] : null,
                    'total_stays' => $guest->reservations->count(),
                    'status' => $currentReservation?->status ?? 'checked_out',
                    'police_verification_status' => $guest->police_verification_status,
                ];
            });
        
        $stats = [
            'total' => Guest::count(),
            'current' => Guest::whereHas('reservations', function($query) {
                $query->where('status', 'checked_in')
                    ->whereNull('actual_check_out')
                    ->whereNotNull('room_id')
                    ->whereHas('room');
            })->count(),
            'vip' => Guest::where('is_vip', true)->count(),
            'returning' => Guest::has('reservations', '>', 1)->count(),
        ];
        
        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');
        $isFrontDesk = str_starts_with($routeName, 'front-desk.');
        
        $viewPath = 'Admin/Guests/Index';
        if ($isManager) {
            $viewPath = 'Manager/Guests/Index';
        } elseif ($isFrontDesk) {
            $viewPath = 'FrontDesk/Guests/Index';
        }
        
        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'guests' => $guests,
            'guestStats' => $stats,
        ]);
    }

    public function create()
    {
        $guestTypes = GuestType::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color', 'discount_percentage']);
        
        // Determine which view to render based on route name (prioritize route over role)
        $routeName = request()->route()->getName() ?? '';
        $user = auth()->user();
        
        $viewPath = 'Admin/Guests/Create';
        
        // Prioritize route name - if route starts with manager., use manager view
        if (str_starts_with($routeName, 'manager.')) {
            $viewPath = 'Manager/Guests/Create';
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            $viewPath = 'FrontDesk/Guests/Create';
        } elseif ($user->hasRole('manager') && !$user->hasRole('admin')) {
            // Fallback: if user is manager (but not admin), use manager view
            $viewPath = 'Manager/Guests/Create';
        } elseif ($user->hasRole('front_desk') && !$user->hasRole('admin') && !$user->hasRole('manager')) {
            // Fallback: if user is front_desk only, use front-desk view
            $viewPath = 'FrontDesk/Guests/Create';
        }
        
        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'guestTypes' => $guestTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Personal Information
            'guest_type_id' => 'nullable|exists:guest_types,id',
            'title' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'nationality' => 'required|string|max:100',
            'occupation' => 'nullable|string|max:255',
            
            // Contact Information
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            
            // Emergency Contact
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:100',
            'emergency_contact_address' => 'nullable|string',
            
            // Identification Documents
            'id_type' => 'required|in:passport,national_id,drivers_license,other',
            'id_number' => 'required|string|max:100',
            'id_issuing_authority' => 'required|string|max:255',
            'id_issue_date' => 'required|date',
            'id_expiry_date' => 'required|date|after:id_issue_date',
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Passport Details (if applicable)
            'passport_number' => 'nullable|string|max:100',
            'passport_issuing_country' => 'nullable|string|max:100',
            'passport_issue_date' => 'nullable|date',
            'passport_expiry_date' => 'nullable|date|after:passport_issue_date',
            'passport_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Visa Details
            'visa_number' => 'nullable|string|max:100',
            'visa_type' => 'nullable|string|max:100',
            'visa_issue_date' => 'nullable|date',
            'visa_expiry_date' => 'nullable|date|after:visa_issue_date',
            'visa_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Travel Information
            'arrival_from' => 'nullable|string|max:255',
            'departure_to' => 'nullable|string|max:255',
            'purpose_of_visit' => 'required|string|max:255',
            'expected_duration_days' => 'nullable|integer|min:1',
            
            // Companion Information
            'total_companions' => 'nullable|integer|min:0',
            'companion_details' => 'nullable|array',
            
            // Vehicle Information
            'vehicle_registration' => 'nullable|string|max:50',
            'vehicle_make_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:50',
            
            // Preferences
            'preferences' => 'nullable|array',
            'special_requests' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'dietary_restrictions' => 'nullable|string',
            
            // System Fields
            'is_vip' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        do {
            $guestId = 'GST-' . Str::upper(Str::random(8));
        } while (Guest::where('guest_id', $guestId)->exists());

        $validated['guest_id'] = $guestId;

        // Handle file uploads
        if ($request->hasFile('id_document')) {
            $validated['id_document_path'] = $request->file('id_document')->store('guest-documents', 'public');
        }
        
        if ($request->hasFile('passport_document')) {
            $validated['passport_document_path'] = $request->file('passport_document')->store('guest-documents', 'public');
        }
        
        if ($request->hasFile('visa_document')) {
            $validated['visa_document_path'] = $request->file('visa_document')->store('guest-documents', 'public');
        }

        // Set default values
        $validated['police_verification_status'] = 'verified';
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['is_blacklisted'] = false;
        $validated['total_companions'] = $validated['total_companions'] ?? 0;
        $validated['is_vip'] = $validated['is_vip'] ?? false;

        // Remove file objects from validated array before creating
        unset($validated['id_document'], $validated['passport_document'], $validated['visa_document']);

        $guest = Guest::create($validated);

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.guests.index')
                ->with('success', 'Guest registered successfully. Police verification is pending.');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.guests.index')
                ->with('success', 'Guest registered successfully. Police verification is pending.');
        }

        return redirect()->route('admin.guests.index')
            ->with('success', 'Guest registered successfully. Police verification is pending.');
    }

    public function show(Guest $guest)
    {
        $guest->load(['createdBy', 'updatedBy', 'reservations.room', 'reservations.roomType']);
        
        // Determine current reservation status
        $currentReservation = $guest->reservations->where('status', 'checked_in')->first();
        $mostRecentReservation = $guest->reservations->sortByDesc('check_out_date')->first();
        
        // Set current status based on reservations
        $currentStatus = 'no_reservations';
        if ($currentReservation) {
            $currentStatus = 'checked_in';
        } elseif ($mostRecentReservation) {
            $currentStatus = $mostRecentReservation->status;
        }
        
        // Add computed status to guest data
        $guest->current_status = $currentStatus;
        $guest->current_reservation = $currentReservation;
        
        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');
        $isFrontDesk = str_starts_with($routeName, 'front-desk.');
        
        $viewPath = 'Admin/Guests/Show';
        if ($isManager) {
            $viewPath = 'Manager/Guests/Show';
        } elseif ($isFrontDesk) {
            $viewPath = 'FrontDesk/Guests/Show';
        }
        
        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'guest' => $guest,
        ]);
    }

    public function edit(Guest $guest)
    {
        $guestTypes = GuestType::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color', 'discount_percentage']);
        
        // Determine which view to render based on route name
        $routeName = request()->route()->getName() ?? '';
        $isManager = str_starts_with($routeName, 'manager.');
        $isFrontDesk = str_starts_with($routeName, 'front-desk.');
        
        $viewPath = 'Admin/Guests/Edit';
        if ($isManager) {
            $viewPath = 'Manager/Guests/Edit';
        } elseif ($isFrontDesk) {
            $viewPath = 'FrontDesk/Guests/Edit';
        }
        
        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'guest' => $guest,
            'guestTypes' => $guestTypes,
        ]);
    }

    public function update(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            // Similar validation as store, but with nullable for most fields
            'guest_type_id' => 'nullable|exists:guest_types,id',
            'title' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'nationality' => 'required|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:100',
            'emergency_contact_address' => 'nullable|string',
            'id_type' => 'required|in:passport,national_id,drivers_license,other',
            'id_number' => 'required|string|max:100',
            'id_issuing_authority' => 'required|string|max:255',
            'id_issue_date' => 'required|date',
            'id_expiry_date' => 'required|date|after:id_issue_date',
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'passport_number' => 'nullable|string|max:100',
            'passport_issuing_country' => 'nullable|string|max:100',
            'passport_issue_date' => 'nullable|date',
            'passport_expiry_date' => 'nullable|date|after:passport_issue_date',
            'passport_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'visa_number' => 'nullable|string|max:100',
            'visa_type' => 'nullable|string|max:100',
            'visa_issue_date' => 'nullable|date',
            'visa_expiry_date' => 'nullable|date|after:visa_issue_date',
            'visa_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'arrival_from' => 'nullable|string|max:255',
            'departure_to' => 'nullable|string|max:255',
            'purpose_of_visit' => 'required|string|max:255',
            'expected_duration_days' => 'nullable|integer|min:1',
            'total_companions' => 'nullable|integer|min:0',
            'companion_details' => 'nullable|array',
            'vehicle_registration' => 'nullable|string|max:50',
            'vehicle_make_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:50',
            'preferences' => 'nullable|array',
            'special_requests' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'dietary_restrictions' => 'nullable|string',
            'is_vip' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        // Handle file uploads
        if ($request->hasFile('id_document')) {
            if ($guest->id_document_path) {
                Storage::disk('public')->delete($guest->id_document_path);
            }
            $validated['id_document_path'] = $request->file('id_document')->store('guest-documents', 'public');
        }
        
        if ($request->hasFile('passport_document')) {
            if ($guest->passport_document_path) {
                Storage::disk('public')->delete($guest->passport_document_path);
            }
            $validated['passport_document_path'] = $request->file('passport_document')->store('guest-documents', 'public');
        }
        
        if ($request->hasFile('visa_document')) {
            if ($guest->visa_document_path) {
                Storage::disk('public')->delete($guest->visa_document_path);
            }
            $validated['visa_document_path'] = $request->file('visa_document')->store('guest-documents', 'public');
        }

        $validated['updated_by'] = auth()->id();
        unset($validated['id_document'], $validated['passport_document'], $validated['visa_document']);

        $guest->update($validated);

        // When a guest's identity/stay details are updated, any checked-in reservation
        // whose police data was already marked 'sent' needs to be re-reported.
        \App\Models\Reservation::where('guest_id', $guest->id)
            ->where('status', 'checked_in')
            ->where('police_report_status', 'sent')
            ->update(['police_report_status' => 'modified']);

        // Redirect based on route name
        $routeName = request()->route()->getName() ?? '';
        if (str_starts_with($routeName, 'manager.')) {
            return redirect()->route('manager.guests.index')
                ->with('success', 'Guest information updated successfully.');
        } elseif (str_starts_with($routeName, 'front-desk.')) {
            return redirect()->route('front-desk.guests.index')
                ->with('success', 'Guest information updated successfully.');
        }

        return redirect()->route('admin.guests.index')
            ->with('success', 'Guest information updated successfully.');
    }

    public function destroy(Guest $guest)
    {
        // Delete associated documents
        if ($guest->id_document_path) {
            Storage::disk('public')->delete($guest->id_document_path);
        }
        if ($guest->passport_document_path) {
            Storage::disk('public')->delete($guest->passport_document_path);
        }
        if ($guest->visa_document_path) {
            Storage::disk('public')->delete($guest->visa_document_path);
        }

        $guest->delete();

        return redirect()->route('admin.guests.index')
            ->with('success', 'Guest deleted successfully.');
    }
}
