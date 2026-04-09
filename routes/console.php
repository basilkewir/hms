<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Simple reservation automation command
Artisan::command('reservations:automate-statuses {--dry-run : Show what would be done without making changes}', function () {
    $dryRun = $this->option('dry-run');

    $this->info('Starting reservation status automation...');
    if ($dryRun) {
        $this->info('DRY RUN MODE - No changes will be made');
    }

    // Get automation configuration
    $config = [
        'no_show_hours' => config('hotel.automation.no_show_hours', 2),
        'auto_checkout_hour' => config('hotel.automation.auto_checkout_hour', 11),
        'pending_confirmation_hours' => config('hotel.automation.pending_confirmation_hours', 24),
        'enable_notifications' => config('hotel.automation.enable_notifications', true),
        'dry_run' => $dryRun,
    ];

    // Process no-show reservations
    $this->info('Processing no-show reservations...');
    $cutoffTime = now()->subHours($config['no_show_hours']);

    $reservations = \App\Models\Reservation::where('status', 'confirmed')
        ->where('check_in_date', today())
        ->whereNull('actual_check_in')
        ->where(function($query) use ($cutoffTime) {
            $query->whereNull('preferred_check_in_time')
                  ->orWhere('preferred_check_in_time', '<=', $cutoffTime->format('H:i'));
        })
        ->get();

    foreach ($reservations as $reservation) {
        if (!$dryRun) {
            // Mark as no-show logic here
            $this->line("Would mark reservation #{$reservation->reservation_number} as no-show");
        } else {
            $this->line("Would mark reservation #{$reservation->reservation_number} as no-show (DRY RUN)");
        }
    }

    $this->info("Processed {$reservations->count()} no-show reservations");

    // Process auto check-out
    $this->info('Processing automatic check-outs...');
    $autoCheckoutTime = now()->setHour($config['auto_checkout_hour'])->setMinute(0)->setSecond(0);

    if (now()->lt($autoCheckoutTime)) {
        $this->info('Auto-checkout time not reached yet');
    } else {
        $checkoutReservations = \App\Models\Reservation::where('status', 'checked_in')
            ->where('check_out_date', '<', today())
            ->orWhere(function($query) {
                $query->where('check_out_date', today())
                      ->where('actual_check_out', null);
            })
            ->get();

        foreach ($checkoutReservations as $reservation) {
            if (!$dryRun) {
                // Auto check-out logic here
                $this->line("Would auto-check out reservation #{$reservation->reservation_number}");
            } else {
                $this->line("Would auto-check out reservation #{$reservation->reservation_number} (DRY RUN)");
            }
        }

        $this->info("Processed {$checkoutReservations->count()} auto check-outs");
    }

    $this->info('Reservation status automation completed.');

})->purpose('Automatically update reservation statuses based on time and conditions');

