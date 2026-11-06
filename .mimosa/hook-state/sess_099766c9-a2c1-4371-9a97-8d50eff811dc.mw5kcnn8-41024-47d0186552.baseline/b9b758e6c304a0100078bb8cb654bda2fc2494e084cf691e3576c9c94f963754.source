<?php

namespace App\Jobs;

use App\Gateways\PaddleGateway;
use App\Models\WebhookEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPaddleWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 60;

    public function __construct(public readonly int $webhookEventId) {}

    public function handle(PaddleGateway $gateway): void
    {
        $webhookEvent = WebhookEvent::find($this->webhookEventId);

        if (! $webhookEvent || $webhookEvent->status === 'processed') {
            return;
        }

        try {
            $gateway->handleEvent($webhookEvent->payload);
            $webhookEvent->markProcessed();
        } catch (\Throwable $e) {
            $webhookEvent->markFailed($e->getMessage());
            throw $e;
        }
    }
}
