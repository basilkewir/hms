<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateDailyCleaningTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'housekeeping:generate-daily-tasks {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily cleaning tasks for all rooms, marking them as dirty unless cleaner validated or guest requested no cleaning';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Starting daily cleaning task generation...');
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $today = Carbon::today();
        $tasksCreated = 0;
        $roomsMarkedDirty = 0;
        $skipped = 0;
        $exceptions = 0;
        $tasksAssigned = 0;

        try {
            // Load active housekeeping staff for round-robin assignment
            // Match users whose position text column is 'Housekeeper' OR whose
            // linked Position record has name 'Housekeeper'
            $cleaners = User::where('employment_status', 'active')
                ->where(function ($q) {
                    $q->where('position', 'Housekeeper')
                      ->orWhereHas('position', fn($inner) => $inner->where('name', 'Housekeeper'));
                })
                ->get(['id', 'first_name', 'last_name']);

            // Seed each cleaner's count with tasks already assigned to them today
            // so new tasks top-up fairly on existing workloads
            $cleanerTaskCounts = [];
            foreach ($cleaners as $cleaner) {
                $cleanerTaskCounts[$cleaner->id] = HousekeepingTask::where('assigned_to', $cleaner->id)
                    ->whereDate('scheduled_date', $today)
                    ->count();
            }

            if ($cleaners->isEmpty()) {
                $this->warn('No active housekeeping staff found – tasks will be created unassigned.');
            } else {
                $names = $cleaners->map(fn($c) => trim($c->first_name . ' ' . $c->last_name))->implode(', ');
                $this->info("Found {$cleaners->count()} cleaner(s) for assignment: {$names}");
            }

            // Collect reservation data for priority / skip logic
            $occupiedRoomIds = Reservation::where('status', 'checked_in')
                ->whereDate('check_in_date', '<=', $today)
                ->whereDate('check_out_date', '>=', $today)
                ->pluck('room_id')
                ->unique();

            $checkoutRoomIds = Reservation::whereIn('status', ['checked_in', 'checked_out'])
                ->whereDate('check_out_date', $today)
                ->pluck('room_id')
                ->unique();

            // Target ALL active rooms, not just occupied/checkout ones.
            // Clean rooms with no reservation will be skipped in the loop below.
            $rooms = Room::where('is_active', true)->with('roomType')->get();

            $this->info("Found {$rooms->count()} active rooms to evaluate...");

            foreach ($rooms as $room) {
                try {
                    $guestRequestedNoCleaning = $this->hasGuestRequestedNoCleaning($room, $today);
                    $alreadyValidatedToday    = $this->wasValidatedCleanToday($room, $today);

                    $existingTask = HousekeepingTask::where('room_id', $room->id)
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

                    // Pick cleaner with fewest tasks today (load-balanced)
                    $assignedTo = null;
                    if (!$cleaners->isEmpty()) {
                        asort($cleanerTaskCounts);
                        $assignedTo = array_key_first($cleanerTaskCounts);
                    }

                    if (!$dryRun) {
                        $room->update(['housekeeping_status' => 'dirty']);
                        $roomsMarkedDirty++;

                        $priority = $isCheckoutToday ? 'high' : ($isOccupied ? 'normal' : 'low');
                        $label    = $isCheckoutToday ? 'HIGH-priority checkout' : ($isOccupied ? 'daily occupied' : 'low-priority dirty');

                        if (!$existingTask) {
                            $taskData = $this->prepareTaskData($room, $today, $isCheckoutToday, $assignedTo, $priority);
                            HousekeepingTask::create($taskData);
                            $tasksCreated++;
                            if ($assignedTo) {
                                $cleanerTaskCounts[$assignedTo]++;
                                $tasksAssigned++;
                                $cleanerName = trim(
                                    ($cleaners->firstWhere('id', $assignedTo)->first_name ?? '') . ' ' .
                                    ($cleaners->firstWhere('id', $assignedTo)->last_name ?? '')
                                );
                                $this->line("Room {$room->room_number}: Marked dirty + {$label} task → {$cleanerName}");
                            } else {
                                $this->line("Room {$room->room_number}: Marked dirty + {$label} task (unassigned – no cleaners available)");
                            }
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
                        $priority    = $isCheckoutToday ? 'high' : ($isOccupied ? 'normal' : 'low');
                        $label       = $isCheckoutToday ? 'checkout clean' : ($isOccupied ? 'daily clean' : 'dirty room clean');
                        $cleanerName = $assignedTo
                            ? trim(($cleaners->firstWhere('id', $assignedTo)->first_name ?? '') . ' ' . ($cleaners->firstWhere('id', $assignedTo)->last_name ?? ''))
                            : 'unassigned';
                        if (!$existingTask) {
                            $this->line("Room {$room->room_number}: Would create {$label} [{$priority}] task → {$cleanerName} (DRY RUN)");
                            $tasksCreated++;
                            if ($assignedTo) $cleanerTaskCounts[$assignedTo]++;
                        } else {
                            $this->line("Room {$room->room_number}: Would mark dirty, task already exists (DRY RUN)");
                        }
                        $roomsMarkedDirty++;
                    }

                } catch (\Exception $e) {
                    $this->error("Error processing room {$room->room_number}: " . $e->getMessage());
                    $exceptions++;
                }
            }

            $this->newLine();
            $this->info('=== Daily Cleaning Task Generation Summary ===');
            $this->info("Date: {$today->format('Y-m-d')}");
            $this->info("Occupied/Checkout Rooms Found: {$rooms->count()}");
            $this->info("Rooms Marked Dirty: {$roomsMarkedDirty}");
            $this->info("Tasks Created: {$tasksCreated}");
            $this->info("Tasks Auto-Assigned: {$tasksAssigned}");
            $this->info("Skipped (no-clean request / already validated): {$skipped}");
            $this->info("Exceptions: {$exceptions}");
            if (!$cleaners->isEmpty()) {
                $this->newLine();
                $this->info('Cleaner workload after run:');
                foreach ($cleaners as $c) {
                    $count = $cleanerTaskCounts[$c->id] ?? 0;
                    $this->line("  " . trim($c->first_name . ' ' . $c->last_name) . ": {$count} task(s)");
                }
            }

            if ($dryRun) {
                $this->info('DRY RUN COMPLETED - No actual changes made');
            } else {
                $this->info('Daily cleaning task generation completed successfully');
            }

        } catch (\Exception $e) {
            $this->error("Fatal error in cleaning task generation: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Check if guest has requested no cleaning for this room today
     */
    private function hasGuestRequestedNoCleaning(Room $room, Carbon $date): bool
    {
        // Check current reservation for no cleaning preference
        $currentReservation = $room->reservations()
            ->where('status', 'checked_in')
            ->whereDate('check_in_date', '<=', $date)
            ->whereDate('check_out_date', '>=', $date)
            ->first();

        if ($currentReservation) {
            // Check housekeeping preferences
            $preferences = $currentReservation->guest_preferences ?? [];
            if (is_string($preferences)) {
                $preferences = json_decode($preferences, true);
            }
            
            return isset($preferences['housekeeping_preferences']) && 
                   in_array('no_cleaning', (array)$preferences['housekeeping_preferences']);
        }

        return false;
    }

    /**
     * Check if room was validated as clean today
     */
    private function wasValidatedCleanToday(Room $room, Carbon $date): bool
    {
        // Check if there's a completed and validated cleaning task today
        $validatedTask = HousekeepingTask::where('room_id', $room->id)
            ->where('task_type', 'cleaning')
            ->where('status', 'completed')
            ->where('validation_status', 'validated')
            ->whereDate('validation_timestamp', $date)
            ->first();

        return $validatedTask !== null;
    }

    /**
     * Prepare task data for cleaning task creation
     */
    private function prepareTaskData(Room $room, Carbon $date, bool $isCheckoutToday = false, ?int $assignedTo = null, string $priority = 'normal'): array
    {
        $scheduledTime = $isCheckoutToday ? '10:00:00' : '09:00:00';

        // If checking out today, schedule cleaning 30 min after expected checkout time
        if ($isCheckoutToday) {
            $currentReservation = $room->reservations()
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->whereDate('check_out_date', $date)
                ->first();

            if ($currentReservation) {
                $checkoutTime  = $currentReservation->check_out_time ?? '12:00';
                $scheduledTime = Carbon::parse($checkoutTime)->addMinutes(30)->format('H:i:s');
            }
        }

        $features            = $room->features ?? [];
        $specialInstructions = $this->generateSpecialInstructions($room, $features, $isCheckoutToday);

        return [
            'room_id'           => $room->id,
            'assigned_to'       => $assignedTo,
            'task_type'         => 'cleaning',
            'priority'          => $priority,
            'status'            => 'pending',
            'scheduled_date'    => $date->format('Y-m-d'),
            'scheduled_time'    => $scheduledTime,
            'instructions'      => $specialInstructions,
            'estimated_minutes' => $this->estimateCleaningTime($room, $features),
            'checklist_items'   => $this->getDefaultChecklist($room, $features),
        ];
    }

    /**
     * Generate special instructions based on room features and status
     */
    private function generateSpecialInstructions(Room $room, array $features, bool $isCheckoutToday = false): string
    {
        $instructions = [];

        if ($room->roomType) {
            $instructions[] = "Room Type: {$room->roomType->name}";
        }

        if ($isCheckoutToday) {
            $instructions[] = "Checkout room – full deep clean required.";
            $instructions[] = "Change all linens and towels.";
            $instructions[] = "Check and restock all amenities.";
        } else {
            $instructions[] = "Occupied room – daily refresh while guest is staying.";
            $instructions[] = "Respect guest belongings – do not move personal items.";
        }

        if (in_array('balcony', $features)) {
            $instructions[] = "Clean balcony and outdoor furniture.";
        }

        if (in_array('kitchenette', $features)) {
            $instructions[] = "Clean kitchenette and restock supplies.";
        }

        if (in_array('jacuzzi', $features)) {
            $instructions[] = "Clean and sanitize jacuzzi.";
        }

        return implode("\n", $instructions);
    }

    /**
     * Estimate cleaning time based on room features
     */
    private function estimateCleaningTime(Room $room, array $features): int
    {
        $baseTime = 30; // Base cleaning time in minutes

        // Add time for special features
        $featureTimes = [
            'balcony' => 10,
            'kitchenette' => 15,
            'jacuzzi' => 20,
            'kitchen' => 25,
            'living_area' => 15,
            'office' => 10,
        ];

        foreach ($featureTimes as $feature => $time) {
            if (in_array($feature, $features)) {
                $baseTime += $time;
            }
        }

        // Adjust for room size if available
        if ($room->roomType) {
            switch ($room->roomType->name) {
                case 'Suite':
                case 'Presidential Suite':
                    $baseTime += 20;
                    break;
                case 'Deluxe Room':
                    $baseTime += 10;
                    break;
            }
        }

        return $baseTime;
    }

    /**
     * Get default checklist items for room cleaning
     */
    private function getDefaultChecklist(Room $room, array $features): array
    {
        $checklist = [
            'bed_made' => false,
            'bathroom_cleaned' => false,
            'towels_replaced' => false,
            'linens_changed' => false,
            'dusting_done' => false,
            'vacuuming_done' => false,
            'trash_removed' => false,
            'amenities_checked' => false,
            'tv_tested' => false,
            'lights_tested' => false,
            'air_conditioning_tested' => false,
            'wifi_tested' => false,
        ];

        // Add feature-specific checklist items
        if (in_array('kitchenette', $features) || in_array('kitchen', $features)) {
            $checklist['mini_bar_checked'] = false;
            $checklist['safe_tested'] = false;
        }

        if (in_array('balcony', $features)) {
            $checklist['balcony_cleaned'] = false;
        }

        if (in_array('jacuzzi', $features)) {
            $checklist['jacuzzi_cleaned'] = false;
        }

        return $checklist;
    }
}