// Simple daily summary command
Artisan::command('reservations:daily-summary {--email= : Email address to send summary to}', function () {
    $email = $this->option('email');

    $this->info('Generating daily reservation summary...');

    $today = today();
    $summary = [
        'date' => $today->format('Y-m-d'),
        'date_formatted' => $today->format('F j, Y'),
        'arrivals' => \App\Models\Reservation::whereDate('check_in_date', $today)->where('status', 'confirmed')->count(),
        'arrivals_checked_in' => \App\Models\Reservation::whereDate('check_in_date', $today)->where('status', 'checked_in')->count(),
        'departures' => \App\Models\Reservation::whereDate('check_out_date', $today)->where('status', 'checked_in')->count(),
        'departures_checked_out' => \App\Models\Reservation::whereDate('check_out_date', $today)->where('status', 'checked_out')->count(),
        'no_shows' => \App\Models\Reservation::whereDate('check_in_date', $today)->where('status', 'no_show')->count(),
        'cancellations' => \App\Models\Reservation::whereDate('cancelled_at', $today)->count(),
        'current_occupancy' => \App\Models\Reservation::where('status', 'checked_in')->count(),
        'pending_reservations' => \App\Models\Reservation::where('status', 'pending')->count(),
        'modified_reservations' => \App\Models\Reservation::where('status', 'modified')->count(),
        'total_room_revenue' => \App\Models\Reservation::whereDate('created_at', $today)->sum('total_room_charges'),
        'total_revenue' => \App\Models\Reservation::whereDate('created_at', $today)->sum('total_amount'),
        'automation_summary' => [
            'auto_checkouts' => 0,
            'auto_no_shows' => 0,
            'auto_cancellations' => 0,
        ],
    ];

    $this->info("Daily summary generated for {$summary['date_formatted']}");
    $this->info("Arrivals: " . ($summary['arrivals'] + $summary['arrivals_checked_in']));
    $this->info("Departures: " . ($summary['departures'] + $summary['departures_checked_out']));
    $this->info("No-shows: {$summary['no_shows']}");
    $this->info("Cancellations: {$summary['cancellations']}");
    $this->info("Current Occupancy: {$summary['current_occupancy']}");
    $this->info("Total Revenue: " . number_format($summary['total_revenue'], 2) . " FCFA");

    if ($email) {
        $this->info("Summary would be sent to: {$email}");
    }

    $this->info('Daily reservation summary completed.');

})->purpose('Generate and send daily reservation summary report');

