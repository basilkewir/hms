<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;
use App\Models\Setting;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $confirmationToken;
    public array $hotel;

    public function __construct(Reservation $reservation, string $confirmationToken)
    {
        $this->reservation      = $reservation;
        $this->confirmationToken = $confirmationToken;
        $this->hotel = [
            'name'    => Setting::get('hotel_name', config('app.name')),
            'address' => Setting::get('hotel_address', 'Yaoundé, Cameroon'),
            'phone'   => Setting::get('hotel_phone',   ''),
            'email'   => Setting::get('hotel_email',   ''),
            'website' => Setting::get('hotel_website', 'https://donzebemanagement.qzz.io'),
            'currency' => Setting::get('currency',     'XAF'),
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                $this->hotel['name'],
            ),
            subject: 'Booking Confirmed – ' . $this->reservation->reservation_number . ' | ' . $this->hotel['name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'reservation'      => $this->reservation,
                'confirmationToken' => $this->confirmationToken,
                'hotel'            => $this->hotel,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
