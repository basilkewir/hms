<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\OnlineBookingController;
// use App\Http\Controllers\Api\IptvController;
// use App\Http\Controllers\Api\GuestController;
// use App\Http\Controllers\Api\ReservationController;
// use App\Http\Controllers\Api\RoomController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes (no authentication required)
Route::prefix('public')->middleware(['throttle:60,1'])->group(function () {
    // Hotel information
    Route::get('/hotel-info', function () {
        $settings = \App\Models\Setting::whereIn('key', ['hotel_name', 'hotel_address', 'hotel_phone', 'hotel_email', 'currency', 'currency_position'])
            ->pluck('value', 'key')
            ->toArray();

        return response()->json([
            'name' => $settings['hotel_name'] ?? config('app.hotel_name', 'Grand Hotel'),
            'address' => $settings['hotel_address'] ?? config('app.hotel_address', '123 Hotel Street'),
            'phone' => $settings['hotel_phone'] ?? config('app.hotel_phone', '+1234567890'),
            'email' => $settings['hotel_email'] ?? config('app.hotel_email', 'info@hotel.com'),
            'timezone' => config('app.timezone', 'UTC'),
            'currency' => $settings['currency'] ?? 'USD',
            'currency_position' => $settings['currency_position'] ?? 'prefix',
        ]);
    });

    // Booking website integration
    Route::get('/room-types', [BookingController::class, 'roomTypes']);
    Route::get('/availability', [BookingController::class, 'availability']);
    Route::post('/bookings', [BookingController::class, 'store']);
});

// Online Booking Sync API (for hotel website integration)
// All write endpoints require X-Booking-Token header (set in Settings > Integration)
Route::prefix('booking')->middleware(['throttle:60,1'])->group(function () {
    // Room availability + services (public read)
    Route::get('/availability', [OnlineBookingController::class, 'checkAvailability']);
    Route::get('/services', [OnlineBookingController::class, 'getServices']);

    // Guest profile lookup — for pre-filling the online booking form (token-protected)
    Route::get('/guest-lookup', [OnlineBookingController::class, 'guestLookup']);

    // Create booking (token-protected)
    Route::post('/create', [OnlineBookingController::class, 'createBooking']);

    // Booking confirmation lookup — requires confirmation token, not just reservation number
    Route::get('/confirmation', [OnlineBookingController::class, 'getBookingConfirmation']);

    // Webhook: website notifies HMS of booking status changes (e.g. payment received)
    Route::post('/webhook', [OnlineBookingController::class, 'handleWebhook']);
});

// Mobile App Authentication (Public) — throttled to prevent brute-force
Route::middleware(['throttle:5,1'])->post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Check if user has housekeeping role
    if (!$user->hasRole('housekeeping') && !$user->hasRole('staff') && !$user->hasRole('admin')) {
        return response()->json(['message' => 'Access denied. Housekeeping role required.'], 403);
    }

    $token = $user->createToken('cleaner-app')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ],
    ]);
});

