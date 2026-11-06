<?php

namespace App\Jobs;

use App\Gateways\SSLCommerzGateway;
use App\Models\WebhookEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSSLCommerzWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public readonly int $webhookEventId) {}

    public function handle(SSLCommerzGateway $gateway): void
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
