<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\GroupBookingController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\FrontDesk\DashboardController as FrontDeskDashboardController;
use App\Http\Controllers\Accountant\DashboardController as AccountantDashboardController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Admin\WorkShiftController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Welcome page - redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard routes by role
Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('front_desk')) {
        return redirect()->route('front-desk.dashboard');
    } elseif ($user->hasRole('accountant')) {
        return redirect()->route('accountant.dashboard');
    } elseif ($user->hasRole('manager')) {
        return redirect()->route('manager.dashboard');
    } elseif ($user->hasRole('housekeeping')) {
        return redirect()->route('housekeeping.dashboard');
    }

    return redirect('/dashboard');
})->name('dashboard');

// Admin Dashboard
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Reservations
    Route::resource('reservations', ReservationController::class);

    // Reservation Service Charges
    Route::get('/reservations/service-charges', function () {
        return Inertia::render('Admin/ServiceCharges/Index');
    })->name('reservations.service-charges');

    // Waitlist
    Route::resource('waitlist', \App\Http\Controllers\Admin\WaitlistController::class);

    Route::get('/waitlist/check-availability', function () {
        return Inertia::render('Admin/Waitlist/Availability');
    })->name('waitlist.check-availability');

    // Channel Manager
    Route::resource('channel-manager', \App\Http\Controllers\Admin\ChannelManagerController::class);

    // Guests
    Route::get('/guests', function () {
        return Inertia::render('Admin/Guests/Index');
    })->name('guests.index');

    Route::get('/guests/create', function () {
        return Inertia::render('Admin/Guests/Create');
    })->name('guests.create');

    // Check-ins and Check-outs
    Route::get('/checkin', function () {
        return Inertia::render('Admin/CheckIn');
    })->name('checkin');

    Route::get('/checkout', function () {
        return Inertia::render('Admin/CheckOut');
    })->name('checkout');

    // Room Status (must come before resource routes)
    Route::get('/rooms/status', [\App\Http\Controllers\Admin\RoomController::class, 'status'])->name('rooms.status');

    // Rooms
    Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class);

    // Room Types
    Route::resource('room-types', \App\Http\Controllers\Admin\RoomTypeController::class);

    // Floors
    Route::resource('floors', \App\Http\Controllers\Admin\FloorController::class);

    // Building Wings
    Route::resource('building-wings', \App\Http\Controllers\Admin\BuildingWingController::class);

    // Bed Types
    Route::resource('bed-types', \App\Http\Controllers\Admin\BedTypeController::class);

    // Housekeeping
    Route::get('/housekeeping', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'index'])->name('housekeeping');

    Route::get('/housekeeping-tasks', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'index'])->name('housekeeping-tasks.index');
    Route::get('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'show'])->name('housekeeping-tasks.show');
    Route::get('/housekeeping-tasks/{housekeepingTask}/edit', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'edit'])->name('housekeeping-tasks.edit');

    // Maintenance
    Route::get('/maintenance', function () {
        return Inertia::render('Admin/Maintenance/Index');
    })->name('maintenance');

    Route::get('/maintenance-requests', function () {
        return Inertia::render('Admin/Maintenance/Requests/Index');
    })->name('maintenance-requests.index');

    // Devices/IPTV
    Route::get('/devices', function () {
        return Inertia::render('Admin/Devices/Index');
    })->name('devices.index');

    // Preventive Maintenance
    Route::get('/maintenance/preventive/scheduled', function () {
        return Inertia::render('Admin/Maintenance/Preventive/Scheduled');
    })->name('maintenance.preventive.scheduled');

    // Time Tracking
    Route::get('/time-tracking', function () {
        return Inertia::render('Admin/TimeTracking/Index');
    })->name('time-tracking.index');

    // Services
    Route::get('/services', function () {
        return Inertia::render('Admin/Services/Index');
    })->name('services.index');

    // Employee Management
    Route::get('/work-shifts', [WorkShiftController::class, 'index'])->name('work-shifts.index');
    Route::post('/work-shifts', [WorkShiftController::class, 'store'])->name('work-shifts.store');
    Route::put('/work-shifts/{workShift}', [WorkShiftController::class, 'update'])->name('work-shifts.update');
    Route::delete('/work-shifts/{workShift}', [WorkShiftController::class, 'destroy'])->name('work-shifts.destroy');

    // Shift Assignment Routes
    Route::post('/work-shifts/assign', [WorkShiftController::class, 'assignShift'])->name('work-shifts.assign');
    Route::put('/work-shifts/assignments/{employeeShift}', [WorkShiftController::class, 'updateAssignment'])->name('work-shifts.assignments.update');
    Route::delete('/work-shifts/assignments/{employeeShift}', [WorkShiftController::class, 'cancelAssignment'])->name('work-shifts.assignments.destroy');

    Route::get('/schedules', [\App\Http\Controllers\Admin\ScheduleController::class, 'index'])->name('schedules.index');

    // Schedule CRUD Routes
    Route::post('/schedules', [\App\Http\Controllers\Admin\ScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [\App\Http\Controllers\Admin\ScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Admin\ScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Schedule Management Routes
    Route::get('/schedules/print', [\App\Http\Controllers\Admin\ScheduleController::class, 'print'])->name('schedules.print');
    Route::get('/schedules/export', [\App\Http\Controllers\Admin\ScheduleController::class, 'export'])->name('schedules.export');
    Route::post('/schedules/generate', [\App\Http\Controllers\Admin\ScheduleController::class, 'generate'])->name('schedules.generate');

    // Schedule Request Routes
    Route::post('/schedules/requests/approve/{request}', [\App\Http\Controllers\Admin\ScheduleController::class, 'approveRequest'])->name('schedules.requests.approve');
    Route::post('/schedules/requests/reject/{request}', [\App\Http\Controllers\Admin\ScheduleController::class, 'rejectRequest'])->name('schedules.requests.reject');

    Route::get('/housekeeping/schedules', function () {
    // Get mock data (base schedules)
    $mockSchedules = [
        [
            'id' => 1,
            'assigned_to' => [
                'id' => 1,
                'name' => 'John Doe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'employee_id' => 'EMP001'
            ],
            'room_numbers' => ['101', '102'],
            'room_count' => 2,
            'start_date' => '2026-02-24',
            'end_date' => '2026-02-24',
            'preferred_start_time' => '09:00',
            'status' => 'pending',
            'notes' => 'Regular cleaning',
            'created_at' => '2026-02-24T10:00:00Z',
            'updated_at' => '2026-02-24T10:00:00Z'
        ],
        [
            'id' => 2,
            'assigned_to' => [
                'id' => 2,
                'name' => 'Jane Smith',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'employee_id' => 'EMP002'
            ],
            'room_numbers' => ['201', '202', '203'],
            'room_count' => 3,
            'start_date' => '2026-02-25',
            'end_date' => '2026-02-25',
            'preferred_start_time' => '14:00',
            'status' => 'in_progress',
            'notes' => 'Deep cleaning required',
            'created_at' => '2026-02-24T11:00:00Z',
            'updated_at' => '2026-02-24T11:00:00Z'
        ]
    ];

    // Get user-created schedules from session
    $userSchedules = session('housekeeping_schedules', []);

    // Merge mock data with user-created schedules
    $allSchedules = array_merge($mockSchedules, $userSchedules);

    // Sort by creation date (newest first)
    usort($allSchedules, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    return Inertia::render('Admin/Housekeeping/Schedules/Index', [
        'schedules' => [
            'data' => $allSchedules,
            'links' => [],
            'from' => 1,
            'to' => count($allSchedules),
            'total' => count($allSchedules),
            'prev_page_url' => null,
            'next_page_url' => null
        ]
    ]);
})->name('housekeeping.schedules.index');

    // Housekeeping Schedule CRUD Routes
    Route::get('/housekeeping/schedules/create', function () {
        // Get housekeeping staff users
        $housekeepers = \App\Models\User::where('is_active', true)
            ->where('employment_status', 'active')
            ->where(function($query) {
                $query->where('department', 'housekeeping')
                      ->orWhere('position', 'like', '%housekeeping%')
                      ->orWhereHas('roles', function($q) {
                          $q->where('name', 'like', '%housekeeping%');
                      });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_id']);

        // Get all rooms
        $rooms = \App\Models\Room::orderBy('room_number')
            ->get(['id', 'room_number', 'room_type_id', 'status', 'is_active']);

        return Inertia::render('Admin/Housekeeping/Schedules/Create', [
            'housekeepers' => $housekeepers,
            'rooms' => $rooms,
        ]);
    })->name('housekeeping.schedules.create');

    Route::post('/housekeeping/schedules', function (\Illuminate\Http\Request $request) {
        // Validate the form data
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'room_numbers' => 'required|array|min:1',
            'room_numbers.*' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'preferred_start_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get the assigned user details
        $assignedUser = \App\Models\User::find($validated['assigned_to']);

        // Create the schedule data structure
        $scheduleData = [
            'id' => time(), // Use timestamp as temporary ID
            'assigned_to' => [
                'id' => $assignedUser->id,
                'name' => $assignedUser->first_name . ' ' . $assignedUser->last_name,
                'first_name' => $assignedUser->first_name,
                'last_name' => $assignedUser->last_name,
                'employee_id' => $assignedUser->employee_id
            ],
            'room_numbers' => $validated['room_numbers'],
            'room_count' => count($validated['room_numbers']),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'preferred_start_time' => $validated['preferred_start_time'] ?? null,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];

        // Store in session (temporary solution)
        $existingSchedules = session('housekeeping_schedules', []);
        $existingSchedules[] = $scheduleData;
        session(['housekeeping_schedules' => $existingSchedules]);

        // Log the data for debugging
        \Log::info('Housekeeping schedule created:', $scheduleData);

        return redirect()->route('admin.housekeeping.schedules.index')
            ->with('success', 'Housekeeping schedule created successfully!');
    })->name('housekeeping.schedules.store');

    Route::get('/housekeeping/schedules/{id}', function ($id) {
        // Get mock data (base schedules)
        $mockSchedules = [
            [
                'id' => 1,
                'assigned_to' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'employee_id' => 'EMP001'
                ],
                'room_numbers' => ['101', '102'],
                'room_count' => 2,
                'start_date' => '2026-02-24',
                'end_date' => '2026-02-24',
                'preferred_start_time' => '09:00',
                'status' => 'pending',
                'notes' => 'Regular cleaning',
                'created_at' => '2026-02-24T10:00:00Z',
                'updated_at' => '2026-02-24T10:00:00Z'
            ],
            [
                'id' => 2,
                'assigned_to' => [
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'employee_id' => 'EMP002'
                ],
                'room_numbers' => ['201', '202', '203'],
                'room_count' => 3,
                'start_date' => '2026-02-25',
                'end_date' => '2026-02-25',
                'preferred_start_time' => '14:00',
                'status' => 'in_progress',
                'notes' => 'Deep cleaning required',
                'created_at' => '2026-02-24T11:00:00Z',
                'updated_at' => '2026-02-24T11:00:00Z'
            ]
        ];

        // Get user-created schedules from session
        $userSchedules = session('housekeeping_schedules', []);

        // Merge mock data with user-created schedules
        $allSchedules = array_merge($mockSchedules, $userSchedules);

        // Find the specific schedule
        $schedule = collect($allSchedules)->firstWhere('id', $id);

        if (!$schedule) {
            abort(404, 'Schedule not found');
        }

        return Inertia::render('Admin/Housekeeping/Schedules/Show', [
            'schedule' => $schedule
        ]);
    })->name('housekeeping.schedules.show');

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // Users Export Routes
    Route::get('/users/export/{format}', function (\Illuminate\Http\Request $request, $format) {
        try {
            // Get all users with their relationships
            $users = \App\Models\User::with(['roles', 'department', 'position'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform user data for export
            $exportData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'email' => $user->email ?? '',
                    'phone' => $user->phone ?? '',
                    'employee_id' => $user->employee_id ?? '',
                    'department' => $user->department ? ucwords(str_replace('_', ' ', $user->department)) : '',
                    'position' => $user->position ? ucwords(str_replace('_', ' ', $user->position)) : '',
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'role' => $user->roles->first()?->name ?? '',
                    'hire_date' => $user->hire_date ?? '',
                    'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '',
                ];
            });

            $filename = 'users_export_' . date('Y-m-d');

            switch (strtolower($format)) {
                case 'csv':
                    return response()->streamDownload(function () use ($exportData) {
                        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

                        // Output CSV headers
                        $output = fopen('php://output', 'w');
                        fputcsv($output, $headers);

                        // Output data rows
                        foreach ($exportData as $row) {
                            fputcsv($output, array_values($row));
                        }

                        fclose($output);
                    }, $filename . '.csv', [
                        'Content-Type' => 'text/csv',
                        'Cache-Control' => 'no-store, no-cache',
                    ]);

                case 'excel':
                    // Generate Excel-compatible CSV with proper formatting
                    return response()->streamDownload(function () use ($exportData) {
                        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

                        // Add BOM for proper Excel UTF-8 support
                        echo "\xEF\xBB\xBF";

                        // Output CSV headers
                        $output = fopen('php://output', 'w');
                        fputcsv($output, $headers);

                        // Output data rows
                        foreach ($exportData as $row) {
                            fputcsv($output, array_values($row));
                        }

                        fclose($output);
                    }, $filename . '.csv', [
                        'Content-Type' => 'text/csv',
                        'Cache-Control' => 'no-store, no-cache',
                    ]);

                case 'pdf':
                    // Generate simple text file for PDF printing
                    $textContent = generateSimpleTextExport($exportData, $filename);
                    return response($textContent)
                        ->header('Content-Type', 'text/plain')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '.txt"');

                case 'word':
                    // Generate tab-separated text file for Word
                    $tabText = generateTabTextExport($exportData, $filename);
                    return response($tabText)
                        ->header('Content-Type', 'text/plain')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '.txt"');

                default:
                    return response()->json(['error' => 'Unsupported export format'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export users'], 500);
        }
    })->name('users.export');

    // Helper functions for export formats
    function generateExcelExport($data, $filename) {
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<html xmlns="http://www.w3.org/TR/REC-html401/1998/HTML401/transitional.dtd">';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<meta name="Generator" content="Laravel Excel Export">';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= 'td { mso-number-format:"\@"; } ';
        $html .= '.number { mso-number-format:"\@"; } ';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<h2>' . $filename . '</h2>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . $filename . '</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 40px; }';
        $html .= 'table { border-collapse: collapse; width: 100%; margin: 20px 0; }';
        $html .= 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #e0e0e0; font-weight: bold; }';
        $html .= 'td { padding: 8px; }';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }

    function generatePDFExport($data, $filename) {
        // Generate PDF-optimized HTML document
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= '@page { margin: 2cm; }';
        $html .= 'body { font-family: Arial, sans-serif; margin: 40px; font-size: 12px; }';
        $html .= 'h1 { color: #333; text-align: center; margin-bottom: 30px; }';
        $html .= 'table { border-collapse: collapse; width: 100%; margin: 20px 0; page-break-inside: avoid; }';
        $html .= 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #f0f0f0; font-weight: bold; }';
        $html .= 'td { padding: 8px; }';
        $html .= '.center { text-align: center; }';
        $html .= '.right { text-align: right; }';
        $html .= '.date { white-space: nowrap; }';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . htmlspecialchars($filename) . '</h1>';
        $html .= '<div class="center">Generated on ' . date('Y-m-d H:i:s') . '</div>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<div class="center"><small>Page 1 of 1</small></div>';
        $html .= '</body>';
        $html .= '</html>';
        return $html;
    }

    function generateWordExport($data, $filename) {
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<html xmlns="http://www.w3.org/TR/REC-html401/1998/HTML401/transitional.dtd">';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 40px; }';
        $html .= 'table { border-collapse: collapse; width: 100%; margin: 20px 0; }';
        $html .= 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #e0e0e0; font-weight: bold; }';
        $html .= 'td { padding: 8px; }';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . htmlspecialchars($filename) . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }

    function generateSimpleTextExport($data, $filename) {
        // Generate simple text file for easy viewing and printing
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $content = '';
        $content .= str_repeat('=', 80) . "\n";
        $content .= strtoupper($filename) . "\n";
        $content .= str_repeat('=', 80) . "\n";
        $content .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat('-', 80) . "\n\n";

        // Create formatted table
        $content .= sprintf("%-6s %-15s %-15s %-25s %-15s %-12s %-15s %-15s %-8s %-15s %-12s %-20s\n",
            'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Emp ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'
        );
        $content .= str_repeat('-', 80) . "\n";

        foreach ($data as $row) {
            $content .= sprintf("%-6s %-15s %-15s %-25s %-15s %-12s %-15s %-15s %-8s %-15s %-12s %-20s\n",
                substr($row['id'], 0, 6),
                substr($row['first_name'], 0, 15),
                substr($row['last_name'], 0, 15),
                substr($row['email'], 0, 25),
                substr($row['phone'], 0, 15),
                substr($row['employee_id'], 0, 12),
                substr($row['department'], 0, 15),
                substr($row['position'], 0, 15),
                substr($row['status'], 0, 8),
                substr($row['role'], 0, 15),
                substr($row['hire_date'], 0, 12),
                substr($row['created_at'], 0, 20)
            );
        }

        $content .= "\n" . str_repeat('=', 80) . "\n";
        $content .= "Total Records: " . count($data) . "\n";
        $content .= str_repeat('=', 80) . "\n";

        return $content;
    }

    function generateTabTextExport($data, $filename) {
        // Generate tab-separated text file for Word import
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $content = '';
        $content .= $filename . "\n";
        $content .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        // Header row
        $content .= implode("\t", $headers) . "\n";

        // Data rows
        foreach ($data as $row) {
            $content .= implode("\t", array_values($row)) . "\n";
        }

        $content .= "\nTotal Records: " . count($data) . "\n";

        return $content;
    }

    // Guest Types
Route::get('/guest-types', function () {
    // Get mock guest types data
    $mockGuestTypes = [
        [
            'id' => 1,
            'name' => 'Standard',
            'description' => 'Regular hotel guests',
            'default_rate' => 100.00,
            'status' => 'active',
            'created_at' => '2026-02-24T10:00:00Z',
            'updated_at' => '2026-02-24T10:00:00Z',
            'guest_count' => 125
        ],
        [
            'id' => 2,
            'name' => 'VIP',
            'description' => 'Premium guests with special privileges',
            'default_rate' => 250.00,
            'status' => 'active',
            'created_at' => '2026-02-24T11:00:00Z',
            'updated_at' => '2026-02-24T11:00:00Z',
            'guest_count' => 45
        ],
        [
            'id' => 3,
            'name' => 'Corporate',
            'description' => 'Business travelers with corporate rates',
            'default_rate' => 150.00,
            'status' => 'active',
            'created_at' => '2026-02-24T12:00:00Z',
            'updated_at' => '2026-02-24T12:00:00Z',
            'guest_count' => 78
        ],
        [
            'id' => 4,
            'name' => 'Group',
            'description' => 'Large groups with special pricing',
            'default_rate' => 80.00,
            'status' => 'active',
            'created_at' => '2026-02-24T13:00:00Z',
            'updated_at' => '2026-02-24T13:00:00Z',
            'guest_count' => 32
        ]
    ];

    // Calculate stats
    $stats = [
        'total' => count($mockGuestTypes),
        'active' => count(array_filter($mockGuestTypes, fn($type) => $type['status'] === 'active')),
        'inactive' => count(array_filter($mockGuestTypes, fn($type) => $type['status'] === 'inactive')),
        'totalGuests' => array_sum(array_column($mockGuestTypes, 'guest_count'))
    ];

    return Inertia::render('Admin/GuestTypes/Index', [
        'guestTypes' => $mockGuestTypes,
        'stats' => $stats
    ]);
})->name('guest-types.index');
    Route::get('/roles', function () {
        return Inertia::render('Admin/Roles/Index');
    })->name('roles.index');

    // Roles API Routes
    Route::get('/roles/api', function () {
        try {
            // Get all roles with user counts
            $roles = \Spatie\Permission\Models\Role::all()->map(function ($role) {
                try {
                    $userCount = $role->users()->count();
                } catch (\Exception $e) {
                    $userCount = 0;
                }

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->name,
                    'description' => 'Role: ' . $role->name,
                    'user_count' => $userCount,
                    'is_active' => true,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at
                ];
            });

            return response()->json($roles);
        } catch (\Exception $e) {
            // Return mock data if there's an error
            \Log::error('Error fetching roles: ' . $e->getMessage());
            return response()->json([
                [
                    'id' => 1,
                    'name' => 'admin',
                    'display_name' => 'Administrator',
                    'description' => 'System administrator with full access',
                    'user_count' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 2,
                    'name' => 'manager',
                    'display_name' => 'Manager',
                    'description' => 'Hotel manager with management access',
                    'user_count' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    });

    Route::get('/roles/permissions/all', function () {
        try {
            // Get all permissions grouped by category
            $permissions = \Spatie\Permission\Models\Permission::all()->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'general';
            });

            return response()->json([
                'permissions' => $permissions->toArray()
            ]);
        } catch (\Exception $e) {
            // Return mock permissions if there's an error
            Log::error('Error fetching permissions: ' . $e->getMessage());
            return response()->json([
                'permissions' => [
                    'users' => [
                        ['id' => 1, 'name' => 'users.view', 'guard_name' => 'web'],
                        ['id' => 2, 'name' => 'users.create', 'guard_name' => 'web'],
                        ['id' => 3, 'name' => 'users.edit', 'guard_name' => 'web'],
                        ['id' => 4, 'name' => 'users.delete', 'guard_name' => 'web']
                    ],
                    'roles' => [
                        ['id' => 5, 'name' => 'roles.view', 'guard_name' => 'web'],
                        ['id' => 6, 'name' => 'roles.create', 'guard_name' => 'web'],
                        ['id' => 7, 'name' => 'roles.edit', 'guard_name' => 'web'],
                        ['id' => 8, 'name' => 'roles.delete', 'guard_name' => 'web']
                    ],
                    'permissions' => [
                        ['id' => 9, 'name' => 'permissions.manage', 'guard_name' => 'web']
                    ]
                ]
            ]);
        }
    });

    Route::get('/roles/{id}/permissions', function ($id) {
        try {
            $role = \Spatie\Permission\Models\Role::find($id);

            if (!$role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $permissions = $role->permissions->pluck('name')->toArray();

            return response()->json([
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            // Return mock permissions if there's an error
            Log::error('Error fetching role permissions: ' . $e->getMessage());
            return response()->json([
                'permissions' => ['users.view', 'roles.view']
            ]);
        }
    });

    // Analytics
    Route::get('/analytics', function () {
        return Inertia::render('Admin/Analytics/Index');
    })->name('analytics.index');

    // Budget Management
    Route::get('/budget', function () {
        return Inertia::render('Admin/Budget/Index');
    })->name('budget.index');

    Route::get('/budget/dashboard', function () {
        return Inertia::render('Admin/Budget/Dashboard');
    })->name('budget.dashboard');

    Route::get('/budget/analytics', function () {
        return Inertia::render('Admin/Budget/Analytics');
    })->name('budget.analytics');

    Route::get('/budget/alerts', function () {
        return Inertia::render('Admin/Budget/Alerts');
    })->name('budget.alerts');

    Route::get('/budget/archived', function () {
        return Inertia::render('Admin/Budget/Archived');
    })->name('budget.archived');

    Route::get('/budget/reports', function () {
        return Inertia::render('Admin/Budget/Reports');
    })->name('budget.reports');

    Route::get('/budget/expenses', function () {
        return Inertia::render('Admin/Budget/Expenses/Index');
    })->name('budget.expenses.index');

    Route::get('/budget/expenses/pending-approvals', function () {
        return Inertia::render('Admin/Budget/Expenses/PendingApprovals');
    })->name('budget.expenses.pending-approvals');

    // Reports
    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports/Index');
    })->name('reports');

    Route::get('/reports/index', function () {
        return Inertia::render('Admin/Reports/Index');
    })->name('reports.index');

    Route::get('/financial-reports', function () {
        return Inertia::render('Admin/FinancialReports/Index');
    })->name('financial-reports.index');

    Route::get('/reports/occupancy', function () {
        return Inertia::render('Admin/Reports/Occupancy');
    })->name('reports.occupancy');

    Route::get('/reports/revenue', function () {
        return Inertia::render('Admin/Reports/Revenue');
    })->name('reports.revenue');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update.put');

    Route::get('/license/info', function () {
        // Return a mock license response for now
        return response()->json([
            'status' => 'active',
            'license_key' => 'DEMO-LICENSE-KEY',
            'hotel_name' => 'Grand Hotel',
            'license_type' => 'Professional',
            'expires_at' => '2026-12-31',
            'device_allocation' => [
                ['type' => 'POS', 'used' => 2, 'limit' => 5],
                ['type' => 'Front Desk', 'used' => 1, 'limit' => 3],
                ['type' => 'Admin', 'used' => 1, 'limit' => 2]
            ],
            'total_used' => 4,
            'total_limit' => 10,
            'features' => [
                'reservations' => true,
                'pos' => true,
                'housekeeping' => true,
                'reporting' => true,
                'iptv' => true
            ]
        ]);
    })->name('license.info');

    Route::get('/settings/index', function () {
        return Inertia::render('Admin/Settings/Index');
    })->name('settings.index');

    Route::get('/settings/email', function () {
        return Inertia::render('Admin/Settings/Email');
    })->name('settings.email');

    Route::get('/settings/backup', function () {
        return Inertia::render('Admin/Settings/Backup');
    })->name('settings.backup');

    Route::get('/settings/logs', function () {
        return Inertia::render('Admin/Settings/Logs');
    })->name('settings.logs');

    Route::get('/settings/print-settings', function () {
        return Inertia::render('Admin/Settings/PrintSettings');
    })->name('settings.print-settings');

    // Transactions
    Route::get('/transactions', function () {
        return Inertia::render('Admin/Transactions/Index');
    })->name('transactions');

    // Expenses
    Route::get('/expenses', function () {
        return Inertia::render('Admin/Expenses/Index');
    })->name('expenses.index');

    // Customer Groups
    Route::get('/customer-groups', function () {
        // Get mock customer groups data
        $mockCustomerGroups = [
            [
                'id' => 1,
                'name' => 'VIP',
                'discount_percentage' => 10,
                'description' => 'Premium customers with special privileges',
                'min_spending' => 1000,
                'status' => 'active',
                'created_at' => '2026-02-24T10:00:00Z',
                'updated_at' => '2026-02-24T10:00:00Z'
            ],
            [
                'id' => 2,
                'name' => 'Regular',
                'discount_percentage' => 5,
                'description' => 'Standard customers with regular discounts',
                'min_spending' => 0,
                'status' => 'active',
                'created_at' => '2026-02-24T11:00:00Z',
                'updated_at' => '2026-02-24T11:00:00Z'
            ],
            [
                'id' => 3,
                'name' => 'Premium',
                'discount_percentage' => 15,
                'description' => 'High-value customers with maximum benefits',
                'min_spending' => 5000,
                'status' => 'active',
                'created_at' => '2026-02-24T12:00:00Z',
                'updated_at' => '2026-02-24T12:00:00Z'
            ]
        ];

        // Calculate stats
        $stats = [
            'total' => count($mockCustomerGroups),
            'active' => count(array_filter($mockCustomerGroups, fn($group) => $group['status'] === 'active')),
            'inactive' => count(array_filter($mockCustomerGroups, fn($group) => $group['status'] === 'inactive')),
            'totalCustomers' => 150 // Mock total customers count
        ];

        return Inertia::render('Admin/CustomerGroups/Index', [
            'customerGroups' => [
                'data' => $mockCustomerGroups,
                'links' => [],
                'from' => 1,
                'to' => count($mockCustomerGroups),
                'total' => count($mockCustomerGroups),
                'prev_page_url' => null,
                'next_page_url' => null
            ],
            'stats' => $stats
        ]);
    })->name('customer-groups.index');

    Route::get('/customer-groups/create', function () {
        return Inertia::render('Admin/CustomerGroups/Create');
    })->name('customer-groups.create');

    Route::get('/customer-groups/{id}', function ($id) {
        // Get mock customer groups data
        $mockCustomerGroups = [
            [
                'id' => 1,
                'name' => 'VIP',
                'discount_percentage' => 10,
                'description' => 'Premium customers with special privileges',
                'min_spending' => 1000,
                'status' => 'active',
                'created_at' => '2026-02-24T10:00:00Z',
                'updated_at' => '2026-02-24T10:00:00Z'
            ],
            [
                'id' => 2,
                'name' => 'Regular',
                'discount_percentage' => 5,
                'description' => 'Standard customers with regular discounts',
                'min_spending' => 0,
                'status' => 'active',
                'created_at' => '2026-02-24T11:00:00Z',
                'updated_at' => '2026-02-24T11:00:00Z'
            ],
            [
                'id' => 3,
                'name' => 'Premium',
                'discount_percentage' => 15,
                'description' => 'High-value customers with maximum benefits',
                'min_spending' => 5000,
                'status' => 'active',
                'created_at' => '2026-02-24T12:00:00Z',
                'updated_at' => '2026-02-24T12:00:00Z'
            ]
        ];

        // Find the specific customer group
        $customerGroup = collect($mockCustomerGroups)->firstWhere('id', $id);

        if (!$customerGroup) {
            abort(404, 'Customer group not found');
        }

        return Inertia::render('Admin/CustomerGroups/Show', [
            'customerGroup' => $customerGroup
        ]);
    })->name('customer-groups.show');

    Route::get('/customer-groups/{id}/edit', function ($id) {
        // Get mock customer groups data
        $mockCustomerGroups = [
            [
                'id' => 1,
                'name' => 'VIP',
                'discount_percentage' => 10,
                'description' => 'Premium customers with special privileges',
                'min_spending' => 1000,
                'status' => 'active',
                'created_at' => '2026-02-24T10:00:00Z',
                'updated_at' => '2026-02-24T10:00:00Z'
            ],
            [
                'id' => 2,
                'name' => 'Regular',
                'discount_percentage' => 5,
                'description' => 'Standard customers with regular discounts',
                'min_spending' => 0,
                'status' => 'active',
                'created_at' => '2026-02-24T11:00:00Z',
                'updated_at' => '2026-02-24T11:00:00Z'
            ],
            [
                'id' => 3,
                'name' => 'Premium',
                'discount_percentage' => 15,
                'description' => 'High-value customers with maximum benefits',
                'min_spending' => 5000,
                'status' => 'active',
                'created_at' => '2026-02-24T12:00:00Z',
                'updated_at' => '2026-02-24T12:00:00Z'
            ]
        ];

        // Find the specific customer group
        $customerGroup = collect($mockCustomerGroups)->firstWhere('id', $id);

        if (!$customerGroup) {
            abort(404, 'Customer group not found');
        }

        return Inertia::render('Admin/CustomerGroups/Edit', [
            'customerGroup' => $customerGroup
        ]);
    })->name('customer-groups.edit');

    // Customer Groups Export Routes
    Route::get('/customer-groups/export/{format}', function (\Illuminate\Http\Request $request, $format) {
        try {
            // Get mock customer groups data
            $customerGroups = [
                [
                    'id' => 1,
                    'name' => 'VIP',
                    'discount_percentage' => 10,
                    'description' => 'Premium customers with special privileges',
                    'min_spending' => 1000,
                    'status' => 'active',
                    'created_at' => '2026-02-24T10:00:00Z',
                    'updated_at' => '2026-02-24T10:00:00Z',
                    'customer_count' => 45
                ],
                [
                    'id' => 2,
                    'name' => 'Regular',
                    'discount_percentage' => 5,
                    'description' => 'Standard customers with regular discounts',
                    'min_spending' => 0,
                    'status' => 'active',
                    'created_at' => '2026-02-24T11:00:00Z',
                    'updated_at' => '2026-02-24T11:00:00Z',
                    'customer_count' => 85
                ],
                [
                    'id' => 3,
                    'name' => 'Premium',
                    'discount_percentage' => 15,
                    'description' => 'High-value customers with maximum benefits',
                    'min_spending' => 5000,
                    'status' => 'active',
                    'created_at' => '2026-02-24T12:00:00Z',
                    'updated_at' => '2026-02-24T12:00:00Z',
                    'customer_count' => 20
                ]
            ];

            $filename = 'customer_groups_export_' . date('Y-m-d');

            switch (strtolower($format)) {
                case 'csv':
                    return response()->streamDownload(function () use ($customerGroups) {
                        $headers = ['ID', 'Name', 'Discount Percentage', 'Description', 'Min Spending', 'Status', 'Customer Count', 'Created At', 'Updated At'];

                        // Output CSV headers
                        $output = fopen('php://output', 'w');
                        fputcsv($output, $headers);

                        // Output data rows
                        foreach ($customerGroups as $group) {
                            fputcsv($output, [
                                $group['id'],
                                $group['name'],
                                $group['discount_percentage'] . '%',
                                $group['description'],
                                '$' . number_format($group['min_spending'], 2),
                                ucfirst($group['status']),
                                $group['customer_count'],
                                $group['created_at'],
                                $group['updated_at']
                            ]);
                        }

                        fclose($output);
                    }, $filename . '.csv', [
                        'Content-Type' => 'text/csv',
                        'Cache-Control' => 'no-store, no-cache',
                    ]);

                case 'excel':
                    $html = generateCustomerGroupsExcelExport($customerGroups, $filename);
                    return response($html)
                        ->header('Content-Type', 'application/vnd.ms-excel')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"');

                case 'pdf':
                    $html = generateCustomerGroupsPDFExport($customerGroups, $filename);
                    return response($html)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '.pdf"');

                case 'word':
                    $html = generateCustomerGroupsWordExport($customerGroups, $filename);
                    return response($html)
                        ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '.doc"');

                default:
                    return response()->json(['error' => 'Unsupported export format'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Customer groups export error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export customer groups'], 500);
        }
    })->name('customer-groups.export');

    // Helper functions for customer groups export formats
    function generateCustomerGroupsExcelExport($data, $filename) {
        $headers = ['ID', 'Name', 'Discount Percentage', 'Description', 'Min Spending', 'Status', 'Customer Count', 'Created At', 'Updated At'];

        $html = '<html><head><meta charset="utf-8"><style>';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= '</style></head><body>';
        $html .= '<h2>' . $filename . '</h2>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $group) {
            $html .= '<tr>';
            $html .= '<td>' . $group['id'] . '</td>';
            $html .= '<td>' . htmlspecialchars($group['name']) . '</td>';
            $html .= '<td>' . $group['discount_percentage'] . '%</td>';
            $html .= '<td>' . htmlspecialchars($group['description']) . '</td>';
            $html .= '<td>$' . number_format($group['min_spending'], 2) . '</td>';
            $html .= '<td>' . ucfirst($group['status']) . '</td>';
            $html .= '<td>' . $group['customer_count'] . '</td>';
            $html .= '<td>' . $group['created_at'] . '</td>';
            $html .= '<td>' . $group['updated_at'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';
        return $html;
    }

    function generateCustomerGroupsPDFExport($data, $filename) {
        // For now, return HTML that can be converted to PDF
        return generateCustomerGroupsExcelExport($data, $filename);
    }

    function generateCustomerGroupsWordExport($data, $filename) {
        // Word-compatible HTML
        $headers = ['ID', 'Name', 'Discount Percentage', 'Description', 'Min Spending', 'Status', 'Customer Count', 'Created At', 'Updated At'];

        $html = '<html><head><meta charset="utf-8"><style>';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #000; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #e0e0e0; font-weight: bold; } ';
        $html .= '</style></head><body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $group) {
            $html .= '<tr>';
            $html .= '<td>' . $group['id'] . '</td>';
            $html .= '<td>' . htmlspecialchars($group['name']) . '</td>';
            $html .= '<td>' . $group['discount_percentage'] . '%</td>';
            $html .= '<td>' . htmlspecialchars($group['description']) . '</td>';
            $html .= '<td>$' . number_format($group['min_spending'], 2) . '</td>';
            $html .= '<td>' . ucfirst($group['status']) . '</td>';
            $html .= '<td>' . $group['customer_count'] . '</td>';
            $html .= '<td>' . $group['created_at'] . '</td>';
            $html .= '<td>' . $group['updated_at'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';
        return $html;
    }

    // Customers
    Route::get('/customers', function () {
        // Get mock customers data (in a real implementation, you would fetch from database)
        $mockCustomers = [
            [
                'id' => 1,
                'customer_code' => 'CUST001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'customer_group' => [
                    'id' => 1,
                    'name' => 'VIP',
                    'discount_percentage' => 10
                ],
                'status' => 'active',
                'created_at' => '2026-02-24T10:00:00Z',
                'updated_at' => '2026-02-24T10:00:00Z'
            ],
            [
                'id' => 2,
                'customer_code' => 'CUST002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+0987654321',
                'customer_group' => [
                    'id' => 2,
                    'name' => 'Regular',
                    'discount_percentage' => 5
                ],
                'status' => 'active',
                'created_at' => '2026-02-24T11:00:00Z',
                'updated_at' => '2026-02-24T11:00:00Z'
            ],
            [
                'id' => 3,
                'customer_code' => 'CUST003',
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+1122334455',
                'customer_group' => null,
                'status' => 'inactive',
                'created_at' => '2026-02-24T12:00:00Z',
                'updated_at' => '2026-02-24T12:00:00Z'
            ]
        ];

        // Get mock customer groups
        $mockCustomerGroups = [
            ['id' => 1, 'name' => 'VIP', 'discount_percentage' => 10],
            ['id' => 2, 'name' => 'Regular', 'discount_percentage' => 5],
            ['id' => 3, 'name' => 'Premium', 'discount_percentage' => 15]
        ];

        return Inertia::render('Admin/Customers/Index', [
            'customers' => [
                'data' => $mockCustomers,
                'links' => [],
                'from' => 1,
                'to' => count($mockCustomers),
                'total' => count($mockCustomers),
                'prev_page_url' => null,
                'next_page_url' => null
            ],
            'customerGroups' => $mockCustomerGroups,
            'filters' => [
                'search' => '',
                'group_id' => '',
                'status' => ''
            ]
        ]);
    })->name('customers.index');

    Route::get('/customers/create', function () {
        // Get mock customer groups for the create form
        $mockCustomerGroups = [
            ['id' => 1, 'name' => 'VIP', 'discount_percentage' => 10],
            ['id' => 2, 'name' => 'Regular', 'discount_percentage' => 5],
            ['id' => 3, 'name' => 'Premium', 'discount_percentage' => 15]
        ];

        return Inertia::render('Admin/Customers/Create', [
            'customerGroups' => $mockCustomerGroups
        ]);
    })->name('customers.create');
});

// Front Desk Dashboard
Route::middleware(['auth', 'role:front_desk|admin'])->prefix('front-desk')->name('front-desk.')->group(function () {
    Route::get('/dashboard', [FrontDeskDashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::get('/customers', function () {
        return Inertia::render('FrontDesk/Customers/Index');
    })->name('customers.index');

    Route::get('/customer-groups', function () {
        return Inertia::render('FrontDesk/CustomerGroups/Index');
    })->name('customer-groups.index');

    // Reservations
    Route::get('/reservations', function () {
        return Inertia::render('FrontDesk/Reservations/Index');
    })->name('reservations.index');

    Route::get('/reservations/arrivals', function () {
        return Inertia::render('FrontDesk/Reservations/Arrivals');
    })->name('reservations.arrivals');

    Route::get('/reservations/departures', function () {
        return Inertia::render('FrontDesk/Reservations/Departures');
    })->name('reservations.departures');

    // Guests
    Route::get('/guests', function () {
        return Inertia::render('FrontDesk/Guests/Index');
    })->name('guests.index');

    // Check-ins and Check-outs
    Route::get('/checkin', function () {
        return Inertia::render('FrontDesk/Checkin/Index');
    })->name('checkin');

    Route::get('/checkout', function () {
        return Inertia::render('FrontDesk/Checkout/Index');
    })->name('checkout');

    // Rooms
    Route::get('/rooms', function () {
        return Inertia::render('FrontDesk/Rooms/Index');
    })->name('rooms.index');

    Route::get('/room-assignment', function () {
        return Inertia::render('FrontDesk/RoomAssignment/Index');
    })->name('room-assignment');

    // Payments
    Route::get('/payments/process', function () {
        return Inertia::render('FrontDesk/Payments/Process');
    })->name('payments.process');

    // Key Cards
    Route::get('/key-cards', function () {
        return Inertia::render('FrontDesk/KeyCards/Index');
    })->name('key-cards.index');

    // Services
    Route::prefix('services')->name('services.')->group(function () {
        // Concierge Services
        Route::get('/concierge', [\App\Http\Controllers\FrontDesk\ConciergeController::class, 'index'])->name('concierge');
        Route::post('/concierge', [\App\Http\Controllers\FrontDesk\ConciergeController::class, 'store'])->name('concierge.store');
        Route::patch('/concierge/{conciergeRequest}/status', [\App\Http\Controllers\FrontDesk\ConciergeController::class, 'updateStatus'])->name('concierge.update-status');

        // Housekeeping Services
        Route::get('/housekeeping', [\App\Http\Controllers\FrontDesk\HousekeepingController::class, 'index'])->name('housekeeping');
        Route::post('/housekeeping', [\App\Http\Controllers\FrontDesk\HousekeepingController::class, 'store'])->name('housekeeping.store');
        Route::patch('/housekeeping/{housekeepingTask}/status', [\App\Http\Controllers\FrontDesk\HousekeepingController::class, 'updateStatus'])->name('housekeeping.update-status');

        // Maintenance Services
        Route::get('/maintenance', [\App\Http\Controllers\FrontDesk\MaintenanceController::class, 'index'])->name('maintenance');
        Route::post('/maintenance', [\App\Http\Controllers\FrontDesk\MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::patch('/maintenance/{maintenanceRequest}/status', [\App\Http\Controllers\FrontDesk\MaintenanceController::class, 'updateStatus'])->name('maintenance.update-status');
    });
});

// Accountant Dashboard
Route::middleware(['auth', 'role:accountant|admin'])->prefix('accountant')->name('accountant.')->group(function () {
    Route::get('/dashboard', [AccountantDashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::get('/customers', function () {
        return Inertia::render('Accountant/Customers/Index');
    })->name('customers.index');

    Route::get('/customer-groups', function () {
        return Inertia::render('Accountant/CustomerGroups/Index');
    })->name('customer-groups.index');

    // Budget
    Route::get('/budget', function () {
        return Inertia::render('Accountant/Budget/Index');
    })->name('budget.index');

    Route::get('/budget/comparison', function () {
        return Inertia::render('Accountant/Budget/Comparison');
    })->name('budget.comparison');

    Route::get('/budget/forecast', function () {
        return Inertia::render('Accountant/Budget/Forecast');
    })->name('budget.forecast');

    // Accounting
    Route::get('/transactions', function () {
        return Inertia::render('Accountant/Transactions/Index');
    })->name('transactions.index');

    Route::get('/expenses', function () {
        return Inertia::render('Accountant/Expenses/Index');
    })->name('expenses.index');

    Route::get('/invoices', function () {
        return Inertia::render('Accountant/Invoices/Index');
    })->name('invoices.index');

    Route::get('/payroll', function () {
        return Inertia::render('Accountant/Payroll/Index');
    })->name('payroll.index');

    // Reports
    Route::get('/reports/profit-loss', function () {
        return Inertia::render('Accountant/Reports/ProfitLoss');
    })->name('reports.profit-loss');

    Route::get('/reports/balance-sheet', function () {
        return Inertia::render('Accountant/Reports/BalanceSheet');
    })->name('reports.balance-sheet');

    Route::get('/reports/cash-flow', function () {
        return Inertia::render('Accountant/Reports/CashFlow');
    })->name('reports.cash-flow');

    Route::get('/reports/revenue', function () {
        return Inertia::render('Accountant/Reports/Revenue');
    })->name('reports.revenue');
});

// Housekeeping Dashboard
Route::middleware(['auth', 'role:housekeeping|admin'])->prefix('housekeeping')->name('housekeeping.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Housekeeping/Dashboard');
    })->name('dashboard');

    // Rooms
    Route::get('/rooms', function () {
        return Inertia::render('Housekeeping/Rooms/Index');
    })->name('rooms.index');

    Route::get('/rooms/to-clean', function () {
        return Inertia::render('Housekeeping/Rooms/ToClean');
    })->name('rooms.to-clean');

    // Tasks
    Route::get('/tasks/daily', function () {
        return Inertia::render('Housekeeping/Tasks/Daily');
    })->name('tasks.daily');

    Route::get('/tasks/weekly', function () {
        return Inertia::render('Housekeeping/Tasks/Weekly');
    })->name('tasks.weekly');

    Route::get('/tasks/history', function () {
        return Inertia::render('Housekeeping/Tasks/History');
    })->name('tasks.history');

    // Inventory
    Route::get('/inventory/amenities', function () {
        return Inertia::render('Housekeeping/Inventory/Amenities');
    })->name('inventory.amenities');

    Route::get('/inventory/linens', function () {
        return Inertia::render('Housekeeping/Inventory/Linens');
    })->name('inventory.linens');

    Route::get('/inventory/supplies', function () {
        return Inertia::render('Housekeeping/Inventory/Supplies');
    })->name('inventory.supplies');

    Route::get('/inventory/request', function () {
        return Inertia::render('Housekeeping/Inventory/Request');
    })->name('inventory.request');

    // Maintenance Report
    Route::get('/maintenance/report', function () {
        return Inertia::render('Housekeeping/Maintenance/Report');
    })->name('maintenance.report');
});

// Maintenance Dashboard
Route::middleware(['auth', 'role:maintenance|admin'])->prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Maintenance/Dashboard');
    })->name('dashboard');

    // IPTV Devices
    Route::get('/iptv/devices', function () {
        return Inertia::render('Maintenance/IPTV/Devices');
    })->name('iptv.devices');

    // Preventive Maintenance
    Route::get('/preventive/scheduled', function () {
        return Inertia::render('Maintenance/Preventive/Scheduled');
    })->name('preventive.scheduled');
});

// Manager Dashboard
Route::middleware(['auth', 'role:manager|admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

    // Staff Schedules
    Route::get('/staff/schedules', [\App\Http\Controllers\Manager\ScheduleController::class, 'index'])->name('staff.schedules');

    // Operations
    Route::get('/operations', function () {
        return Inertia::render('Manager/Operations');
    })->name('operations');

    // Reports
    Route::get('/reports', function () {
        return Inertia::render('Manager/Reports');
    })->name('reports');

    // Reservations
    Route::get('/reservations', function () {
        return Inertia::render('Manager/Reservations/Index');
    })->name('reservations.index');

    // Guests
    Route::get('/guests/current', function () {
        return Inertia::render('Manager/Guests/Current');
    })->name('guests.current');

    Route::get('/guests/history', function () {
        return Inertia::render('Manager/Guests/History');
    })->name('guests.history');

    // Rooms
    Route::get('/rooms', function () {
        return Inertia::render('Manager/Rooms/Index');
    })->name('rooms.index');

    // Housekeeping Tasks
    Route::get('/housekeeping-tasks', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'index'])->name('housekeeping-tasks.index');
    Route::get('/housekeeping-tasks/{housekeepingTask}', [\App\Http\Controllers\Admin\HousekeepingTaskController::class, 'show'])->name('housekeeping-tasks.show');

    // Maintenance
    Route::get('/maintenance', function () {
        return Inertia::render('Manager/Maintenance/Index');
    })->name('maintenance');

    Route::get('/maintenance-requests', function () {
        return Inertia::render('Manager/MaintenanceRequests/Index');
    })->name('maintenance-requests.index');

    // IPTV
    Route::get('/iptv', function () {
        return Inertia::render('Manager/IPTV/Index');
    })->name('iptv.index');

    // Check-in/Check-out
    Route::get('/checkin', function () {
        return Inertia::render('Manager/Checkin/Index');
    })->name('checkin');

    Route::get('/checkout', function () {
        return Inertia::render('Manager/Checkout/Index');
    })->name('checkout');

    // Customers
    Route::get('/customers', function () {
        return Inertia::render('Manager/Customers/Index');
    })->name('customers.index');

    Route::get('/customer-groups', function () {
        return Inertia::render('Manager/CustomerGroups/Index');
    })->name('customer-groups.index');
});

// Hall Booking Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Halls Management
    Route::resource('halls', HallController::class);

    // Hall Availability
    Route::get('/halls/{hall}/availability', [HallController::class, 'availability'])->name('halls.availability');
    Route::get('/halls/{hall}/bookings', [HallController::class, 'bookings'])->name('halls.bookings');
});

// Package Booking Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Packages Management
    Route::resource('packages', PackageController::class);

    // Package Availability
    Route::get('/packages/{package}/availability', [PackageController::class, 'availability'])->name('packages.availability');
});

