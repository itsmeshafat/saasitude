<?php

namespace App\Contracts;

use App\Models\Device;

interface PushNotificationDriver
{
    /** Send a push to one registered device. Throws on delivery failure. */
    public function send(Device $device, string $title, string $body, array $data = []): void;
}
