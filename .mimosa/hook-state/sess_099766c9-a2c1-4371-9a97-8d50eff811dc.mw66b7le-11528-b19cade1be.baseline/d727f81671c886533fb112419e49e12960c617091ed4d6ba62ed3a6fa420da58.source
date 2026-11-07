<?php

namespace App\Http\Controllers;

use App\Gateways\SSLCommerzGateway;
use App\Jobs\ProcessSSLCommerzWebhookJob;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SSLCommerzWebhookController extends Controller
{
    public function handle(Request $request, SSLCommerzGateway $gateway): Response
    {
        try {
            $event = $gateway->verifyWebhook($request);
        } catch (\RuntimeException $e) {
            abort(400, $e->getMessage());
        }

        $webhookEvent = WebhookEvent::firstOrCreate(
            ['gateway' => 'sslcommerz', 'event_id' => $event['notification_id']],
            ['payload' => $event, 'status' => 'pending'],
        );

        if ($webhookEvent->wasRecentlyCreated) {
            ProcessSSLCommerzWebhookJob::dispatch($webhookEvent->id)->onQueue('billing');
        }

        return response('', 200);
    }
}
