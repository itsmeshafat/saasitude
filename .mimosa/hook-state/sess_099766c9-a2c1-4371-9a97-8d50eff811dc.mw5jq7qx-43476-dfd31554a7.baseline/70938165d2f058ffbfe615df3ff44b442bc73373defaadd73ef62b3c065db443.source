<?php

namespace App\Jobs;

use App\Mail\SubscriptionRenewalReminder;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SubscriptionRenewalReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $subscriptionId,
        public readonly int $daysUntilRenewal,
    ) {}

    public function handle(): void
    {
        $subscription = Subscription::with('workspace', 'plan')->find($this->subscriptionId);

        if (! $subscription || ! $subscription->isActive()) {
            return;
        }

        $owner = $subscription->workspace->users()->first();

        if (! $owner) {
            return;
        }

        Mail::to($owner->email)->send(
            new SubscriptionRenewalReminder($subscription, $this->daysUntilRenewal),
        );
    }
}
