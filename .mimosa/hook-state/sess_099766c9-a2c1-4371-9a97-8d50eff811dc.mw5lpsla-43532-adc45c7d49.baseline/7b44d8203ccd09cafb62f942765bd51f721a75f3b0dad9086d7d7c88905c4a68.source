<?php

namespace App\Http\Controllers;

use App\Gateways\PaddleGateway;
use App\Jobs\ProcessPaddleWebhookJob;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaddleWebhookController extends Controller
{
    public function handle(Request $request, PaddleGateway $gateway): Response
    {
        try {
            $event = $gateway->verifyWebhook($request);
        } catch (\RuntimeException) {
            abort(400, 'Invalid signature');
        }

        $notificationId = $event['notification_id'] ?? $event['event_id'] ?? null;

        if (! $notificationId) {
            return response('', 200);
        }

        $webhookEvent = WebhookEvent::firstOrCreate(
            ['gateway' => 'paddle', 'event_id' => $notificationId],
            ['payload' => $event, 'status' => 'pending'],
        );

        if ($webhookEvent->wasRecentlyCreated) {
            ProcessPaddleWebhookJob::dispatch($webhookEvent->id)->onQueue('billing');
        }

        return response('', 200);
    }
}
