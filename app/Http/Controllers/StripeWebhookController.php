<?php

namespace App\Http\Controllers;

use App\Gateways\StripeGateway;
use App\Jobs\ProcessStripeWebhookJob;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripeGateway $gateway): Response
    {
        try {
            $event = $gateway->verifyWebhook($request);
        } catch (SignatureVerificationException) {
            abort(400, 'Invalid signature');
        }

        // ADR-008: idempotency via unique(gateway, event_id)
        [$webhookEvent, $created] = [
            WebhookEvent::firstOrCreate(
                ['gateway' => 'stripe', 'event_id' => $event['id']],
                ['payload' => $event, 'status' => 'pending']
            ),
            false,
        ];

        // Check if it was actually created (not a duplicate)
        if ($webhookEvent->wasRecentlyCreated) {
            ProcessStripeWebhookJob::dispatch($webhookEvent->id)->onQueue('billing');
        }

        return response('', 200);
    }
}
