<?php

namespace App\Console\Commands;

use App\Mail\BookingConfirmation;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TestBookingEmail extends Command
{
    protected $signature = 'booking:test-email
                            {--email= : Recipient email address (defaults to MAIL_FROM_ADDRESS)}
                            {--room-type= : Room type ID to use (defaults to first active type)}
                            {--send : Actually send via SMTP (skip queue)}';

    protected $description = 'Send a test booking confirmation email to verify the full SMTP flow';

    public function handle(): int
    {
        $recipientEmail = $this->option('email') ?? config('mail.from.address');
        $roomTypeId     = $this->option('room-type');

        $hotelName = \App\Models\Setting::get('hotel_name', config('app.name'));
        $this->info($hotelName . ' — Booking Confirmation Email Test');
        $this->line('─────────────────────────────────────────────');

        // ── Find a room type ───────────────────────────────────────────────
        $roomType = $roomTypeId
            ? RoomType::find($roomTypeId)
            : RoomType::where('is_active', true)->first();

        if (!$roomType) {
            $this->error('No active room type found. Run database seeders first.');
            return self::FAILURE;
        }
        $this->line("  Room type   : {$roomType->name} (#{$roomType->id})");

        // ── Find or create a dummy room of that type ───────────────────────
        $room = Room::where('room_type_id', $roomType->id)
            ->where('status', 'available')
            ->where('is_active', true)
            ->first();

        if (!$room) {
            $this->warn('  No available room found for this type — the email will show room type only.');
            $room = null;
        } else {
            $this->line("  Room        : #{$room->room_number}");
        }

        // ── Build and persist a test reservation (rolled back after email send) ──
        // We must persist because SerializesModels reloads by ID in the queue worker.
        $confirmationToken = bin2hex(random_bytes(16));
        $reservation       = null;
        $guest             = null;

        DB::beginTransaction();
        try {
            $systemUserId = DB::table('users')->min('id') ?? 1;
            $guest = Guest::firstOrCreate(
                ['email' => 'booking-test-dummy@donzebe.internal'],
                [
                    'first_name'                  => 'John',
                    'last_name'                   => 'Doe',
                    'phone'                       => '+237 600 000 000',
                    'nationality'                 => 'Cameroonian',
                    'id_type'                     => 'national_id',
                    'id_number'                   => 'TEST-0000',
                    'police_verification_status'  => 'pending',
                    'guest_type_id'               => 1,
                    'created_by'                  => $systemUserId,
                ]
            );

            $checkIn  = Carbon::tomorrow();
            $checkOut = Carbon::tomorrow()->addDays(3);
            $nights   = 3;
            $taxRate  = Setting::get('room_tax_rate', Setting::get('tax_rate', 0)) / 100;
            $roomRate = $roomType->base_price;
            $charges  = $roomRate * $nights;
            $taxes    = $charges * $taxRate;
            $total    = $charges + $taxes;

            $reservation = Reservation::create([
                'reservation_number' => 'TEST' . date('Ymd') . strtoupper(bin2hex(random_bytes(3))),
                'confirmation_token' => hash('sha256', $confirmationToken),
                'guest_id'           => $guest->id,
                'room_id'            => $room?->id,
                'room_type_id'       => $roomType->id,
                'check_in_date'      => $checkIn,
                'check_out_date'     => $checkOut,
                'nights'             => $nights,
                'number_of_adults'   => 2,
                'adults'             => 2,
                'number_of_children' => 0,
                'children'           => 0,
                'status'             => 'confirmed',
                'room_rate'          => $roomRate,
                'total_room_charges' => $charges,
                'taxes'              => $taxes,
                'service_charges'    => 0,
                'total_price'        => $total,
                'total_amount'       => $total,
                'paid_amount'        => 0,
                'balance_amount'     => $total,
                'booking_source'     => 'website',
                'special_requests'   => ['High floor preferred', 'Extra pillows please'],
                'created_by'         => $systemUserId,
            ]);

            $reservation->load(['guest', 'room', 'roomType']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Could not create test reservation: ' . $e->getMessage());
            return self::FAILURE;
        }

        // ── Dispatch ───────────────────────────────────────────────────────
        $this->line("  Recipient   : {$recipientEmail}");
        $this->line("  Reservation : #{$reservation->reservation_number} (id={$reservation->id})");
        $this->line("  SMTP host   : " . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->line("  Queue driver: " . config('queue.default'));
        $this->newLine();

        try {
            $mailable = new BookingConfirmation($reservation, $confirmationToken);

            if ($this->option('send')) {
                // Send synchronously — SMTP verified immediately, then clean up test data
                Mail::to($recipientEmail, 'Test Guest')->send($mailable);
                $reservation->delete();
                if ($guest->wasRecentlyCreated) {
                    $guest->delete();
                }
                $this->info('✓ Email sent via SMTP successfully. Test data cleaned up.');
            } else {
                // Queue via the configured driver — reservation must stay in DB until worker runs
                Mail::to($recipientEmail, 'Test Guest')->queue($mailable);
                $this->info('✓ Email queued successfully.');
                if (config('queue.default') === 'database') {
                    $pending = DB::table('jobs')->count();
                    $this->line("  Jobs in queue: {$pending}");
                    $this->newLine();
                    $this->comment('Run  php artisan queue:work --once  to process it.');
                    $this->comment("Clean up test data after:  Reservation::find({$reservation->id})?->delete();");
                }
            }
        } catch (\Exception $e) {
            // Clean up test reservation on failure too
            $reservation?->delete();
            if ($guest?->wasRecentlyCreated) {
                $guest->delete();
            }
            $this->error('✗ Failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }

        $this->newLine();
        if (config('mail.mailers.smtp.host') === 'mailpit') {
            $this->comment('Open http://localhost:8025 to view the email in Mailpit.');
        }

        return self::SUCCESS;
    }
}
