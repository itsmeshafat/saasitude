<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaddleWebhookJob;
use App\Jobs\ProcessSSLCommerzWebhookJob;
use App\Jobs\ProcessStripeWebhookJob;
use App\Models\WebhookEvent;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookEventsController extends Controller
{
    public function index(Request $request): View
    {
        $events = WebhookEvent::query()
            ->when($request->query('gateway'), fn ($q, $gateway) => $q->where('gateway', $gateway))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.webhook-events', [
            'events' => $events,
            'gateways' => ['stripe', 'paddle', 'sslcommerz'],
            'statuses' => ['pending', 'processed', 'failed'],
            'filters' => [
                'gateway' => (string) $request->query('gateway', ''),
                'status' => (string) $request->query('status', ''),
            ],
        ]);
    }

    public function show(WebhookEvent $event): View
    {
        return view('dashboard.webhook-events-detail', ['event' => $event]);
    }

    /**
     * Re-queue a stored webhook event. Idempotent: the processing job
     * matches on UNIQUE(gateway, event_id) so a replay of an already
     * processed event is a no-op.
     */
    public function replay(Request $request, WebhookEvent $event): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('webhooks.replay'), 403);

        $job = match ($event->gateway) {
            'stripe' => ProcessStripeWebhookJob::class,
            'paddle' => ProcessPaddleWebhookJob::class,
            'sslcommerz' => ProcessSSLCommerzWebhookJob::class,
            default => null,
        };

        if ($job === null) {
            return redirect()->route('admin.webhook-events.index')
                ->with('error', "Unknown gateway [{$event->gateway}].");
        }

        $event->update(['status' => 'pending', 'error_message' => null]);
        dispatch(new $job($event->id));

        TyroAudit::log('webhook.replayed', $event, null, [
            'gateway' => $event->gateway,
            'event_id' => $event->event_id,
            'admin_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.webhook-events.index')
            ->with('success', 'Event re-queued for processing.');
    }
}
