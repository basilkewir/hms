<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationStatusChanged;

class AutomateReservationStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:automate-statuses {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update reservation statuses based on time and conditions';

    /**
     * Automation configuration
     */
    protected $config;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->config = $this->getAutomationConfig();
        
        $this->info('Starting reservation status automation...');
        
        // Process different automation rules
        $this->processNoShowReservations();
        $this->processAutoCheckOut();
        $this->processPendingConfirmations();
        $this->processModifiedReservations();
        $this->releaseRoomsForNoShows();
        
        $this->info('Reservation status automation completed.');
    }

    /**
     * Get automation configuration
     */
    protected function getAutomationConfig()
    {
        return [
            'no_show_hours' => config('hotel.automation.no_show_hours', 2), // Hours after check-in time to mark as no-show
            'auto_checkout_hour' => config('hotel.automation.auto_checkout_hour', 11), // Hour to auto-checkout (11 AM)
            'pending_confirmation_hours' => config('hotel.automation.pending_confirmation_hours', 24), // Hours to wait before cancelling pending
            'enable_notifications' => config('hotel.automation.enable_notifications', true),
            'dry_run' => $this->option('dry-run'),
        ];
    }

    /**
     * Process reservations that should be marked as no-show
     */
    protected function processNoShowReservations()
    {
        $this->info('Processing no-show reservations...');
        
        $cutoffTime = now()->subHours($this->config['no_show_hours']);
        
        $reservations = Reservation::where('status', 'confirmed')
            ->where('check_in_date', today())
            ->whereNull('actual_check_in')
            ->where(function($query) use ($cutoffTime) {
                $query->whereNull('preferred_check_in_time')
                      ->orWhere('preferred_check_in_time', '<=', $cutoffTime->format('H:i'));
            })
            ->get();

        foreach ($reservations as $reservation) {
            if (!$this->config['dry_run']) {
                $this->markAsNoShow($reservation);
            }
            
            $this->line("Marked reservation #{$reservation->reservation_number} as no-show");
        }
        
        $this->info("Processed {$reservations->count()} no-show reservations");
    }

    /**
     * Process automatic check-outs
     */
    protected function processAutoCheckOut()
    {
        $this->info('Processing automatic check-outs...');
        
        $autoCheckoutTime = now()->setHour($this->config['auto_checkout_hour'])->setMinute(0)->setSecond(0);
        
        // Only process if we're past the auto-checkout time
        if (now()->lt($autoCheckoutTime)) {
            $this->info('Auto-checkout time not reached yet');
            return;
        }
        
        $reservations = Reservation::where('status', 'checked_in')
            ->where('check_out_date', '<', today())
            ->orWhere(function($query) {
                $query->where('check_out_date', today())
                      ->where('actual_check_out', null);
            })
            ->get();

        foreach ($reservations as $reservation) {
            if (!$this->config['dry_run']) {
                $this->autoCheckOut($reservation);
            }
            
            $this->line("Auto-checked out reservation #{$reservation->reservation_number}");
        }
        
        $this->info("Processed {$reservations->count()} auto check-outs");
    }

    /**
     * Process pending reservations that should be cancelled
     */
    protected function processPendingConfirmations()
    {
        $this->info('Processing pending confirmation cancellations...');
        
        $cutoffTime = now()->subHours($this->config['pending_confirmation_hours']);
        
        $reservations = Reservation::where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();

        foreach ($reservations as $reservation) {
            if (!$this->config['dry_run']) {
                $this->cancelPendingReservation($reservation);
            }
            
            $this->line("Cancelled pending reservation #{$reservation->reservation_number}");
        }
        
        $this->info("Processed {$reservations->count()} pending cancellations");
    }

    /**
     * Process modified reservations to confirm changes
     */
    protected function processModifiedReservations()
    {
        $this->info('Processing modified reservations...');
        
        $reservations = Reservation::where('status', 'modified')
            ->where('updated_at', '<', now()->subHours(2)) // Give 2 hours for review
            ->get();

        foreach ($reservations as $reservation) {
            if (!$this->config['dry_run']) {
                $this->confirmModifiedReservation($reservation);
            }
            
            $this->line("Confirmed modified reservation #{$reservation->reservation_number}");
        }
        
        $this->info("Processed {$reservations->count()} modified reservations");
    }

    /**
     * Release rooms for no-show reservations
     */
    protected function releaseRoomsForNoShows()
    {
        $this->info('Releasing rooms for no-show reservations...');
        
        $noShowReservations = Reservation::where('status', 'no_show')
            ->whereHas('room', function($query) {
                $query->where('status', 'reserved');
            })
            ->get();

        foreach ($noShowReservations as $reservation) {
            if (!$this->config['dry_run'] && $reservation->room) {
                $reservation->room->update(['status' => 'available']);
                
                // Create housekeeping task to prepare room
                $this->createHousekeepingTask($reservation->room, 'no_show_cleanup');
            }
            
            $this->line("Released room {$reservation->room->room_number} from no-show reservation");
        }
        
        $this->info("Released {$noShowReservations->count()} rooms");
    }

    /**
     * Mark reservation as no-show
     */
    protected function markAsNoShow(Reservation $reservation)
    {
        DB::transaction(function() use ($reservation) {
            $oldStatus = $reservation->status;
            
            $reservation->update([
                'status' => 'no_show',
                'cancelled_at' => now(),
                'cancelled_by' => 1, // System user ID
                'cancellation_reason' => 'Automated no-show - guest did not check in by required time',
                'cancellation_charges' => $this->calculateNoShowCharges($reservation),
            ]);

            // Log the status change
            $this->logStatusChange($reservation, $oldStatus, 'no_show', 'Automated no-show');

            // Send notification if enabled
            if ($this->config['enable_notifications']) {
                $this->sendStatusChangeNotification($reservation, $oldStatus, 'no_show');
            }
        });
    }

    /**
     * Auto check-out reservation
     */
    protected function autoCheckOut(Reservation $reservation)
    {
        DB::transaction(function() use ($reservation) {
            $oldStatus = $reservation->status;
            
            $reservation->update([
                'status' => 'checked_out',
                'actual_check_out' => now(),
                'checked_out_by' => 1, // System user ID
            ]);

            // Update room status
            if ($reservation->room) {
                $reservation->room->update(['status' => 'needs_cleaning']);
                
                // Create housekeeping task
                $this->createHousekeepingTask($reservation->room, 'checkout_cleaning');
            }

            // Log the status change
            $this->logStatusChange($reservation, $oldStatus, 'checked_out', 'Automated check-out');

            // Send notification if enabled
            if ($this->config['enable_notifications']) {
                $this->sendStatusChangeNotification($reservation, $oldStatus, 'checked_out');
            }
        });
    }

    /**
     * Cancel pending reservation
     */
    protected function cancelPendingReservation(Reservation $reservation)
    {
        DB::transaction(function() use ($reservation) {
            $oldStatus = $reservation->status;
            
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => 1, // System user ID
                'cancellation_reason' => 'Automated cancellation - reservation not confirmed within time limit',
            ]);

            // Release room if assigned
            if ($reservation->room) {
                $reservation->room->update(['status' => 'available']);
            }

            // Log the status change
            $this->logStatusChange($reservation, $oldStatus, 'cancelled', 'Automated cancellation');

            // Send notification if enabled
            if ($this->config['enable_notifications']) {
                $this->sendStatusChangeNotification($reservation, $oldStatus, 'cancelled');
            }
        });
    }

    /**
     * Confirm modified reservation
     */
    protected function confirmModifiedReservation(Reservation $reservation)
    {
        DB::transaction(function() use ($reservation) {
            $oldStatus = $reservation->status;
            
            $reservation->update([
                'status' => 'confirmed',
            ]);

            // Log the status change
            $this->logStatusChange($reservation, $oldStatus, 'confirmed', 'Automated confirmation of modified reservation');

            // Send notification if enabled
            if ($this->config['enable_notifications']) {
                $this->sendStatusChangeNotification($reservation, $oldStatus, 'confirmed');
            }
        });
    }

    /**
     * Calculate no-show charges
     */
    protected function calculateNoShowCharges(Reservation $reservation)
    {
        // Typically first night's room rate
        return $reservation->room_rate;
    }

    /**
     * Create housekeeping task
     */
    protected function createHousekeepingTask(Room $room, string $taskType)
    {
        // This would integrate with your existing housekeeping system
        // Implementation depends on your housekeeping task structure
    }

    /**
     * Log status change
     */
    protected function logStatusChange(Reservation $reservation, string $oldStatus, string $newStatus, string $reason)
    {
        Log::info("Reservation #{$reservation->reservation_number} status changed", [
            'reservation_id' => $reservation->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'automated' => true,
            'timestamp' => now(),
        ]);
    }

    /**
     * Send status change notification
     */
    protected function sendStatusChangeNotification(Reservation $reservation, string $oldStatus, string $newStatus)
    {
        try {
            // Send email to guest
            if ($reservation->guest && $reservation->guest->email) {
                Mail::to($reservation->guest->email)->send(
                    new ReservationStatusChanged($reservation, $oldStatus, $newStatus)
                );
            }

            // Send notification to front desk staff
            $frontDeskUsers = User::role('front_desk')->get();
            foreach ($frontDeskUsers as $user) {
                // Send notification to front desk users
                // This could be email, in-app notification, etc.
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for reservation #{$reservation->reservation_number}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
