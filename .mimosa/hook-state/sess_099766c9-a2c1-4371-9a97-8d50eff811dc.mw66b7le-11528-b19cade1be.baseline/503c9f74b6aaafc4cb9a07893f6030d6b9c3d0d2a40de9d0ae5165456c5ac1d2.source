<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountDeletionConfirmed;
use App\Mail\CardExpiring;
use App\Mail\DataExportReady;
use App\Mail\PaymentFailedMail;
use App\Mail\PaymentSucceeded;
use App\Mail\PlanChanged;
use App\Mail\RefundIssued;
use App\Mail\SubscriptionCanceledMail;
use App\Mail\SubscriptionRenewalReminder;
use App\Mail\SubscriptionResumed;
use App\Mail\TrialEnding;
use App\Mail\TrialStarted;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use HasinHayder\TyroDashboard\Services\AdminNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailTemplatesController extends Controller
{
    /** Mailable key => [class, sample-subject] */
    private function templates(): array
    {
        return [
            'trial-started' => [TrialStarted::class, 'Trial started'],
            'trial-ending' => [TrialEnding::class, 'Trial ending in 3 days'],
            'payment-succeeded' => [PaymentSucceeded::class, 'Payment receipt'],
            'payment-failed' => [PaymentFailedMail::class, 'Payment failed'],
            'renewal-reminder' => [SubscriptionRenewalReminder::class, 'Renewal reminder'],
            'plan-changed' => [PlanChanged::class, 'Plan changed'],
            'subscription-canceled' => [SubscriptionCanceledMail::class, 'Subscription canceled'],
            'subscription-resumed' => [SubscriptionResumed::class, 'Subscription resumed'],
            'refund-issued' => [RefundIssued::class, 'Refund issued'],
            'card-expiring' => [CardExpiring::class, 'Card expiring'],
            'account-deleted' => [AccountDeletionConfirmed::class, 'Account deletion'],
            'data-export' => [DataExportReady::class, 'Data export ready'],
        ];
    }

    public function index(): View
    {
        return view('dashboard.email-templates', [
            'templates' => collect($this->templates())->map(fn ($t, $key) => [
                'key' => $key,
                'subject' => $t[1],
            ])->values(),
        ]);
    }

    /** Render a template with sample data (browser preview). */
    public function show(Request $request, string $key): View
    {
        abort_unless(isset($this->templates()[$key]), 404);

        $mailable = $this->sampleMailable($key, $request->user());

        return view('dashboard.email-template-preview', [
            'key' => $key,
            'subject' => $mailable->envelope()->subject,
            'html' => $mailable->render(),
        ]);
    }

    /** Send a test email for a template to the admin's own address. */
    public function sendTest(Request $request, string $key): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('settings.manage'), 403);
        abort_unless(isset($this->templates()[$key]), 404);

        Mail::to($request->user())->send($this->sampleMailable($key, $request->user()));

        AdminNotice::show("Test email [{$key}] sent to {$request->user()->email}.", '#2563eb', '#ffffff', 'center');

        return redirect()->route('admin.email-templates.show', $key);
    }

    private function sampleMailable(string $key, ?User $user = null): object
    {
        [$class] = $this->templates()[$key];

        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => '', 'is_public' => true]);
        $workspace = Workspace::first();

        $subscription = new Subscription([
            'workspace_id' => $workspace->id ?? 1,
            'plan_id' => $plan->id,
            'gateway' => 'stripe',
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(3),
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);
        $subscription->setRelation('plan', $plan);
        $subscription->setRelation('workspace', $workspace ?? new Workspace(['name' => 'Acme']));

        $payment = new Payment(['amount' => 1200, 'currency' => 'USD']);

        return match ($key) {
            'payment-succeeded', 'refund-issued' => new $class($payment),
            'renewal-reminder' => new $class($subscription, 3),
            'account-deleted', 'data-export' => new $class($user ?? new User(['name' => 'Test User', 'email' => 'test@example.com'])),
            default => new $class($subscription),
        };
    }
}
