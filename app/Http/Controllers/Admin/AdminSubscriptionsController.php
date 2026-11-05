<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriptionsController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = Subscription::query()
            ->with(['workspace.owner', 'plan'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('gateway'), fn ($q, $gateway) => $q->where('gateway', $gateway))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.subscriptions-admin', [
            'subscriptions' => $subscriptions,
            'statuses' => ['trialing', 'active', 'past_due', 'canceled', 'expired'],
            'gateways' => ['stripe', 'paddle', 'sslcommerz', 'manual'],
            'statusCounts' => Subscription::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'filters' => [
                'status' => (string) $request->query('status', ''),
                'gateway' => (string) $request->query('gateway', ''),
            ],
        ]);
    }

    /** State timeline of one subscription (status, period, cancel/trial marks). */
    public function show(Subscription $subscription): View
    {
        $subscription->load(['workspace.owner', 'plan']);

        $timeline = collect([
            ['at' => $subscription->created_at, 'event' => 'Created', 'detail' => "Status {$subscription->status}"],
            ['at' => $subscription->trial_ends_at, 'event' => 'Trial ends', 'detail' => null],
            ['at' => $subscription->current_period_start, 'event' => 'Period start', 'detail' => null],
            ['at' => $subscription->current_period_end, 'event' => 'Period end', 'detail' => null],
            ['at' => $subscription->canceled_at, 'event' => 'Canceled', 'detail' => $subscription->cancel_at_period_end ? 'At period end' : 'Immediately'],
        ])->filter(fn ($entry) => $entry['at'] !== null)
            ->sortBy('at')
            ->values();

        return view('dashboard.subscriptions-admin-detail', [
            'subscription' => $subscription,
            'timeline' => $timeline,
        ]);
    }
}
