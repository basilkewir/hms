<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReservationSummary;

class ReservationDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:daily-summary {--email= : Email address to send summary to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send daily reservation summary report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating daily reservation summary...');
        
        $summary = $this->generateDailySummary();
        
        // Send to specified email or default management emails
        $email = $this->option('email') ?: $this->getManagementEmails();
        
        if ($email) {
            Mail::to($email)->send(new DailyReservationSummary($summary));
            $this->info("Daily summary sent to: {$email}");
        }
        
        // Log the summary
        Log::info('Daily reservation summary generated', $summary);
        
        $this->info('Daily reservation summary completed.');
    }

    /**
     * Generate daily summary data
     */
    protected function generateDailySummary()
    {
        $today = today();
        
        return [
            'date' => $today->format('Y-m-d'),
            'date_formatted' => $today->format('F j, Y'),
            
            // Today's arrivals
            'arrivals' => Reservation::whereDate('check_in_date', $today)
                ->where('status', 'confirmed')
                ->count(),
            
            'arrivals_checked_in' => Reservation::whereDate('check_in_date', $today)
                ->where('status', 'checked_in')
                ->count(),
            
            // Today's departures
            'departures' => Reservation::whereDate('check_out_date', $today)
                ->where('status', 'checked_in')
                ->count(),
            
            'departures_checked_out' => Reservation::whereDate('check_out_date', $today)
                ->where('status', 'checked_out')
                ->count(),
            
            // No-shows today
            'no_shows' => Reservation::whereDate('check_in_date', $today)
                ->where('status', 'no_show')
                ->count(),
            
            // Cancellations today
            'cancellations' => Reservation::whereDate('cancelled_at', $today)
                ->count(),
            
            // Current occupancy
            'current_occupancy' => Reservation::where('status', 'checked_in')->count(),
            
            // Pending reservations
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            
            // Modified reservations
            'modified_reservations' => Reservation::where('status', 'modified')->count(),
            
            // Revenue summary
            'total_room_revenue' => Reservation::whereDate('created_at', $today)
                ->sum('total_room_charges'),
            
            'total_revenue' => Reservation::whereDate('created_at', $today)
                ->sum('total_amount'),
            
            // Automation summary
            'automation_summary' => $this->getAutomationSummary($today),
        ];
    }

    /**
     * Get automation summary for today
     */
    protected function getAutomationSummary($today)
    {
        return [
            'auto_checkouts' => DB::table('reservation_logs')
                ->whereDate('created_at', $today)
                ->where('reason', 'like', '%Automated check-out%')
                ->count(),
            
            'auto_no_shows' => DB::table('reservation_logs')
                ->whereDate('created_at', $today)
                ->where('reason', 'like', '%Automated no-show%')
                ->count(),
            
            'auto_cancellations' => DB::table('reservation_logs')
                ->whereDate('created_at', $today)
                ->where('reason', 'like', '%Automated cancellation%')
                ->count(),
        ];
    }

    /**
     * Get management email addresses
     */
    protected function getManagementEmails()
    {
        // Get users with management roles
        $managementUsers = User::role(['admin', 'manager'])->get();
        
        return $managementUsers->pluck('email')->filter()->toArray();
    }
}
