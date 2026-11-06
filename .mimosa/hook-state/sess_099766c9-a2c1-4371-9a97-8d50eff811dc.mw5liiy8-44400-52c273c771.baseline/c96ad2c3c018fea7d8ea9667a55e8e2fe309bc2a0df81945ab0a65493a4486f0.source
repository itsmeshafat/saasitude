<?php

use App\Jobs\GracePeriodExpiryJob;
use App\Models\Subscription;
use App\Services\SettingsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billing:renewals')->dailyAt('09:00')->name('billing:renewals');

// Dispatch GracePeriodExpiryJob for past_due subscriptions that have exceeded the grace period
Schedule::call(function (): void {
    $graceDays = (int) app(SettingsService::class)->get('grace_period_days', 5);

    Subscription::where('status', 'past_due')
        ->where('updated_at', '<=', now()->subDays($graceDays))
        ->each(fn (Subscription $sub) => GracePeriodExpiryJob::dispatch($sub->id)->onQueue('billing'));
})->dailyAt('02:00')->name('billing:grace-period-expiry');