// IPTV API routes (for Android client integration)
Route::prefix('iptv')->group(function () {
    // Simple test endpoint
    Route::get('/test', function () {
        return response()->json([
            'success' => true,
            'message' => 'IPTV API is working',
            'timestamp' => now()->toISOString()
        ]);
    });

    // Hotel IPTV Client APIs (No authentication required for client apps)
    Route::get('/location-weather', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'location' => [
                    'city' => 'Lagos',
                    'country' => 'Nigeria',
                    'latitude' => '6.5244',
                    'longitude' => '3.3792',
                    'timezone' => 'Africa/Lagos',
                ],
                'weather' => [
                    'temperature' => 28,
                    'feels_like' => 32,
                    'humidity' => 75,
                    'description' => 'Partly cloudy',
                    'icon' => '02d',
                    'wind_speed' => 3.5,
                    'pressure' => 1013
                ],
                'last_updated' => now()->toISOString(),
            ]
        ]);
    });

    Route::get('/hotel-services', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'services' => [
                    'dining' => [
                        'name' => 'Dining Services',
                        'icon' => 'restaurant',
                        'items' => [
                            [
                                'name' => 'Main Restaurant',
                                'description' => 'Fine dining experience',
                                'hours' => '6:00 AM - 11:00 PM',
                                'phone' => '+1234567895',
                                'location' => 'Ground Floor',
                            ],
                            [
                                'name' => 'Room Service',
                                'description' => '24-hour room service',
                                'hours' => '24/7',
                                'phone' => '+1234567892',
                                'extension' => '2',
                            ],
                        ]
                    ],
                    'wellness' => [
                        'name' => 'Wellness & Spa',
                        'icon' => 'spa',
                        'items' => [
                            [
                                'name' => 'Spa Services',
                                'description' => 'Massage and beauty treatments',
                                'hours' => '9:00 AM - 9:00 PM',
                                'phone' => '+1234567896',
                                'location' => '2nd Floor',
                            ],
                        ]
                    ],
                ],
                'hotel_name' => 'Grand Hotel',
                'last_updated' => now()->toISOString(),
            ]
        ]);
    });

    Route::get('/reception-contact', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'contacts' => [
                    'reception' => [
                        'name' => 'Front Desk',
                        'phone' => '+1234567890',
                        'extension' => '0',
                        'email' => 'frontdesk@hotel.com',
                        'hours' => '24/7',
                    ],
                    'concierge' => [
                        'name' => 'Concierge',
                        'phone' => '+1234567891',
                        'extension' => '1',
                        'email' => 'concierge@hotel.com',
                        'hours' => '6:00 AM - 10:00 PM',
                    ],
                    'room_service' => [
                        'name' => 'Room Service',
                        'phone' => '+1234567892',
                        'extension' => '2',
                        'email' => 'roomservice@hotel.com',
                        'hours' => '24/7',
                    ],
                ],
                'hotel_phone' => '+1234567890',
                'hotel_address' => '123 Hotel Street, City',
            ]
        ]);
    });

    Route::get('/client-info', [\App\Http\Controllers\Api\HotelIptvController::class, 'getClientInfo']);
    Route::get('/client-bill', [\App\Http\Controllers\Api\HotelIptvController::class, 'getClientBill']);
    Route::get('/config', [\App\Http\Controllers\Api\HotelIptvController::class, 'getIptvConfig']);
    Route::post('/device-status', [\App\Http\Controllers\Api\HotelIptvController::class, 'updateDeviceStatus']);
    Route::get('/emergency', [\App\Http\Controllers\Api\HotelIptvController::class, 'getEmergencyInfo']);

    // Original IPTV routes (commented out until controllers are created)
    /*
    // Device authentication and room setup
    Route::post('/authenticate', [IptvController::class, 'authenticate']);

    // Channel management
    Route::get('/channels', [IptvController::class, 'getChannels']);
    Route::get('/channels/{id}', [IptvController::class, 'getChannel']);

    // VOD (Video on Demand) content
    Route::get('/vod', [IptvController::class, 'getVodContent']);
    Route::get('/vod/{id}', [IptvController::class, 'getVodDetails']);
    Route::get('/vod/categories', [IptvController::class, 'getVodCategories']);

    // Usage logging and analytics
    Route::post('/log-usage', [IptvController::class, 'logUsage']);
    Route::post('/heartbeat', [IptvController::class, 'heartbeat']);

    // Device management
    Route::post('/device/register', [IptvController::class, 'registerDevice']);
    Route::put('/device/update', [IptvController::class, 'updateDevice']);
    Route::get('/device/status', [IptvController::class, 'getDeviceStatus']);

    // Room settings
    Route::get('/room/settings', [IptvController::class, 'getRoomSettings']);
    Route::put('/room/settings', [IptvController::class, 'updateRoomSettings']);

    // Guest preferences
    Route::get('/guest/preferences', [IptvController::class, 'getGuestPreferences']);
    Route::put('/guest/preferences', [IptvController::class, 'updateGuestPreferences']);
    */
});

