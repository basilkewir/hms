<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $event,
        private readonly string $title,
        private readonly string $body,
        private readonly array $actionUrls = [],
        private readonly array $metadata = [],
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge($this->metadata, [
            'event' => $this->event,
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->resolveActionUrl($notifiable),
        ]);
    }

    private function resolveActionUrl(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'hasRole')) {
            foreach ($this->actionUrls as $role => $url) {
                if ($role === 'default') {
                    continue;
                }

                if ($notifiable->hasRole($role)) {
                    return $url;
                }
            }
        }

        return $this->actionUrls['default'] ?? null;
    }
}