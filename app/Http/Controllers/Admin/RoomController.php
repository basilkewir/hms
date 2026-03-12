<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomAmenity;
use App\Models\Floor;
use App\Models\BedType;
use App\Models\Reservation;
use App\Models\User;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Services\LicenseValidationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;

class RoomController extends Controller
{
    public function index()
    {
        // Determine which view to render based on route
        $user = auth()->user();
        $isManager = $user->hasRole('manager');

        $perPage = (int) request()->get('per_page', 15);
        $perPage = max(5, min($perPage, 100));

        // For managers, show rooms that need attention (dirty/cleaning/waiting_for_check) OR are occupied/available
        // If room is not dirty or being cleaned, then it should be occupied or available
        $roomsQuery = Room::with(['roomType', 'floor', 'currentReservation.guest']);

        if ($isManager) {
            $roomsQuery->where(function($query) {
                $query->where('status', 'cleaning')
                    ->orWhere('housekeeping_status', 'dirty')
                    ->orWhere('housekeeping_status', 'waiting_for_check')
                    ->orWhere('status', 'occupied')
                    ->orWhere('status', 'available');
            });
        }

        // Calculate stats - include cleaning status
        // For stats, we need all rooms to get accurate counts
        $allRooms = Room::with(['roomType', 'floor', 'currentReservation.guest'])->get();
        $stats = [
            'total' => $allRooms->count(),
            'available' => $allRooms->where('status', 'available')->count(),
            'occupied' => $allRooms->where('status', 'occupied')->count(),
            'cleaning' => $allRooms->filter(function($room) {
                return $room->status === 'cleaning' || $room->housekeeping_status === 'dirty' || $room->housekeeping_status === 'waiting_for_check';
            })->count(),
            'maintenance' => $allRooms->where('status', 'maintenance')->count(),
        ];

        // Get floors for filter dropdown
        $floors = [];
        if (Schema::hasColumn('rooms', 'floor_id')) {
            $floors = Floor::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('floor_number')
                ->get(['id', 'floor_number', 'name']);
        }

        $viewPath = $isManager ? 'Manager/Rooms/Index' : 'Admin/Rooms/Index';

        $rooms = $roomsQuery
            ->orderBy('room_number')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function($room) {
                // Get floor display - handle both new relationship and old column
                $floorDisplay = null;
                $floorNumber = null;

                if ($room->floor) {
                    // New relationship exists
                    $floorDisplay = $room->floor->name ?: 'Floor ' . $room->floor->floor_number;
                    $floorNumber = $room->floor->floor_number;
                } elseif ($room->floor_id) {
                    // Has floor_id but relationship not loaded
                    $floorNumber = $room->floor_id;
                    $floorDisplay = 'Floor ' . $room->floor_id;
                } elseif (isset($room->attributes['floor'])) {
                    // Old floor column
                    $floorValue = $room->attributes['floor'];
                    $floorNumber = is_numeric($floorValue) ? $floorValue : null;
                    $floorDisplay = is_numeric($floorValue) ? 'Floor ' . $floorValue : $floorValue;
                }

                // Get current reservation and guest info
                $currentReservation = $room->currentReservation;
                $guest = $currentReservation?->guest;

                // Get pending housekeeping tasks for this room
                $housekeepingTasks = HousekeepingTask::where('room_id', $room->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->with('assignedTo')
                    ->get()
                    ->map(function($task) {
                        // Handle scheduled_time - it's stored as string (H:i format)
                        // Format time string to H:i (remove seconds if present)
                        $scheduledTime = null;
                        if ($task->scheduled_time) {
                            if (is_string($task->scheduled_time)) {
                                // Remove seconds if present (HH:MM:SS -> HH:MM)
                                $scheduledTime = substr($task->scheduled_time, 0, 5);
                            } elseif ($task->scheduled_time instanceof \DateTime || $task->scheduled_time instanceof \Carbon\Carbon) {
                                $scheduledTime = $task->scheduled_time->format('H:i');
                            }
                        }

                        return [
                            'id' => $task->id,
                            'task_type' => $task->task_type,
                            'priority' => $task->priority,
                            'status' => $task->status,
                            'assigned_to' => $task->assignedTo ? [
                                'id' => $task->assignedTo->id,
                                'name' => $task->assignedTo->full_name,
                            ] : null,
                            'scheduled_date' => $task->scheduled_date->format('Y-m-d'),
                            'scheduled_time' => $scheduledTime,
                        ];
                    });

                // Get pending maintenance requests for this room
                $maintenanceRequests = MaintenanceRequest::where('room_id', $room->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->with('assignedTo')
                    ->get()
                    ->map(function($request) {
                        return [
                            'id' => $request->id,
                            'title' => $request->title,
                            'priority' => $request->priority ?? 'normal',
                            'status' => $request->status,
                            'assigned_to' => $request->assignedTo ? [
                                'id' => $request->assignedTo->id,
                                'name' => $request->assignedTo->full_name,
                            ] : null,
                            'reported_at' => $request->reported_at?->format('Y-m-d H:i'),
                        ];
                    });

                // Get pending reservations for this room (for check-in)
                $pendingReservations = Reservation::where('room_id', $room->id)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->whereDate('check_in_date', '<=', now())
                    ->with('guest')
                    ->orderBy('check_in_date', 'asc')
                    ->get()
                    ->map(function($reservation) {
                        return [
                            'id' => $reservation->id,
                            'reservation_number' => $reservation->reservation_number,
                            'guest' => $reservation->guest ? [
                                'id' => $reservation->guest->id,
                                'name' => $reservation->guest->full_name,
                            ] : null,
                            'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                            'status' => $reservation->status,
                        ];
                    });

                return [
                    'id' => $room->id,
                    'number' => $room->room_number,
                    'floor' => $floorDisplay,
                    'floor_number' => $floorNumber,
                    'type' => $room->roomType->name ?? 'N/A',
                    'status' => $room->status,
                    'housekeeping_status' => $room->housekeeping_status,
                    // Use reservation's room_rate if available, otherwise fall back to room type base_price
                    'rate' => ($currentReservation?->room_rate ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->room_rate : null)) ?? ($room->roomType->base_price ?? 0),
                    'has_iptv' => $room->iptv_active ?? false,
                    'guest' => $guest ? [
                        'name' => $guest->full_name ?? 'N/A',
                        'id' => $guest->id,
                    ] : null,
                    'check_in' => $currentReservation?->check_in_date?->format('Y-m-d') ?? null,
                    'reservation_id' => $currentReservation?->id ?? null,
                    'housekeeping_tasks' => $housekeepingTasks,
                    'maintenance_requests' => $maintenanceRequests,
                    'pending_reservations' => $pendingReservations,
                ];
            });

        // Get housekeeping staff
        $housekeepers = User::whereHas('roles', function($query) {
            $query->where('name', 'housekeeping');
        })->orWhereHas('roles', function($query) {
            $query->where('name', 'staff');
        })->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                ];
            });

        // Get maintenance staff
        $maintenanceStaff = User::whereHas('roles', function($query) {
            $query->where('name', 'maintenance');
        })->orWhereHas('roles', function($query) {
            $query->where('name', 'staff');
        })->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                ];
            });

        return Inertia::render($viewPath, [
            'user' => $user->load('roles'),
            'rooms' => $rooms,
            'roomStats' => $stats,
            'floors' => $floors,
            'housekeepers' => $housekeepers,
            'maintenanceStaff' => $maintenanceStaff,
        ]);
    }

    public function show(Room $room)
    {
        $room->load([
            'roomType',
            'floor',
            'currentReservation.guest',
            'reservations' => function($query) {
                $query->with(['guest', 'activeKeyCard'])
                    ->where('status', 'checked_in')
                    ->latest('check_in_date');
            }
        ]);

        return Inertia::render('Admin/Rooms/Show', [
            'user' => auth()->user()->load('roles'),
            'room' => [
                'id' => $room->id,
                'number' => $room->room_number,
                'floor' => $room->floor ? ($room->floor->name ?? "Floor {$room->floor->floor_number}") : 'Unknown',
                'type' => $room->roomType->name ?? 'N/A',
                'status' => $room->status,
                'housekeeping_status' => $room->housekeeping_status,
                'rate' => $room->roomType->base_price ?? 0,
                'capacity' => $room->roomType->max_occupancy ?? 0,
                'guest' => $room->currentReservation?->guest?->full_name,
                'check_in' => $room->currentReservation?->check_in_date?->format('Y-m-d H:i'),
                'check_out' => $room->currentReservation?->check_out_date?->format('Y-m-d H:i'),
                'reservation_id' => $room->currentReservation?->id,
                'amenities' => $room->roomType?->amenities ?? [],
                'special_features' => $room->special_features,
                'notes' => $room->notes,
                'last_cleaned' => $room->last_cleaned_at?->diffForHumans(),
            ],
        ]);
    }

    public function edit(Room $room)
    {
        $room->load(['roomType', 'floor', 'buildingWing', 'bedType']);

        // Get all necessary data for the form
        $roomTypes = RoomType::where('is_active', true)->get(['id', 'name', 'code']);
        $amenities = RoomAmenity::where('is_active', true)->get(['id', 'name']);

        // Get floors, building wings, and bed types
        $floors = [];
        $buildingWings = [];
        $bedTypes = [];

        if (Schema::hasColumn('rooms', 'floor_id')) {
            $floors = Floor::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('floor_number')
                ->get(['id', 'floor_number', 'name']);
        }

        if (Schema::hasColumn('rooms', 'building_wing_id')) {
            $buildingWings = \App\Models\BuildingWing::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
        }

        // Always get bed types (they might be used via room type relationship)
        $bedTypes = BedType::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();

        // Get bed_type_id - check both direct column and relationship
        $bedTypeId = null;
        if (Schema::hasColumn('rooms', 'bed_type_id')) {
            $bedTypeId = $room->bed_type_id;
        }
        // If not found, try to get from relationship
        if (!$bedTypeId && $room->relationLoaded('bedType') && $room->bedType) {
            $bedTypeId = $room->bedType->id;
        }

        // Prepare room data - handle both new and old structure
        $roomData = [
            'id' => $room->id,
            'room_number' => $room->room_number,
            'room_type_id' => $room->room_type_id,
            'floor_id' => $room->floor_id ?? null,
            'building_wing_id' => $room->building_wing_id ?? null,
            'bed_type_id' => $bedTypeId,
            'status' => $room->status,
            'housekeeping_status' => $room->housekeeping_status ?? 'clean',
            'iptv_active' => $room->iptv_active ?? false,
            'features' => $room->features ?? [], // This will be used to initialize amenities
            'special_features' => $room->special_features ?? '',
            'notes' => $room->notes ?? '',
        ];

        // Fallback for old floor column if new structure doesn't exist
        if (!$roomData['floor_id'] && isset($room->attributes['floor'])) {
            $floorValue = $room->attributes['floor'];
            if (is_numeric($floorValue) && $floors->count() > 0) {
                // Try to find matching floor
                $matchingFloor = $floors->firstWhere('floor_number', $floorValue);
                if ($matchingFloor) {
                    $roomData['floor_id'] = $matchingFloor->id;
                }
            }
        }

        return Inertia::render('Admin/Rooms/Edit', [
            'user' => auth()->user()->load('roles'),
            'room' => $roomData,
            'roomTypes' => $roomTypes,
            'amenities' => $amenities,
            'floors' => $floors,
            'buildingWings' => $buildingWings,
            'bedTypes' => $bedTypes,
        ]);
    }

    public function create()
    {
        // Get all necessary data for the create form
        $roomTypes = RoomType::where('is_active', true)->get(['id', 'name', 'code']);
        $amenities = RoomAmenity::where('is_active', true)->get(['id', 'name']);

        // Get floors, building wings, and bed types
        $floors = [];
        $buildingWings = [];
        $bedTypes = [];

        if (Schema::hasColumn('rooms', 'floor_id')) {
            $floors = Floor::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('floor_number')
                ->get(['id', 'floor_number', 'name']);
        }

        if (Schema::hasColumn('rooms', 'building_wing_id')) {
            $buildingWings = \App\Models\BuildingWing::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
        }

        // Always get bed types
        $bedTypes = BedType::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Rooms/Create', [
            'user' => auth()->user()->load('roles'),
            'roomTypes' => $roomTypes,
            'amenities' => $amenities,
            'floors' => $floors,
            'buildingWings' => $buildingWings,
            'bedTypes' => $bedTypes,
        ]);
    }

    public function store(Request $request)
    {
        // ── License room limit check ──────────────────────────────────────────
        $limits   = app(LicenseValidationService::class)->getLicenseLimits();
        $maxRooms = $limits['max_rooms']; // -1 = unlimited
        if ($maxRooms !== -1 && Room::count() >= $maxRooms) {
            $msg = "License limit reached: your plan allows a maximum of {$maxRooms} rooms.";
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->withErrors(['room_number' => $msg]);
        }

        $rules = [
            'room_number' => 'required|string|unique:rooms,room_number',
            'room_type_id' => 'required|exists:room_types,id',
            'floor_id' => 'nullable|exists:floors,id',
            'building_wing_id' => 'nullable|exists:building_wings,id',
            'status' => 'required|in:available,occupied,maintenance,cleaning,reserved,out_of_order',
            'housekeeping_status' => 'nullable|in:clean,dirty,inspected,maintenance_required,waiting_for_check',
            'iptv_active' => 'nullable|boolean',
            'amenities' => 'nullable|array',
            'special_features' => 'nullable|string',
            'notes' => 'nullable|string',
        ];

        // Only validate bed_type_id if the column exists
        if (Schema::hasColumn('rooms', 'bed_type_id')) {
            $rules['bed_type_id'] = 'nullable|exists:bed_types,id';
        }

        $validated = $request->validate($rules);

        // Map amenities array to features (store as array of amenity IDs)
        if (isset($validated['amenities']) && is_array($validated['amenities'])) {
            $validated['features'] = $validated['amenities'];
            unset($validated['amenities']);
        }

        // Handle old floor column if new structure doesn't exist
        if (!Schema::hasColumn('rooms', 'floor_id') && Schema::hasColumn('rooms', 'floor')) {
            // If floor_id was provided but column doesn't exist, try to get floor_number
            if (isset($validated['floor_id'])) {
                $floor = Floor::find($validated['floor_id']);
                if ($floor) {
                    $validated['floor'] = $floor->floor_number;
                }
                unset($validated['floor_id']);
            }
        }

        // Remove bed_type_id if column doesn't exist
        if (!Schema::hasColumn('rooms', 'bed_type_id') && isset($validated['bed_type_id'])) {
            unset($validated['bed_type_id']);
        }

        // Create the room
        $room = Room::create($validated);

        // Sync live room count to license server (non-blocking — failure won't abort the request)
        app(LicenseValidationService::class)->syncRooms(Room::count());

        return redirect()->route('admin.rooms.index')->with('success', 'Room created successfully');
    }

    /**
     * Room status page - similar to FrontDesk room status with check-in/check-out functionality
     */
    public function status()
    {
        $rooms = Room::with([
            'roomType',
            'currentReservation.guest',
            'currentReservation.activeKeyCard',
            'floor',
            'reservations' => function($query) {
                $query->where('status', 'checked_in')->with(['guest', 'activeKeyCard'])->latest('check_in_date')->limit(1);
            },
            'pendingReservations' => function($query) {
                $query->whereIn('status', ['confirmed', 'pending'])
                    ->whereDate('check_in_date', '<=', now())
                    ->with('guest')
                    ->latest('check_in_date')
                    ->limit(1);
            }
        ])->get();

        // For occupied rooms, ensure we have the reservation loaded even if currentReservation doesn't match
        foreach ($rooms as $room) {
            // If the model relationship is too strict (e.g. date bounds) but we do have a checked-in reservation,
            // treat it as the current reservation for UI + reconciliation.
            if (!$room->currentReservation && $room->reservations->isNotEmpty()) {
                $room->setRelation('currentReservation', $room->reservations->first());
            }

            if ($room->status === 'occupied') {
                if (!$room->currentReservation && $room->reservations->isEmpty()) {
                    $checkedInReservation = Reservation::where('room_id', $room->id)
                        ->where('status', 'checked_in')
                        ->with(['guest', 'activeKeyCard'])
                        ->latest('check_in_date')
                        ->first();

                    if (!$checkedInReservation) {
                        $checkedInReservation = Reservation::where('room_id', $room->id)
                            ->whereIn('status', ['checked_in', 'confirmed', 'pending'])
                            ->whereNull('actual_check_out')
                            ->with(['guest', 'activeKeyCard'])
                            ->latest('check_in_date')
                            ->first();
                    }

                    if ($checkedInReservation) {
                        $room->setRelation('reservations', collect([$checkedInReservation]));
                        $room->setRelation('currentReservation', $checkedInReservation);
                    }
                }
            }

            // Reconcile: if the room has a checked-in reservation, it must be marked occupied
            // (prevents cases where guests appear checked-in but rooms/status shows no occupied rooms)
            if ($room->currentReservation && $room->status !== 'occupied') {
                $room->status = 'occupied';
                $room->save();
            }
        }

        $effectiveStatus = function($room) {
            return $room->currentReservation ? 'occupied' : $room->status;
        };

        $roomStatus = [
            'available' => $rooms->filter(fn($room) => $effectiveStatus($room) === 'available')->count(),
            'occupied' => $rooms->filter(fn($room) => $effectiveStatus($room) === 'occupied')->count(),
            'cleaning' => $rooms->where('housekeeping_status', 'dirty')->count(),
            'maintenance' => $rooms->filter(fn($room) => $effectiveStatus($room) === 'maintenance')->count(),
        ];

        // Get available key cards for check-in
        $availableKeyCards = \App\Models\KeyCard::available()->get();

        $routeName = request()->route()->getName() ?? '';
        $viewPath = str_starts_with($routeName, 'manager.') ? 'Manager/Reservations/RoomStatus' : 'Admin/Rooms/Status';

        return Inertia::render($viewPath, [
            'user' => auth()->user()->load('roles'),
            'availableKeyCards' => $availableKeyCards->map(fn($card) => [
                'id' => $card->id,
                'card_number' => $card->card_number,
                'card_type' => $card->card_type,
            ]),
            'rooms' => $rooms->map(fn($room) => [
                'id' => $room->id,
                'number' => $room->room_number,
                'floor' => $room->floor ? ($room->floor->name ?? "Floor {$room->floor->floor_number}") : ($room->floor ?? 'Unknown'),
                'floor_number' => $room->floor?->floor_number ?? $room->floor ?? 0,
                'type' => $room->roomType->name ?? 'N/A',
                'status' => $room->currentReservation ? 'occupied' : $room->status,
                'housekeeping_status' => $room->housekeeping_status,
                // Use reservation's room_rate if available, otherwise fall back to room type base_price
                'price' => ($room->currentReservation?->room_rate ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->room_rate : null)) ?? ($room->roomType->base_price ?? 0),
                'capacity' => $room->roomType->max_occupancy ?? 0,
                'guest' => $room->currentReservation?->guest?->full_name ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->full_name : null),
                'guest_phone' => $room->currentReservation?->guest?->phone ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->phone : null),
                'guest_email' => $room->currentReservation?->guest?->email ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->guest?->email : null),
                'check_in' => $room->currentReservation?->check_in_date?->format('Y-m-d H:i') ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->check_in_date?->format('Y-m-d H:i') : null),
                'check_out' => $room->currentReservation?->check_out_date?->format('Y-m-d H:i') ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->check_out_date?->format('Y-m-d H:i') : null),
                'reservation_id' => $room->currentReservation?->id ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->id : null),
                'pending_reservation' => $room->pendingReservations->isNotEmpty() ? [
                    'id' => $room->pendingReservations->first()->id,
                    'reservation_number' => $room->pendingReservations->first()->reservation_number,
                    'guest_name' => $room->pendingReservations->first()->guest?->full_name ?? 'N/A',
                    'check_in_date' => $room->pendingReservations->first()->check_in_date?->format('Y-m-d'),
                    'check_out_date' => $room->pendingReservations->first()->check_out_date?->format('Y-m-d'),
                ] : null,
                'nights' => ($room->currentReservation ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first() : null)) ? (($room->currentReservation ?? $room->reservations->first())->check_in_date->diffInDays(($room->currentReservation ?? $room->reservations->first())->check_out_date)) : 0,
                'total_amount' => $room->currentReservation?->total_amount ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->total_amount ?? 0 : 0),
                'balance' => $room->currentReservation?->balance_amount ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->balance_amount ?? 0 : 0),
                'amenities' => $room->roomType?->amenities ?? [],
                'bed_type' => $room->roomType?->bed_type ?? 'N/A',
                'view_type' => $room->roomType?->view_type ?? 'N/A',
                'last_cleaned' => $room->last_cleaned_at?->diffForHumans(),
                'key_card' => ($room->currentReservation?->activeKeyCard ?? ($room->status === 'occupied' && $room->reservations->isNotEmpty() ? $room->reservations->first()->activeKeyCard : null)) ? [
                    'id' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->id,
                    'card_number' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->card_number,
                    'card_type' => ($room->currentReservation?->activeKeyCard ?? $room->reservations->first()->activeKeyCard)->card_type,
                ] : null,
            ]),
            'roomStatus' => $roomStatus,
        ]);
    }

    /**
     * Manual checkout: mark an occupied room as available when no reservation is linked.
     */
    public function manualCheckout(Request $request, Room $room)
    {
        $hasActiveReservation = $room->currentReservation()->exists();

        if ($room->status !== 'occupied' && !$hasActiveReservation) {
            return back()->with('error', 'Room is not occupied. Manual checkout only applies to occupied rooms.');
        }

        $room->update([
            'status' => 'cleaning',
            'housekeeping_status' => 'dirty',
        ]);

        return back()->with('success', "Room {$room->room_number} has been manually checked out and marked for cleaning.");
    }

    /**
     * Mark room as clean (housekeeping completion)
     */
    public function markClean(Request $request, Room $room)
    {
        $hasActiveReservation = $room->currentReservation()->exists();
        $newStatus = $room->status;

        if ($room->status === 'occupied' || $hasActiveReservation) {
            $newStatus = 'occupied';
        } elseif (
            $room->status === 'cleaning'
            || $room->status === 'available'
            || in_array($room->housekeeping_status, ['dirty', 'waiting_for_check'], true)
        ) {
            $newStatus = 'available';
        }

        $updateData = [
            'housekeeping_status' => 'clean',
            'status' => $newStatus,
        ];

        if (Schema::hasColumn('rooms', 'last_cleaned_at')) {
            $updateData['last_cleaned_at'] = now();
        }

        if (Schema::hasColumn('rooms', 'last_cleaned_by')) {
            $updateData['last_cleaned_by'] = auth()->id();
        }

        $room->update($updateData);

        $statusMessage = $newStatus === 'occupied'
            ? "Room {$room->room_number} has been marked as clean and remains occupied."
            : "Room {$room->room_number} has been marked as clean and is now available.";

        return back()->with('success', $statusMessage);
    }
}
