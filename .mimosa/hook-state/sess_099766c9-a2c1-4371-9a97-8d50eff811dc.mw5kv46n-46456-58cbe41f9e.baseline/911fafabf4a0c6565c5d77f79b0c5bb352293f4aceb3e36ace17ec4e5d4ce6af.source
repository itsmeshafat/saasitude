<?php

namespace App\Gateways;

use App\Contracts\PaymentGateway;
use App\DTOs\CheckoutOptions;
use App\DTOs\CheckoutSession;
use App\Models\GatewayCustomer;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Workspace;
use App\Services\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Paddle\SDK\Client;
use Paddle\SDK\Entities\Shared\CustomData;
use Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom;
use Paddle\SDK\Entities\Subscription\SubscriptionResumeEffectiveFrom;
use Paddle\SDK\Environment;
use Paddle\SDK\Notifications\PaddleSignature;
use Paddle\SDK\Notifications\Secret;
use Paddle\SDK\Options;
use Paddle\SDK\Resources\CustomerPortalSessions\Operations\CreateCustomerPortalSession;
use Paddle\SDK\Resources\Customers\Operations\CreateCustomer;
use Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription;
use Paddle\SDK\Resources\Subscriptions\Operations\ResumeSubscription;
use Paddle\SDK\Resources\Transactions\Operations\Create\TransactionCreateItem;
use Paddle\SDK\Resources\Transactions\Operations\CreateTransaction;

class PaddleGateway implements PaymentGateway
{
    private ?Client $client = null;

    private function paddle(): Client
    {
        return $this->client ??= new Client(
            apiKey: config('services.paddle.api_key'),
            options: new Options(
                environment: config('services.paddle.sandbox') ? Environment::SANDBOX : Environment::PRODUCTION,
            ),
        );
    }

    public function key(): string
    {
        return 'paddle';
    }

    public function capabilities(): array
    {
        return ['recurring', 'portal', 'tax_mor'];
    }

    public function createCustomer(Workspace $workspace): GatewayCustomer
    {
        $existing = GatewayCustomer::where('workspace_id', $workspace->id)
            ->where('gateway', 'paddle')
            ->first();

        if ($existing) {
            return $existing;
        }

        $owner = $workspace->owner;
        $customer = $this->paddle()->customers->create(new CreateCustomer(
            email: $owner?->email ?? 'unknown@example.com',
            name: $workspace->name,
        ));

        return GatewayCustomer::create([
            'workspace_id' => $workspace->id,
            'gateway' => 'paddle',
            'gateway_customer_id' => $customer->id,
        ]);
    }

    public function startCheckout(Workspace $workspace, PlanPrice $price, CheckoutOptions $options): CheckoutSession
    {
        $customer = $this->createCustomer($workspace);

        $transaction = $this->paddle()->transactions->create(new CreateTransaction(
            items: [new TransactionCreateItem(priceId: $price->gateway_price_id, quantity: 1)],
            customerId: $customer->gateway_customer_id,
            customData: new CustomData([
                'workspace_id' => $workspace->id,
                'plan_price_id' => $price->id,
            ]),
        ));

        return new CheckoutSession($transaction->checkout?->url ?? '', $transaction->id);
    }

    public function portalUrl(Workspace $workspace, string $returnUrl): ?string
    {
        $customer = GatewayCustomer::where('workspace_id', $workspace->id)
            ->where('gateway', 'paddle')
            ->first();

        if (! $customer) {
            return null;
        }

        $session = $this->paddle()->customerPortalSessions->create(
            $customer->gateway_customer_id,
            new CreateCustomerPortalSession,
        );

        return $session->urls->general->overview;
    }

    public function refundPayment(Payment $payment): void
    {
        throw new \RuntimeException('Paddle refunds are issued from the Paddle dashboard (MoR).');
    }

    public function cancel(Subscription $subscription, bool $atPeriodEnd = true): void
    {
        $effectiveFrom = $atPeriodEnd
            ? SubscriptionEffectiveFrom::NextBillingPeriod()
            : SubscriptionEffectiveFrom::Immediately();

        $this->paddle()->subscriptions->cancel(
            $subscription->gateway_subscription_id,
            new CancelSubscription($effectiveFrom),
        );

        $subscription->cancel(! $atPeriodEnd);
    }

    public function resume(Subscription $subscription): void
    {
        $this->paddle()->subscriptions->resume(
            $subscription->gateway_subscription_id,
            new ResumeSubscription(SubscriptionResumeEffectiveFrom::Immediately()),
        );

        $subscription->resume();
    }

