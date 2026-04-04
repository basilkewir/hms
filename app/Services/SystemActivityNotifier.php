<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemActivityNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SystemActivityNotifier
{
    public function notifyRoles(
        array $roles,
        string $event,
        string $title,
        string $body,
        array $actionUrls = [],
        array $metadata = [],
        ?User $excludeUser = null,
    ): void {
        $recipients = $this->usersWithRoles($roles, $excludeUser);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new SystemActivityNotification($event, $title, $body, $actionUrls, $metadata)
        );
    }

    public function usersWithRoles(array $roles, ?User $excludeUser = null): Collection
    {
        $recipients = User::query()
            ->whereHas('roles', function ($query) use ($roles) {
                $query->whereIn('name', $roles);
            })
            ->get()
            ->unique('id')
            ->values();

        if (!$excludeUser) {
            return $recipients;
        }

        return $recipients
            ->reject(fn (User $user) => (int) $user->id === (int) $excludeUser->id)
            ->values();
    }
}