// Group Booking Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Group Bookings Management
    Route::resource('group-bookings', GroupBookingController::class);

    // Group Booking Details
    Route::get('/group-bookings/{groupBooking}/details', [GroupBookingController::class, 'details'])->name('group-bookings.details');
    Route::get('/group-bookings/{groupBooking}/invoices', [GroupBookingController::class, 'invoices'])->name('group-bookings.invoices');
});

// Reservations with Hall/Package/Group Booking support
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('reservations', ReservationController::class);

    // Reservation with hall booking
    Route::get('/reservations/{reservation}/hall-booking', [ReservationController::class, 'hallBooking'])->name('reservations.hall-booking');
    Route::post('/reservations/{reservation}/hall-booking', [ReservationController::class, 'storeHallBooking'])->name('reservations.hall-booking.store');

    // Reservation with package booking
    Route::get('/reservations/{reservation}/package-booking', [ReservationController::class, 'packageBooking'])->name('reservations.package-booking');
    Route::post('/reservations/{reservation}/package-booking', [ReservationController::class, 'storePackageBooking'])->name('reservations.package-booking.store');

    // Reservation with group booking
    Route::get('/reservations/{reservation}/group-booking', [ReservationController::class, 'groupBooking'])->name('reservations.group-booking');
    Route::post('/reservations/{reservation}/group-booking', [ReservationController::class, 'storeGroupBooking'])->name('reservations.group-booking.store');

    // Payment for hall/package/group booking
    Route::post('/reservations/{reservation}/pay-booking', [ReservationController::class, 'payBooking'])->name('reservations.pay-booking');
});