    public function verifyWebhook(Request $request): array
    {
        $header = $request->header(PaddleSignature::HEADER, '');
        $rawBody = $request->getContent();

        $valid = PaddleSignature::parse($header)->verify(
            $rawBody,
            new Secret(config('services.paddle.webhook_secret')),
        );

        if (! $valid) {
            throw new \RuntimeException('Invalid Paddle webhook signature');
        }

        return json_decode($rawBody, true);
    }

    public function handleEvent(array $event): void
    {
        match ($event['event_type'] ?? '') {
            'subscription.activated' => $this->handleSubscriptionActivated($event['data']),
            'subscription.created' => $this->handleSubscriptionCreated($event['data']),
            'subscription.updated' => $this->handleSubscriptionUpdated($event['data']),
            'subscription.canceled' => $this->handleSubscriptionCanceled($event['data']),
            'subscription.past_due' => $this->handleSubscriptionPastDue($event['data']),
            'transaction.completed' => $this->handleTransactionCompleted($event['data']),
            'transaction.payment_failed' => $this->handleTransactionPaymentFailed($event['data']),
            default => null,
        };
    }

    private function handleSubscriptionCreated(array $data): void
    {
        $customData = $data['custom_data'] ?? [];
        $workspaceId = $customData['workspace_id'] ?? null;
        $planPriceId = $customData['plan_price_id'] ?? null;

        if (! $workspaceId || ! $planPriceId) {
            return;
        }

        $price = PlanPrice::find($planPriceId);
        if (! $price) {
            return;
        }

        $status = $data['status'] === 'trialing' ? 'trialing' : 'active';

        $periodStart = isset($data['current_billing_period']['starts_at'])
            ? Carbon::parse($data['current_billing_period']['starts_at'])
            : now();
        $periodEnd = isset($data['current_billing_period']['ends_at'])
            ? Carbon::parse($data['current_billing_period']['ends_at'])
            : now()->addMonth();

        Subscription::create([
            'workspace_id' => $workspaceId,
            'plan_id' => $price->plan_id,
            'gateway' => 'paddle',
            'gateway_subscription_id' => $data['id'],
            'gateway_customer_id' => $data['customer_id'],
            'status' => $status,
            'trial_ends_at' => isset($data['trial_dates']['ends_at']) ? Carbon::parse($data['trial_dates']['ends_at']) : null,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]);

        app(EntitlementService::class)->flush((int) $workspaceId);
    }

    private function handleSubscriptionActivated(array $data): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $data['id'])->first();
        if (! $subscription) {
            return;
        }

        $periodStart = isset($data['current_billing_period']['starts_at'])
            ? Carbon::parse($data['current_billing_period']['starts_at'])
            : null;
        $periodEnd = isset($data['current_billing_period']['ends_at'])
            ? Carbon::parse($data['current_billing_period']['ends_at'])
            : null;

        $subscription->activate(array_filter([
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]));
    }

    private function handleSubscriptionUpdated(array $data): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $data['id'])->first();
        if (! $subscription) {
            return;
        }

        $status = $data['status'] ?? $subscription->status;

        if ($status === 'active') {
            $periodStart = isset($data['current_billing_period']['starts_at'])
                ? Carbon::parse($data['current_billing_period']['starts_at'])
                : null;
            $periodEnd = isset($data['current_billing_period']['ends_at'])
                ? Carbon::parse($data['current_billing_period']['ends_at'])
                : null;
            $subscription->activate(array_filter([
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
            ]));
        } elseif ($status === 'past_due') {
            $subscription->markPastDue();
        } else {
            $subscription->update(['status' => $status]);
            app(EntitlementService::class)->flush($subscription->workspace_id);
        }
    }

    private function handleSubscriptionCanceled(array $data): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $data['id'])->first();
        $subscription?->cancel(immediately: true);
    }

    private function handleSubscriptionPastDue(array $data): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $data['id'])->first();
        $subscription?->markPastDue();
    }

    private function handleTransactionCompleted(array $data): void
    {
        $subId = $data['subscription_id'] ?? null;
        if (! $subId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subId)->first();
        if (! $subscription) {
            return;
        }

        $subscription->update(['status' => 'active']);
        app(EntitlementService::class)->flush($subscription->workspace_id);
    }

    private function handleTransactionPaymentFailed(array $data): void
    {
        $subId = $data['subscription_id'] ?? null;
        if (! $subId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subId)->first();
        $subscription?->markPastDue();
    }
}
