<?php

namespace App\Jobs;

use App\Gateways\StripeGateway;
use App\Models\WebhookEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessStripeWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 60;

    public function __construct(public readonly int $webhookEventId) {}

    public function handle(StripeGateway $gateway): void
    {
        $webhookEvent = WebhookEvent::findOrFail($this->webhookEventId);

        try {
            $gateway->handleEvent($webhookEvent->payload);
            $webhookEvent->markProcessed();
        } catch (Throwable $e) {
            $webhookEvent->markFailed($e->getMessage());
            throw $e;
        }
    }
}
