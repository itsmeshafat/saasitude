<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GracePeriodExpiryJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $subscriptionId) {}

    public function handle(): void
    {
        $subscription = Subscription::find($this->subscriptionId);

        if (! $subscription || $subscription->status !== 'past_due') {
            return;
        }

        $freePlan = Plan::where('slug', 'free')->first();

        if ($freePlan) {
            $subscription->update(['plan_id' => $freePlan->id]);
        }

        $subscription->expire();
    }
}
