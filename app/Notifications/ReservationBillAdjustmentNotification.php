<?php

namespace App\Notifications;

use App\Models\GuestBillAdjustmentRequest;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReservationBillAdjustmentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Reservation $reservation,
        private readonly GuestBillAdjustmentRequest $adjustmentRequest,
        private readonly string $event,
        private readonly ?string $actorName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        [$title, $body] = match ($this->event) {
            'requested' => [
                'Bill modification request pending',
                sprintf(
                    'Reservation %s has a new %s request for %s from %s.',
                    $this->reservation->reservation_number,
                    $this->adjustmentRequest->adjustment_type === 'decrease' ? 'bill reduction' : 'bill increase',
                    number_format((float) $this->adjustmentRequest->amount, 2),
                    $this->actorName ?: 'Front Desk'
                ),
            ],
            'approved' => [
                'Bill modification approved',
                sprintf(
                    'Your bill adjustment request for reservation %s was approved by %s.',
                    $this->reservation->reservation_number,
                    $this->actorName ?: 'Management'
                ),
            ],
            'rejected' => [
                'Bill modification rejected',
                sprintf(
                    'Your bill adjustment request for reservation %s was rejected by %s.',
                    $this->reservation->reservation_number,
                    $this->actorName ?: 'Management'
                ),
            ],
            default => [
                'Bill modification update',
                sprintf('Reservation %s has a bill modification update.', $this->reservation->reservation_number),
            ],
        };

        return [
            'event' => $this->event,
            'title' => $title,
            'body' => $body,
            'reservation_id' => $this->reservation->id,
            'reservation_number' => $this->reservation->reservation_number,
            'bill_adjustment_request_id' => $this->adjustmentRequest->id,
            'action_url' => $this->resolveActionUrl($notifiable),
        ];
    }

    private function resolveActionUrl(object $notifiable): string
    {
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('manager') && ! $notifiable->hasRole('admin')) {
            return route('manager.reservations.show', $this->reservation);
        }

        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('front_desk')) {
            return route('front-desk.reservations.show', $this->reservation);
        }

        return route('admin.reservations.show', $this->reservation);
    }
}