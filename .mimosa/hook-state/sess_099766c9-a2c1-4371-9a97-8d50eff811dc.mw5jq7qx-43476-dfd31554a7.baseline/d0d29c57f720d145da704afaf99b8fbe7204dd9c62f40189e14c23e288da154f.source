<?php

namespace App\Services;

use App\Contracts\PushNotificationDriver;
use App\Models\AppNotification;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private readonly PushNotificationDriver $push,
        private readonly SettingsService $settings,
    ) {}

    /**
     * Create an in-app notification and dispatch a push to the user's
     * registered devices (when enabled for this type). Delivery failures
     * are logged, never propagated — a broken push must not break a flow.
     */
    public function send(User $user, string $type, string $title, array $data = [], ?string $actionUrl = null, ?Workspace $workspace = null): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace?->id,
            'type' => $type,
            'title' => $title,
            'body' => $data['body'] ?? null,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);

        if ($this->wantsPush($user, $type)) {
            $user->devices()->each(function ($device) use ($title, $data) {
                try {
                    $this->push->send($device, $title, (string) ($data['body'] ?? ''), $data);
                } catch (\Throwable $e) {
                    Log::warning('push.failed', ['device' => $device->id, 'error' => $e->getMessage()]);
                }
            });
        }

        return $notification;
    }

    /** Per-type push preference, stored in user settings ("notify.{type}.push"). */
    private function wantsPush(User $user, string $type): bool
    {
        $value = $this->settings->get("user.{$user->id}.notify.{$type}.push");

        return $value === null ? true : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
