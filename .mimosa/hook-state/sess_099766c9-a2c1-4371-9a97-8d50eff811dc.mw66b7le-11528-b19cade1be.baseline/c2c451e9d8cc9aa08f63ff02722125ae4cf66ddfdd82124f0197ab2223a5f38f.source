<?php

namespace Tests\Fakes;

use App\Contracts\PushNotificationDriver;
use App\Models\Device;

/** Records pushes instead of delivering them. */
class FakePushDriver implements PushNotificationDriver
{
    /** @var array<int, array{token: string, title: string, body: string, data: array}> */
    public array $sent = [];

    public function send(Device $device, string $title, string $body, array $data = []): void
    {
        $this->sent[] = ['token' => $device->push_token, 'title' => $title, 'body' => $body, 'data' => $data];
    }
}
