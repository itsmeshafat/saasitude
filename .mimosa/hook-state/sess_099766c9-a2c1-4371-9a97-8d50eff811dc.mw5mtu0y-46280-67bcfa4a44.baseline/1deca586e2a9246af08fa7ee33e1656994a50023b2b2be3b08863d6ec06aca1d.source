<?php

namespace App\Actions;

use App\Models\Workspace;
use App\Services\GatewayManager;
use HasinHayder\Tyro\Support\TyroAudit;
use RuntimeException;

class CancelSubscription
{
    public function __construct(private GatewayManager $gateways) {}

    /**
     * Cancel a workspace's active subscription at period end (default) or
     * immediately, recording the cancellation reason on subscription metadata.
     */
    public function execute(Workspace $workspace, string $reason, bool $immediately = false): void
    {
        $subscription = $workspace->activeSubscription();

        if (! $subscription) {
            throw new RuntimeException('This workspace has no active subscription to cancel.');
        }

        if (session('impersonator_id')) {
            throw new RuntimeException('Billing actions are blocked while impersonating a user.');
        }

        $gateway = $this->gateways->resolve($subscription->gateway);

        $metadata = $subscription->metadata ?? [];
        $metadata['cancellation_reason'] = $reason;
        $metadata['cancelled_by'] = auth()->id();
        $subscription->metadata = $metadata;
        $subscription->save();

        $gateway->cancel($subscription, atPeriodEnd: ! $immediately);

        TyroAudit::log('subscription.canceled', $subscription, [
            'reason' => $reason,
            'immediately' => $immediately,
        ], null);
    }
}
