<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;

class ReservationStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reservation;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation, string $oldStatus, string $newStatus)
    {
        $this->reservation = $reservation;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusText = $this->getStatusText($this->newStatus);
        
        return new Envelope(
            subject: "Reservation Status Updated - {$statusText} - #{$this->reservation->reservation_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-status-changed',
            with: [
                'reservation' => $this->reservation,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'oldStatusText' => $this->getStatusText($this->oldStatus),
                'newStatusText' => $this->getStatusText($this->newStatus),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get human-readable status text
     */
    protected function getStatusText(string $status): string
    {
        return match($status) {
            'pending' => 'Pending Confirmation',
            'confirmed' => 'Confirmed',
            'checked_in' => 'Checked In',
            'checked_out' => 'Checked Out',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'modified' => 'Modified',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
