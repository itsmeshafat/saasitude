<?php

namespace App\Providers;

use App\Actions\CreateWorkspace;
use App\Events\PaymentFailed;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCanceled;
use App\Mail\PaymentFailedMail;
use App\Mail\PlanChanged;
use App\Mail\SubscriptionCanceledMail;
use App\Mail\TrialStarted;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class WorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(Registered::class, function (Registered $event): void {
            // The framework event carries Authenticatable; only our User has workspaces.
            if (! $event->user instanceof User) {
                return;
            }

            app(CreateWorkspace::class)->execute($event->user, $event->user->name, true);
        });

        // Transactional billing emails (queued) + in-app notifications.
        Event::listen(SubscriptionActivated::class, function (SubscriptionActivated $event): void {
            $subscription = $event->subscription->loadMissing('workspace.owner', 'plan');
            $owner = $subscription->workspace->owner;

            if ($subscription->trial_ends_at !== null && $subscription->trial_ends_at->isFuture()) {
                Mail::to($owner)->queue(new TrialStarted($subscription));
            } else {
                Mail::to($owner)->queue(new PlanChanged($subscription));
            }

            app(NotificationService::class)->send(
                $owner,
                $subscription->status === 'trialing' ? 'trial.started' : 'plan.changed',
                $subscription->status === 'trialing' ? 'Your trial has started' : 'Your plan is active',
                ['body' => "Your {$subscription->plan->name} subscription for {$subscription->workspace->name} is {$subscription->status}."],
                route('dashboard.billing'),
                $subscription->workspace,
            );
        });

        Event::listen(PaymentFailed::class, function (PaymentFailed $event): void {
            $subscription = $event->subscription->loadMissing('workspace.owner', 'plan');
            $owner = $subscription->workspace->owner;

            Mail::to($owner)->queue(new PaymentFailedMail($subscription));

            app(NotificationService::class)->send(
                $owner, 'payment.failed', 'Payment failed',
                ['body' => 'Update your payment method to avoid an interruption in service.'],
                route('dashboard.billing'), $subscription->workspace,
            );
        });

        Event::listen(SubscriptionCanceled::class, function (SubscriptionCanceled $event): void {
            $subscription = $event->subscription->loadMissing('workspace.owner', 'plan');

            Mail::to($subscription->workspace->owner)->queue(new SubscriptionCanceledMail($subscription));
        });
    }
}
