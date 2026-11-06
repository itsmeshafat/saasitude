<?php

namespace App\Services\Push;

use App\Contracts\PushNotificationDriver;
use App\Models\Device;
use RuntimeException;

/**
 * Stub FCM driver — logs instead of hitting the FCM API. Swap with a real
 * HTTP implementation (or another provider) by rebinding
 * PushNotificationDriver::class in AppServiceProvider.
 */
class FCMDriver implements PushNotificationDriver
{
    public function send(Device $device, string $title, string $body, array $data = []): void
    {
        // ponytail: real FCM HTTP v1 call — enable when FCM credentials exist
        logger()->info('push.fcm', [
            'token' => substr($device->push_token, 0, 8).'…',
            'platform' => $device->platform,
            'title' => $title,
            'body' => $body,
        ]);

        if (app()->environment('production') && ! config('services.fcm.enabled')) {
            throw new RuntimeException('FCM is not configured.');
        }
    }
}
