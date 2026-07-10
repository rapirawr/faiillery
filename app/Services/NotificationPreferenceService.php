<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferenceService
{
    /**
     * Retrieve all notification preferences for a user.
     */
    public function getPreferences(User $user): array
    {
        return NotificationPreference::where('user_id', $user->id)
            ->get()
            ->groupBy('channel')
            ->map(function ($items) {
                return $items->pluck('enabled', 'event_type');
            })
            ->toArray();
    }

    /**
     * Check if a specific notification channel & event combination is enabled for a user.
     */
    public function isEnabled(User $user, string $channel, string $eventType): bool
    {
        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('event_type', $eventType)
            ->first();

        // Defaults to true if no preference is explicitly set
        return $preference ? $preference->enabled : true;
    }

    /**
     * Set preference for a specific notification channel and event type.
     */
    public function setPreference(User $user, string $channel, string $eventType, bool $enabled): NotificationPreference
    {
        return NotificationPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'channel' => $channel,
                'event_type' => $eventType,
            ],
            [
                'enabled' => $enabled,
            ]
        );
    }
}