// POS Routes
Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [\App\Http\Controllers\POS\POSController::class, 'index'])->name('index');
    Route::post('/process-sale', [\App\Http\Controllers\POS\POSController::class, 'processSale'])->name('process-sale');
    Route::post('/open-drawer', [\App\Http\Controllers\POS\POSController::class, 'openDrawer'])->name('open-drawer');

    // Suppliers
    Route::get('/suppliers', [\App\Http\Controllers\POS\SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [\App\Http\Controllers\POS\SupplierController::class, 'store'])->name('suppliers.store');

    // Products
    Route::get('/products', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get settings for currency display
        $settings = [
            'currency' => \App\Models\Setting::where('key', 'currency')->first()?->value ?? 'USD',
            'currency_position' => \App\Models\Setting::where('key', 'currency_position')->first()?->value ?? 'before',
            'decimal_separator' => \App\Models\Setting::where('key', 'decimal_separator')->first()?->value ?? '.',
            'thousand_separator' => \App\Models\Setting::where('key', 'thousand_separator')->first()?->value ?? ',',
            'currency_decimals' => \App\Models\Setting::where('key', 'currency_decimals')->first()?->value ?? 2,
        ];

        return Inertia::render('POS/Products/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'products' => \App\Models\Product::with('category', 'brand', 'unit')->orderBy('name')->get(),
            'categories' => \App\Models\ProductCategory::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'units' => \App\Models\Unit::orderBy('name')->get(),
            'settings' => $settings
        ]);
    })->name('products.index');

    // Product CRUD
    Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');

    // Stock Adjustment
    Route::post('/adjust-stock', [\App\Http\Controllers\POS\POSController::class, 'adjustStock'])->name('pos.adjust-stock');

    // Product History
    Route::get('/product-history/{product}', [\App\Http\Controllers\POS\POSController::class, 'productHistory'])->name('pos.product-history');

    // Stock Adjustments
    Route::post('/adjustments', [\App\Http\Controllers\POS\POSController::class, 'storeAdjustment'])->name('pos.adjustments.store');

    // Stock Transfers
    Route::post('/transfers', [\App\Http\Controllers\POS\POSController::class, 'storeTransfer'])->name('pos.transfers.store');

    // API endpoints for dropdowns
    Route::get('/api/products', function () {
        return response()->json([
            'products' => \App\Models\Product::orderBy('name')->get()
        ]);
    });

    Route::get('/api/products-and-warehouses', function () {
        return response()->json([
            'products' => \App\Models\Product::orderBy('name')->get(),
            'warehouses' => \App\Models\Warehouse::orderBy('name')->get()
        ]);
    });

    // Categories
    Route::get('/categories', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        // Get categories with product counts
        $categories = \App\Models\ProductCategory::withCount('products')->orderBy('name')->get();

        return Inertia::render('POS/Categories/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'categories' => $categories
        ]);
    })->name('categories.index');

    // Brands
    Route::get('/brands', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Brands/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'brands' => \App\Models\Brand::withCount('products')->orderBy('name')->get()
        ]);
    })->name('brands.index');

    // Units
    Route::get('/units', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Units/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'units' => \App\Models\Unit::withCount('products')->orderBy('name')->get()
        ]);
    })->name('units.index');

    // Warehouses
    Route::get('/warehouses', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('POS/Warehouses/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'warehouses' => \App\Models\Warehouse::withCount('products')->orderBy('name')->get()
        ]);
    })->name('warehouses.index');

    // Purchases

    Route::get('/products-and-warehouses', function () {
        return response()->json([
            'products' => \App\Models\Product::orderBy('name')->get(),
            'warehouses' => \App\Models\Warehouse::orderBy('name')->get()
        ]);
    });
});

