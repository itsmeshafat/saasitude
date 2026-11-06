<?php

namespace App\Console\Commands;

use App\Jobs\SubscriptionRenewalReminderJob;
use App\Models\Subscription;
use Illuminate\Console\Command;

class BillingRenewalsCommand extends Command
{
    protected $signature = 'billing:renewals';

    protected $description = 'Dispatch renewal reminder emails for subscriptions expiring soon';

    public function handle(): int
    {
        $remindDays = [7, 3, 1];

        foreach ($remindDays as $days) {
            Subscription::whereIn('status', ['active', 'trialing'])
                ->where('app_driven_renewal', true)
                ->whereDate('current_period_end', now()->addDays($days)->toDateString())
                ->each(fn (Subscription $sub) => SubscriptionRenewalReminderJob::dispatch($sub->id, $days)->onQueue('billing'));
        }

        $this->info('Renewal reminders dispatched.');

        return self::SUCCESS;
    }
}