// Generate daily cleaning tasks command
Artisan::command('housekeeping:generate-daily-tasks {--dry-run : Show what would be done without making changes}', function () {
    $dryRun = $this->option('dry-run');

    $this->info('Starting daily cleaning task generation...');
    if ($dryRun) {
        $this->info('DRY RUN MODE - No changes will be made');
    }

    $today = \Carbon\Carbon::today();
    $tasksCreated = 0;
    $roomsMarkedDirty = 0;
    $skipped = 0;
    $tasksAssigned = 0;

    // Load active housekeeping staff for round-robin assignment
    // Match users whose position text column is 'Housekeeper' OR whose
    // linked Position record has name 'Housekeeper'
    $cleaners = \App\Models\User::where('employment_status', 'active')
        ->where(function ($q) {
            $q->where('position', 'Housekeeper')
              ->orWhereHas('position', fn($inner) => $inner->where('name', 'Housekeeper'));
        })
        ->get(['id', 'first_name', 'last_name']);

    // Seed each cleaner's count with tasks already assigned today
    $cleanerTaskCounts = [];
    foreach ($cleaners as $cleaner) {
        $cleanerTaskCounts[$cleaner->id] = \App\Models\HousekeepingTask::where('assigned_to', $cleaner->id)
            ->whereDate('scheduled_date', $today)
            ->count();
    }

    if ($cleaners->isEmpty()) {
        $this->warn('No housekeeping staff found – tasks will be created unassigned.');
    } else {
        $names = $cleaners->map(fn($c) => trim($c->first_name . ' ' . $c->last_name))->implode(', ');
        $this->info("Found {$cleaners->count()} cleaner(s): {$names}");
    }

    // Collect reservation data for priority / skip logic
    $occupiedRoomIds = \App\Models\Reservation::where('status', 'checked_in')
        ->whereDate('check_in_date', '<=', $today)
        ->whereDate('check_out_date', '>=', $today)
        ->pluck('room_id')
        ->unique();

    $checkoutRoomIds = \App\Models\Reservation::whereIn('status', ['checked_in', 'checked_out'])
        ->whereDate('check_out_date', $today)
        ->pluck('room_id')
        ->unique();

    // Target ALL active rooms; clean rooms with no reservation are skipped in the loop
    $rooms = \App\Models\Room::where('is_active', true)->get();

    $this->info("Found {$rooms->count()} active rooms to evaluate...");

    foreach ($rooms as $room) {
        // Load current checked-in reservation for this room
        $currentReservation = $room->reservations()
            ->where('status', 'checked_in')
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>=', $today)
            ->first();

        // Check if guest requested no cleaning
        $guestRequestedNoCleaning = false;
        if ($currentReservation) {
            $preferences = $currentReservation->guest_preferences ?? [];
            if (is_string($preferences)) {
                $preferences = json_decode($preferences, true) ?? [];
            }
            $guestRequestedNoCleaning = isset($preferences['housekeeping_preferences']) &&
                in_array('no_cleaning', (array)$preferences['housekeeping_preferences']);
        }

        // Check if room was already validated as clean today
        $alreadyValidatedToday = \App\Models\HousekeepingTask::where('room_id', $room->id)
            ->where('task_type', 'cleaning')
            ->where('status', 'completed')
            ->where('validation_status', 'validated')
            ->whereDate('validation_timestamp', $today)
            ->exists();

        // Check if there's already a pending/in-progress cleaning task for today
        $existingTask = \App\Models\HousekeepingTask::where('room_id', $room->id)
            ->where('task_type', 'cleaning')
            ->whereDate('scheduled_date', $today)
            ->first();

        if ($guestRequestedNoCleaning) {
            $this->line("Room {$room->room_number}: Guest requested no cleaning - skipping");
            $skipped++;
            continue;
        }

        if ($alreadyValidatedToday) {
            $this->line("Room {$room->room_number}: Already validated as clean today - skipping");
            $skipped++;
            continue;
        }

        $isCheckoutToday = $checkoutRoomIds->contains($room->id);
        $isOccupied      = $occupiedRoomIds->contains($room->id);
        $hasReservation  = $isCheckoutToday || $isOccupied;

        // Skip rooms that are clean and have no active/checkout reservation
        if (!$hasReservation && ($room->housekeeping_status === 'clean' || $room->housekeeping_status === null)) {
            $this->line("Room {$room->room_number}: Already clean and no active reservation - skipping");
            $skipped++;
            continue;
        }

        // 3-tier priority: checkout=high, occupied=normal, dirty-only=low
        $priority      = $isCheckoutToday ? 'high' : ($isOccupied ? 'normal' : 'low');
        $scheduledTime = $isCheckoutToday ? '10:00:00' : '09:00:00';

        // Pick cleaner with fewest tasks today (load-balanced round-robin)
        $assignedTo   = null;
        $cleanerName  = 'unassigned';
        if (!$cleaners->isEmpty()) {
            asort($cleanerTaskCounts);
            $assignedTo  = array_key_first($cleanerTaskCounts);
            $cleanerObj  = $cleaners->firstWhere('id', $assignedTo);
            $cleanerName = $cleanerObj ? trim($cleanerObj->first_name . ' ' . $cleanerObj->last_name) : 'unknown';
        }

        if (!$dryRun) {
            $room->update(['housekeeping_status' => 'dirty']);
            $roomsMarkedDirty++;

            if (!$existingTask) {
                \App\Models\HousekeepingTask::create([
                    'room_id'           => $room->id,
                    'assigned_to'       => $assignedTo,
                    'task_type'         => 'cleaning',
                    'priority'          => $priority,
                    'status'            => 'pending',
                    'scheduled_date'    => $today->format('Y-m-d'),
                    'scheduled_time'    => $scheduledTime,
                    'estimated_minutes' => 30,
                    'instructions'      => $isCheckoutToday
                        ? 'Checkout room – full deep clean required.'
                        : ($isOccupied
                            ? 'Occupied room – daily refresh while guest is staying.'
                            : 'Room is dirty and needs cleaning.'),
                ]);
                $tasksCreated++;
                if ($assignedTo) {
                    $cleanerTaskCounts[$assignedTo]++;
                    $tasksAssigned++;
                }
                $label = $isCheckoutToday ? 'HIGH-priority checkout' : 'daily';
                $this->line("Room {$room->room_number}: Marked dirty + {$label} task → {$cleanerName}");
            } else {
                if ($existingTask->status === 'cancelled') {
                    $existingTask->update(['status' => 'pending', 'priority' => $priority, 'assigned_to' => $assignedTo]);
                } elseif (is_null($existingTask->assigned_to) && $assignedTo) {
                    $existingTask->update(['assigned_to' => $assignedTo]);
                    $cleanerTaskCounts[$assignedTo]++;
                    $tasksAssigned++;
                }
                $this->line("Room {$room->room_number}: Marked dirty (task already exists)");
            }
        } else {
            $label = $isCheckoutToday ? 'checkout clean' : ($isOccupied ? 'daily clean' : 'dirty room clean');
            if (!$existingTask) {
                $this->line("Room {$room->room_number}: Would create {$label} [{$priority}] task → {$cleanerName} (DRY RUN)");
                $tasksCreated++;
                if ($assignedTo) $cleanerTaskCounts[$assignedTo]++;
            } else {
                $this->line("Room {$room->room_number}: Would mark dirty, task already exists (DRY RUN)");
            }
            $roomsMarkedDirty++;
        }
    }

    $this->newLine();
    $this->info('=== Daily Cleaning Task Generation Summary ===');
    $this->info("Date: {$today->format('Y-m-d')}");
    $this->info("Active Rooms Evaluated: {$rooms->count()}");
    $this->info("Rooms Marked Dirty: {$roomsMarkedDirty}");
    $this->info("Tasks Created: {$tasksCreated}");
    $this->info("Tasks Auto-Assigned: {$tasksAssigned}");
    $this->info("Skipped (no-clean request / already validated): {$skipped}");
    if (!$cleaners->isEmpty()) {
        $this->newLine();
        $this->info('Cleaner workload after run:');
        foreach ($cleaners as $c) {
            $count = $cleanerTaskCounts[$c->id] ?? 0;
            $this->line('  ' . trim($c->first_name . ' ' . $c->last_name) . ": {$count} task(s)");
        }
    }

    if ($dryRun) {
        $this->info('DRY RUN COMPLETED - No actual changes made');
    } else {
        $this->info('Daily cleaning task generation completed successfully');
    }

})->purpose('Mark all currently occupied and checkout rooms dirty and create cleaning tasks for the day');