// HR Management Routes
Route::middleware(['auth', 'role:hr'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('HR/Dashboard', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('dashboard.index');

    // Reports
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('HR/Reports/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reports.index');
});
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'employee_id' => $validated['employee_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'hire_date' => $validated['hire_date'] ?? now(),
                'salary' => $validated['salary'] ?? 0,
                'is_active' => $validated['is_active'] ?? true
            ]);

            // Assign role
            $role = \App\Models\Role::find($validated['role_id']);
            $user->roles()->attach($role);

            // Assign to department if provided
            if (!empty($validated['department_id'])) {
                $user->departments()->attach($validated['department_id']);
            }

            return redirect()->route('hr.employees.index')
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create employee: ' . $e->getMessage())
                ->withInput();
        }
    })->name('employees.store');

    Route::get('/employees/{id}', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $employee = \App\Models\User::with(['roles', 'departments', 'attendances' => function($query) {
            $query->orderBy('date', 'desc')->take(30);
        }])->findOrFail($id);

        return Inertia::render('HR/Employees/Show', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'employee' => $employee
        ]);
    })->name('employees.show');

    Route::get('/employees/{id}/edit', function ($id) {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $employee = \App\Models\User::with(['roles', 'departments'])->findOrFail($id);

        return Inertia::render('HR/Employees/Edit', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'employee' => $employee,
            'roles' => \App\Models\Role::where('name', '!=', 'admin')->get(),
            'departments' => \App\Models\Department::orderBy('name')->get()
        ]);
    })->name('employees.edit');

    Route::put('/employees/{id}', function (\Illuminate\Http\Request $request, $id) {
        try {
            $employee = \App\Models\User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'employee_id' => 'nullable|string|max:50',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'date_of_birth' => 'nullable|date',
                'hire_date' => 'nullable|date',
                'salary' => 'nullable|numeric|min:0',
                'role_id' => 'required|exists:roles,id',
                'department_id' => 'nullable|exists:departments,id',
                'is_active' => 'boolean'
            ]);

            $employee->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'employee_id' => $validated['employee_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'hire_date' => $validated['hire_date'] ?? now(),
                'salary' => $validated['salary'] ?? 0,
                'is_active' => $validated['is_active'] ?? true
            ]);

            // Update role
            $employee->roles()->sync([$validated['role_id']]);

            // Update department
            if (!empty($validated['department_id'])) {
                $employee->departments()->sync([$validated['department_id']]);
            } else {
                $employee->departments()->detach();
            }

            return redirect()->route('hr.employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update employee: ' . $e->getMessage())
                ->withInput();
        }
    })->name('employees.update');

    Route::delete('/employees/{id}', function ($id) {
        try {
            $employee = \App\Models\User::findOrFail($id);

            // Don't allow deletion of admin users
            if ($employee->hasRole('admin')) {
                return redirect()->back()
                    ->with('error', 'Cannot delete admin users.');
            }

            $employee->delete();

            return redirect()->route('hr.employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    })->name('employees.destroy');

    // Departments
    Route::get('/departments', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $departments = \App\Models\Department::withCount('users')->orderBy('name')->paginate(10);

        return Inertia::render('HR/Departments/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'departments' => $departments
        ]);
    })->name('departments.index');

    Route::get('/departments/create', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('HR/Departments/Create', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('departments.create');

    Route::post('/departments', function (\Illuminate\Http\Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:departments,name',
                'description' => 'nullable|string',
                'manager_id' => 'nullable|exists:users,id'
            ]);

            \App\Models\Department::create($validated);

            return redirect()->route('hr.departments.index')
                ->with('success', 'Department created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create department: ' . $e->getMessage())
                ->withInput();
        }
    })->name('departments.store');

    // Attendance
    Route::get('/attendance', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        $attendances = \App\Models\Attendance::with('user')
            ->when(request('date_from'), function ($query, $date) {
                $query->whereDate('date', '>=', $date);
            })
            ->when(request('date_to'), function ($query, $date) {
                $query->whereDate('date', '<=', $date);
            })
            ->when(request('employee_id'), function ($query, $employeeId) {
                $query->where('user_id', $employeeId);
            })
            ->orderBy('date', 'desc')
            ->paginate(20);

        return Inertia::render('HR/Attendance/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'attendances' => $attendances,
            'filters' => request()->only(['date_from', 'date_to', 'employee_id']),
            'employees' => \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', '!=', 'admin');
            })->orderBy('name')->get()
        ]);
    })->name('attendance.index');

    Route::post('/attendance/check-in', function () {
        try {
            $user = auth()->user();

            // Check if already checked in today
            $existing = \App\Models\Attendance::where('user_id', $user->id)
                ->whereDate('date', now()->format('Y-m-d'))
                ->first();

            if ($existing && $existing->check_in_time) {
                return redirect()->back()
                    ->with('error', 'Already checked in today.');
            }

            \App\Models\Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => now()->format('Y-m-d')
                ],
                [
                    'check_in_time' => now()->format('H:i:s'),
                    'status' => 'present'
                ]
            );

            return redirect()->back()
                ->with('success', 'Checked in successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to check in: ' . $e->getMessage());
        }
    })->name('attendance.check-in');

    Route::post('/attendance/check-out', function () {
        try {
            $user = auth()->user();

            $attendance = \App\Models\Attendance::where('user_id', $user->id)
                ->whereDate('date', now()->format('Y-m-d'))
                ->first();

            if (!$attendance || !$attendance->check_in_time) {
                return redirect()->back()
                    ->with('error', 'Please check in first.');
            }

            if ($attendance->check_out_time) {
                return redirect()->back()
                    ->with('error', 'Already checked out today.');
            }

            $attendance->update([
                'check_out_time' => now()->format('H:i:s')
            ]);

            return redirect()->back()
                ->with('success', 'Checked out successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to check out: ' . $e->getMessage());
        }
    })->name('attendance.check-out');

    // Payroll
    Route::get('/payroll', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('HR/Payroll/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role),
            'employees' => \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', '!=', 'admin');
            })->orderBy('name')->get()
        ]);
    })->name('payroll.index');

    // Reports
    Route::get('/reports', function () {
        $user = auth()->user()->load('roles');
        $role = $user->roles->first()?->name ?? 'staff';

        return Inertia::render('HR/Reports/Index', [
            'user' => $user,
            'navigation' => app(\App\Http\Controllers\DashboardController::class)->getNavigationForRole($role)
        ]);
    })->name('reports.index');
});