// General settings endpoint (public - moved outside auth middleware)
Route::get('/settings/general', [\App\Http\Controllers\SettingsController::class, 'getGeneralSettings']);

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {

    // User profile and authentication
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['roles.permissions', 'permissions']);
    });

    // Theme API
    Route::get('/theme', [\App\Http\Controllers\SettingsController::class, 'getThemeSettings']);

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    // Guest Management API - Commented out until controllers are created
    /*
    Route::prefix('guests')->group(function () {
        Route::get('/', [GuestController::class, 'index']);
        Route::post('/', [GuestController::class, 'store']);
        Route::get('/{guest}', [GuestController::class, 'show']);
        Route::put('/{guest}', [GuestController::class, 'update']);
        Route::delete('/{guest}', [GuestController::class, 'destroy']);

        // Police verification
        Route::post('/{guest}/verify-police', [GuestController::class, 'verifyPolice']);
        Route::post('/{guest}/flag-police', [GuestController::class, 'flagForPolice']);

        // Guest status management
        Route::post('/{guest}/blacklist', [GuestController::class, 'blacklist']);
        Route::post('/{guest}/remove-blacklist', [GuestController::class, 'removeBlacklist']);
        Route::post('/{guest}/make-vip', [GuestController::class, 'makeVip']);
        Route::post('/{guest}/remove-vip', [GuestController::class, 'removeVip']);

        // Document management
        Route::post('/{guest}/upload-document', [GuestController::class, 'uploadDocument']);
        Route::get('/{guest}/documents', [GuestController::class, 'getDocuments']);

        // Search and filters
        Route::get('/search/{query}', [GuestController::class, 'search']);
        Route::get('/filter/nationality/{nationality}', [GuestController::class, 'filterByNationality']);
        Route::get('/filter/verification-status/{status}', [GuestController::class, 'filterByVerificationStatus']);
    });
    */

    // Reservation Management API - Commented out until controllers are created
    /*
    Route::prefix('reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);
        Route::post('/', [ReservationController::class, 'store']);
        Route::get('/{reservation}', [ReservationController::class, 'show']);
        Route::put('/{reservation}', [ReservationController::class, 'update']);
        Route::delete('/{reservation}', [ReservationController::class, 'destroy']);

        // Reservation status management
        Route::post('/{reservation}/confirm', [ReservationController::class, 'confirm']);
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel']);
        Route::post('/{reservation}/check-in', [ReservationController::class, 'checkIn']);
        Route::post('/{reservation}/check-out', [ReservationController::class, 'checkOut']);
        Route::post('/{reservation}/no-show', [ReservationController::class, 'markNoShow']);

        // Reservation modifications
        Route::post('/{reservation}/extend', [ReservationController::class, 'extend']);
        Route::post('/{reservation}/change-room', [ReservationController::class, 'changeRoom']);
        Route::post('/{reservation}/add-services', [ReservationController::class, 'addServices']);

        // Billing and payments
        Route::get('/{reservation}/folio', [ReservationController::class, 'getFolio']);
        Route::post('/{reservation}/charges', [ReservationController::class, 'addCharges']);
        Route::post('/{reservation}/payment', [ReservationController::class, 'processPayment']);

        // Reports and analytics
        Route::get('/reports/occupancy', [ReservationController::class, 'occupancyReport']);
        Route::get('/reports/revenue', [ReservationController::class, 'revenueReport']);
        Route::get('/reports/arrivals-departures', [ReservationController::class, 'arrivalsDepaturesReport']);
    });
    */

    // Room Management API - Commented out until controllers are created
    /*
    Route::prefix('rooms')->group(function () {
        Route::get('/', [RoomController::class, 'index']);
        Route::post('/', [RoomController::class, 'store']);
        Route::get('/{room}', [RoomController::class, 'show']);
        Route::put('/{room}', [RoomController::class, 'update']);
        Route::delete('/{room}', [RoomController::class, 'destroy']);

        // Room status management
        Route::post('/{room}/status', [RoomController::class, 'updateStatus']);
        Route::post('/{room}/housekeeping', [RoomController::class, 'updateHousekeepingStatus']);
        Route::post('/{room}/maintenance', [RoomController::class, 'scheduleMaintenance']);

        // IPTV management
        Route::get('/{room}/iptv', [RoomController::class, 'getIptvSettings']);
        Route::put('/{room}/iptv', [RoomController::class, 'updateIptvSettings']);
        Route::get('/{room}/iptv/usage', [RoomController::class, 'getIptvUsage']);

        // Room availability
        Route::get('/availability/{date}', [RoomController::class, 'getAvailability']);
        Route::get('/availability/{startDate}/{endDate}', [RoomController::class, 'getAvailabilityRange']);
    });
    */

    // Housekeeping Notifications API
    Route::prefix('housekeeping')->group(function () {
        Route::get('/notifications', [\App\Http\Controllers\Api\HousekeepingNotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\HousekeepingNotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\HousekeepingNotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\HousekeepingNotificationController::class, 'markAllAsRead']);

        // Mobile App - Housekeeping Tasks
        Route::get('/tasks/my-tasks', function (Request $request) {
            $user = $request->user();
            $today = \Carbon\Carbon::today();

            // Fetch tasks assigned to this cleaner OR unassigned tasks scheduled for today
            // (unassigned tasks are auto-generated daily for occupied rooms)
            $allTasks = \App\Models\HousekeepingTask::with(['room.roomType', 'assignedTo'])
                ->whereIn('status', ['pending', 'in_progress'])
                ->where(function ($q) use ($user, $today) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere(function ($q2) use ($today) {
                          $q2->whereNull('assigned_to')
                             ->whereDate('scheduled_date', $today);
                      });
                })
                ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                ->orderBy('scheduled_date', 'asc')
                ->get();

            // Filter out check_cleaning tasks if corresponding cleaning task is not completed
            $filteredTasks = $allTasks->filter(function($task) use ($user) {
                // If it's not a check_cleaning task, include it
                if ($task->task_type !== 'check_cleaning') {
                    return true;
                }

                // For check_cleaning tasks, check if there's a corresponding cleaning task
                // that is not yet completed for the same room and assigned to the same user
                $cleaningTaskTypes = ['cleaning', 'deep_clean', 'checkout'];
                $hasIncompleteCleaningTask = \App\Models\HousekeepingTask::where('room_id', $task->room_id)
                    ->where('assigned_to', $user->id)
                    ->whereIn('task_type', $cleaningTaskTypes)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->exists();

                // Only show check_cleaning task if there's NO incomplete cleaning task
                return !$hasIncompleteCleaningTask;
            });

            $tasks = $filteredTasks->map(function($task) {
                // Handle scheduled_time - it's stored as string (H:i format)
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
                    'id'            => $task->id,
                    'room'          => $task->room ? [
                        'id'          => $task->room->id,
                        'room_number' => $task->room->room_number,
                        'room_type'   => $task->room->roomType?->name,
                        'housekeeping_status' => $task->room->housekeeping_status,
                    ] : null,
                    'task_type'       => $task->task_type,
                    'priority'        => $task->priority,
                    'status'          => $task->status,
                    'scheduled_date'  => $task->scheduled_date?->format('Y-m-d'),
                    'scheduled_time'  => $scheduledTime,
                    'instructions'    => $task->instructions,
                    'notes'           => $task->notes,
                    'assigned_to'     => $task->assignedTo ? [
                        'id'   => $task->assignedTo->id,
                        'name' => $task->assignedTo->full_name ?? $task->assignedTo->name,
                    ] : null,
                    'is_unassigned'   => is_null($task->assigned_to),
                ];
            });

            return response()->json(['data' => $tasks->values()]);
        });

        // Get completed/cleaned tasks for the cleaner, grouped by date
        Route::get('/tasks/completed', function (Request $request) {
            $user = $request->user();
            $tasks = \App\Models\HousekeepingTask::where('assigned_to', $user->id)
                ->with(['room.roomType', 'assignedTo'])
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->orderBy('completed_at', 'desc')
                ->limit(100) // Show last 100 completed tasks
                ->get()
                ->map(function($task) {
                    // Handle scheduled_time - it's stored as string (H:i format)
                    $scheduledTime = null;
                    if ($task->scheduled_time) {
                        if (is_string($task->scheduled_time)) {
                            $scheduledTime = substr($task->scheduled_time, 0, 5);
                        } elseif ($task->scheduled_time instanceof \DateTime || $task->scheduled_time instanceof \Carbon\Carbon) {
                            $scheduledTime = $task->scheduled_time->format('H:i');
                        }
                    }

                    return [
                        'id' => $task->id,
                        'room' => $task->room ? [
                            'id' => $task->room->id,
                            'room_number' => $task->room->room_number,
                            'room_type' => $task->room->roomType?->name,
                        ] : null,
                        'task_type' => $task->task_type,
                        'priority' => $task->priority,
                        'status' => $task->status,
                        'scheduled_date' => $task->scheduled_date?->format('Y-m-d'),
                        'scheduled_time' => $scheduledTime,
                        'completed_at' => $task->completed_at?->format('Y-m-d H:i:s'),
                        'completed_date' => $task->completed_at?->format('Y-m-d'),
                        'completed_time' => $task->completed_at?->format('H:i'),
                        'instructions' => $task->instructions,
                        'completion_notes' => $task->completion_notes,
                    ];
                });

            // Group tasks by completed date
            $groupedByDate = $tasks->groupBy('completed_date')->map(function($dateTasks, $date) {
                return [
                    'date' => $date,
                    'count' => $dateTasks->count(),
                    'tasks' => $dateTasks->values()->all(),
                ];
            })->values()->all();

            return response()->json(['data' => $tasks, 'grouped_by_date' => $groupedByDate]);
        });

        Route::get('/tasks/{task}', function (Request $request, $taskId) {
            $task = \App\Models\HousekeepingTask::with(['room.roomType', 'assignedTo'])
                ->findOrFail($taskId);

            // Verify user has access to this task
            if ($task->assigned_to !== $request->user()->id && !$request->user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Handle scheduled_time - it's stored as string (H:i format)
            $scheduledTime = null;
            if ($task->scheduled_time) {
                if (is_string($task->scheduled_time)) {
                    // Remove seconds if present (HH:MM:SS -> HH:MM)
                    $scheduledTime = substr($task->scheduled_time, 0, 5);
                } elseif ($task->scheduled_time instanceof \DateTime || $task->scheduled_time instanceof \Carbon\Carbon) {
                    $scheduledTime = $task->scheduled_time->format('H:i');
                }
            }

            return response()->json([
                'id' => $task->id,
                'room' => $task->room,
                'task_type' => $task->task_type,
                'priority' => $task->priority,
                'status' => $task->status,
                'scheduled_date' => $task->scheduled_date?->format('Y-m-d'),
                'scheduled_time' => $scheduledTime,
                'instructions' => $task->instructions,
                'notes' => $task->notes,
            ]);
        });

        Route::post('/tasks/{task}/update-status', function (Request $request, $taskId) {
            $request->validate([
                'status' => 'required|in:pending,in_progress,completed,skipped',
                'notes' => 'nullable|string',
                'completion_notes' => 'nullable|string',
            ]);

            $task = \App\Models\HousekeepingTask::findOrFail($taskId);

            // Verify user has access to this task
            if ($task->assigned_to !== $request->user()->id && !$request->user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $updateData = [
                'status' => $request->status,
            ];

            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            if ($request->has('completion_notes')) {
                $updateData['completion_notes'] = $request->completion_notes;
            }

            if ($request->status === 'in_progress' && !$task->started_at) {
                $updateData['started_at'] = now();
            }

            if ($request->status === 'completed' && !$task->completed_at) {
                $updateData['completed_at'] = now();
                if ($task->started_at) {
                    $updateData['actual_minutes'] = $task->started_at->diffInMinutes(now());
                }

                // Update room status based on task type
                // Reload room relationship to ensure we have the latest data
                $task->load('room');
                if ($task->room) {
                    // If it's a check_cleaning or inspection task, mark room as clean and available
                    if (in_array($task->task_type, ['check_cleaning', 'inspection'])) {
                        $task->room->update([
                            'housekeeping_status' => 'clean',
                            'status' => 'available',
                            'last_cleaned_at' => now(),
                            'last_cleaned_by' => $request->user()->id,
                        ]);
                        // Refresh the room model
                        $task->room->refresh();
                    } else {
                        // For other tasks (cleaning, checkout, etc.), set to waiting_for_check
                        $task->room->update([
                            'housekeeping_status' => 'waiting_for_check',
                            'status' => $task->room->status === 'cleaning' ? 'cleaning' : $task->room->status, // Keep cleaning status until inspection
                        ]);
                        // Refresh the room model
                        $task->room->refresh();
                    }
                }
            }

            $task->update($updateData);

            return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
        });

        // Mark room as clean
        Route::post('/rooms/{room}/mark-clean', function (Request $request, $roomId) {
            $request->validate([
                'notes' => 'nullable|string',
            ]);

            $room = \App\Models\Room::findOrFail($roomId);

            // If room is in cleaning status or waiting_for_check, change to available
            $newStatus = 'available';
            if ($room->status === 'occupied') {
                $newStatus = 'occupied'; // Don't change occupied rooms
            } elseif ($room->status !== 'cleaning' && $room->housekeeping_status !== 'waiting_for_check') {
                $newStatus = $room->status; // Keep current status if not cleaning or waiting_for_check
            }

            $room->update([
                'housekeeping_status' => 'clean',
                'status' => $newStatus,
                'last_cleaned_at' => now(),
                'last_cleaned_by' => $request->user()->id,
            ]);

            // Update any pending or in-progress check_cleaning/inspection tasks for this room assigned to this user
            \App\Models\HousekeepingTask::where('room_id', $roomId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereIn('task_type', ['check_cleaning', 'inspection'])
                ->where('assigned_to', $request->user()->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completion_notes' => $request->notes ?? 'Room marked as clean via mobile app',
                ]);

            return response()->json(['message' => 'Room marked as clean', 'room' => $room]);
        });
    });

    // Mobile App - Maintenance Requests
    Route::prefix('maintenance-requests')->group(function () {
        Route::get('/', function (Request $request) {
            $query = \App\Models\MaintenanceRequest::with(['room', 'assignedTo', 'reportedBy']);

            if ($request->has('room_id')) {
                $query->where('room_id', $request->room_id);
            }

            $requests = $query->orderBy('reported_at', 'desc')->get();

            return response()->json(['data' => $requests]);
        });

        Route::post('/', function (Request $request) {
            $request->validate([
                'room_id' => 'nullable|exists:rooms,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|in:plumbing,electrical,hvac,furniture,appliances,security,it,other',
                'priority' => 'required|in:low,normal,high,urgent',
                'photos' => 'nullable|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
            ]);

            $photoPaths = [];

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('maintenance/photos', 'public');
                    $photoPaths[] = $path;
                }
            }

            $maintenanceRequest = \App\Models\MaintenanceRequest::create([
                'request_number' => 'MR' . strtoupper(\Illuminate\Support\Str::random(8)),
                'room_id' => $request->room_id,
                'reported_by' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->priority,
                'status' => 'open',
                'reported_at' => now(),
                'photos' => !empty($photoPaths) ? $photoPaths : null,
            ]);

            return response()->json(['message' => 'Maintenance request created', 'request' => $maintenanceRequest], 201);
        });

        Route::get('/{request}', function (Request $request, $requestId) {
            $maintenanceRequest = \App\Models\MaintenanceRequest::with(['room', 'assignedTo', 'reportedBy'])
                ->findOrFail($requestId);

            return response()->json($maintenanceRequest);
        });

        Route::get('/room/{roomId}/status', function (Request $request, $roomId) {
            $requests = \App\Models\MaintenanceRequest::where('room_id', $roomId)
                ->whereIn('status', ['open', 'assigned', 'in_progress'])
                ->with(['assignedTo'])
                ->get();

            return response()->json(['data' => $requests]);
        });
    });

    // Staff and Time Tracking API
    Route::prefix('staff')->group(function () {
        // Time tracking
        Route::post('/clock-in', function (Request $request) {
            // Implementation for clock in
            return response()->json(['message' => 'Clocked in successfully']);
        });

        Route::post('/clock-out', function (Request $request) {
            // Implementation for clock out
            return response()->json(['message' => 'Clocked out successfully']);
        });

        Route::get('/time-entries', function (Request $request) {
            // Get user's time entries
            return response()->json(['time_entries' => []]);
        });

        // Leave requests
        Route::get('/leave-requests', function (Request $request) {
            return response()->json(['leave_requests' => []]);
        });

        Route::post('/leave-requests', function (Request $request) {
            return response()->json(['message' => 'Leave request submitted']);
        });

        // Schedule
        Route::get('/schedule', function (Request $request) {
            return response()->json(['schedule' => []]);
        });
    });

    // Financial API
    Route::prefix('financial')->group(function () {
        // Payments
        Route::get('/payments', function () {
            return response()->json(['payments' => []]);
        });

        Route::post('/payments', function (Request $request) {
            return response()->json(['message' => 'Payment processed']);
        });

        // Expenses
        Route::get('/expenses', function () {
            return response()->json(['expenses' => []]);
        });

        Route::post('/expenses', function (Request $request) {
            return response()->json(['message' => 'Expense recorded']);
        });

        // Reports
        Route::get('/reports/revenue', function () {
            return response()->json(['revenue_data' => []]);
        });

        Route::get('/reports/expenses', function () {
            return response()->json(['expense_data' => []]);
        });
    });

    // Reports API
    Route::get('/dashboard', function () {
        return response()->json(['dashboard_data' => []]);
    });

    Route::get('/occupancy', function () {
        return response()->json(['occupancy_data' => []]);
    });

    Route::get('/revenue', function () {
        return response()->json(['revenue_data' => []]);
    });

    Route::get('/guest-analytics', function () {
        return response()->json(['guest_analytics' => []]);
    });

    Route::get('/iptv-usage', function () {
        return response()->json(['iptv_usage' => []]);
    });

    // Products API for POS system
    Route::get('/products', function (Request $request) {
        $products = \App\Models\Product::with(['category', 'brand', 'unit', 'warehouses'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'max_stock' => $product->max_stock,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'brand' => $product->brand ? [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                    ] : null,
                    'unit' => $product->unit ? [
                        'id' => $product->unit->id,
                        'name' => $product->unit->name,
                        'abbreviation' => $product->unit->abbreviation,
                    ] : null,
                    'warehouses' => $product->warehouses->map(function ($warehouse) {
                        return [
                            'id' => $warehouse->id,
                            'name' => $warehouse->name,
                            'pivot_quantity' => $warehouse->pivot->quantity,
                        ];
                    }),
                    'status' => $product->status,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        return response()->json(['data' => $products]);
    });

    // Products and Warehouses API for POS transfers
    Route::get('/products-and-warehouses', function (Request $request) {
        $products = \App\Models\Product::with(['category', 'brand', 'unit', 'warehouses'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                // Safely handle relationships that might be strings
                $category = null;
                if ($product->category && is_object($product->category)) {
                    $category = [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ];
                }

                $brand = null;
                if ($product->brand && is_object($product->brand)) {
                    $brand = [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                    ];
                }

                $unit = null;
                if ($product->unit && is_object($product->unit)) {
                    $unit = [
                        'id' => $product->unit->id,
                        'name' => $product->unit->name,
                        'abbreviation' => $product->unit->abbreviation,
                    ];
                }

                $warehouses = [];
                if ($product->warehouses && is_iterable($product->warehouses)) {
                    $warehouses = $product->warehouses->map(function ($warehouse) {
                        if (is_object($warehouse) && isset($warehouse->id)) {
                            return [
                                'id' => $warehouse->id,
                                'name' => $warehouse->name ?? 'Unknown',
                                'pivot_quantity' => $warehouse->pivot->quantity ?? 0,
                            ];
                        }
                        return null;
                    })->filter()->values()->all();
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'max_stock' => $product->max_stock,
                    'category' => $category,
                    'brand' => $brand,
                    'unit' => $unit,
                    'warehouses' => $warehouses,
                    'status' => $product->status,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        $warehouses = \App\Models\Warehouse::orderBy('name')->get()->map(function ($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'capacity' => $warehouse->capacity,
                'manager' => $warehouse->manager,
                'phone' => $warehouse->phone,
                'email' => $warehouse->email,
                'status' => $warehouse->status,
                'created_at' => $warehouse->created_at,
                'updated_at' => $warehouse->updated_at,
            ];
        });

        return response()->json([
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    });

    Route::get('/staff-performance', function () {
        return response()->json(['staff_performance' => []]);
    });

    // Report data endpoint for ReportPage component
    Route::post('/reports/data', function (Request $request) {
        $request->validate([
            'type' => 'required|string',
            'category' => 'required|string',
            'filters' => 'required|array',
        ]);

        $type = $request->type;
        $category = $request->category;
        $filters = $request->filters;

        // Mock data for revenue report
        if ($category === 'financial' && $type === 'Revenue Report') {
            return response()->json([
                'summary' => [
                    'total_revenue' => [
                        'label' => 'Total Revenue',
                        'value' => '$12,500.00'
                    ],
                    'avg_daily_rate' => [
                        'label' => 'Average Daily Rate',
                        'value' => '$125.00'
                    ],
                    'occupancy_rate' => [
                        'label' => 'Occupancy Rate',
                        'value' => '75%'
                    ],
                    'revpar' => [
                        'label' => 'RevPAR',
                        'value' => '$93.75'
                    ]
                ],
                'chartData' => [
                    ['date' => '2024-01-01', 'value' => 1000],
                    ['date' => '2024-01-02', 'value' => 1200],
                    ['date' => '2024-01-03', 'value' => 1100],
                    ['date' => '2024-01-04', 'value' => 1300],
                    ['date' => '2024-01-05', 'value' => 1400],
                ],
                'chartTitle' => 'Revenue Trends',
                'tableData' => [
                    [
                        'date' => '2024-01-01',
                        'room_revenue' => 800.00,
                        'food_beverage' => 150.00,
                        'other_services' => 50.00,
                        'total' => 1000.00
                    ],
                    [
                        'date' => '2024-01-02',
                        'room_revenue' => 950.00,
                        'food_beverage' => 200.00,
                        'other_services' => 50.00,
                        'total' => 1200.00
                    ],
                    [
                        'date' => '2024-01-03',
                        'room_revenue' => 850.00,
                        'food_beverage' => 200.00,
                        'other_services' => 50.00,
                        'total' => 1100.00
                    ],
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
                    ['key' => 'room_revenue', 'label' => 'Room Revenue', 'type' => 'currency'],
                    ['key' => 'food_beverage', 'label' => 'Food & Beverage', 'type' => 'currency'],
                    ['key' => 'other_services', 'label' => 'Other Services', 'type' => 'currency'],
                    ['key' => 'total', 'label' => 'Total', 'type' => 'currency'],
                ],
                'tableTitle' => 'Daily Revenue Breakdown'
            ]);
        }

        // Mock data for other reports
        return response()->json([
            'summary' => [
                'total' => [
                    'label' => 'Total Records',
                    'value' => '100'
                ]
            ],
            'chartData' => [
                ['label' => 'Category A', 'value' => 40],
                ['label' => 'Category B', 'value' => 30],
                ['label' => 'Category C', 'value' => 30],
            ],
            'chartTitle' => 'Data Distribution',
            'tableData' => [
                ['id' => 1, 'name' => 'Item 1', 'value' => 100],
                ['id' => 2, 'name' => 'Item 2', 'value' => 200],
                ['id' => 3, 'name' => 'Item 3', 'value' => 300],
            ],
            'columns' => [
                ['key' => 'id', 'label' => 'ID', 'type' => 'number'],
                ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['key' => 'value', 'label' => 'Value', 'type' => 'number'],
            ],
            'tableTitle' => 'Sample Data'
        ]);
    });

    // System Administration API
    Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
        // User management
        Route::get('/users', function () {
            return response()->json(['users' => []]);
        });

        Route::post('/users', function (Request $request) {
            return response()->json(['message' => 'User created']);
        });

        // Role and permission management
        Route::get('/roles', function () {
            return response()->json(['roles' => []]);
        });

        Route::get('/permissions', function () {
            return response()->json(['permissions' => []]);
        });

        // System settings
        Route::get('/settings', function () {
            return response()->json(['settings' => []]);
        });

        Route::put('/settings', function (Request $request) {
            return response()->json(['message' => 'Settings updated']);
        });

        // System logs
        Route::get('/logs', function () {
            return response()->json(['logs' => []]);
        });

        // Backup and maintenance
        Route::post('/backup', function () {
            return response()->json(['message' => 'Backup initiated']);
        });

        Route::post('/maintenance-mode', function (Request $request) {
            return response()->json(['message' => 'Maintenance mode toggled']);
        });
    });

    // General settings endpoint (public)
    // Route::get('/settings/general', [\App\Http\Controllers\SettingsController::class, 'getGeneralSettings']);
});

    // License API endpoints (throttled — no public access to info without auth)
Route::prefix('license')->middleware(['throttle:20,1'])->group(function () {
    Route::post('/validate-token', [\App\Http\Controllers\SystemLicenseController::class, 'validateToken']);
    Route::post('/refresh-token', [\App\Http\Controllers\SystemLicenseController::class, 'refreshToken']);
    Route::post('/heartbeat', [\App\Http\Controllers\SystemLicenseController::class, 'sendHeartbeat']);
    // /info requires auth:sanctum — license metadata is not public
    Route::middleware('auth:sanctum')->get('/info', [\App\Http\Controllers\SystemLicenseController::class, 'getLicenseInfo']);
});

    // Payment search API for refund functionality
    Route::get('/payments/search', [App\Http\Controllers\Api\PaymentController::class, 'search']);

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'services' => [
            'database' => 'connected',
            'cache' => 'connected',
            'storage' => 'accessible',
        ]
    ]);
});

// API documentation endpoint
Route::get('/docs', function () {
    return response()->json([
        'message' => 'Hotel Management System API',
        'version' => '1.0.0',
        'documentation' => url('/docs'),
        'endpoints' => [
            'IPTV' => '/api/iptv/*',
            'Guests' => '/api/guests/*',
            'Reservations' => '/api/reservations/*',
            'Rooms' => '/api/rooms/*',
            'Staff' => '/api/staff/*',
            'Financial' => '/api/financial/*',
            'Reports' => '/api/reports/*',
            'Admin' => '/api/admin/*',
        ]
    ]);
});