// Schedule the commands
if (app()->runningInConsole()) {
    app()->booted(function () {
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);

        // Run reservation status automation every 30 minutes
        $schedule->command('reservations:automate-statuses')
                ->everyThirtyMinutes()
                ->description('Automate reservation status updates')
                ->withoutOverlapping();

        // Run at specific times for critical operations
        $schedule->command('reservations:automate-statuses')
                ->dailyAt('11:30') // After auto-checkout time
                ->description('Daily reservation status check')
                ->withoutOverlapping();

        $schedule->command('reservations:automate-statuses')
                ->dailyAt('14:30') // After check-in deadline
                ->description('Afternoon reservation status check')
                ->withoutOverlapping();

        // Send daily summary reports
        $schedule->command('reservations:daily-summary')
                ->dailyAt('23:55')
                ->description('Send daily reservation summary')
                ->withoutOverlapping();

        // Generate daily cleaning tasks
        $schedule->command('housekeeping:generate-daily-tasks')
                ->dailyAt('06:00') // Early morning before shift starts
                ->description('Generate daily cleaning tasks for all rooms')
                ->withoutOverlapping();

        // Also run at noon for any missed rooms
        $schedule->command('housekeeping:generate-daily-tasks')
                ->dailyAt('12:00')
                ->description('Generate cleaning tasks for missed rooms')
                ->withoutOverlapping();

        // Verify the active license against the license server every 6 hours
        $schedule->command('license:check')
                ->everySixHours()
                ->description('Periodic online license verification')
                ->withoutOverlapping()
                ->runInBackground();

        // Fetch fresh weather data from OpenWeatherMap and cache it for IPTV devices
        $schedule->command('weather:fetch')
                ->everyFifteenMinutes()
                ->description('Cache current weather for IPTV player welcome screens')
                ->withoutOverlapping()
                ->runInBackground();
    });
}
