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
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeGateway implements PaymentGateway
{
    private ?StripeClient $stripe = null;

    private function stripe(): StripeClient
    {
        return $this->stripe ??= new StripeClient(config('services.stripe.secret'));
    }

    public function key(): string
    {
        return 'stripe';
    }

    public function capabilities(): array
    {
        return ['recurring', 'portal', 'refunds', 'proration', 'tax_mor'];
    }

    public function createCustomer(Workspace $workspace): GatewayCustomer
    {
        $existing = GatewayCustomer::where('workspace_id', $workspace->id)
            ->where('gateway', 'stripe')
            ->first();

        if ($existing) {
            return $existing;
        }

        $customer = $this->stripe()->customers->create([
            'name' => $workspace->name,
            'metadata' => ['workspace_id' => $workspace->id],
        ]);

        return GatewayCustomer::create([
            'workspace_id' => $workspace->id,
            'gateway' => 'stripe',
            'gateway_customer_id' => $customer->id,
        ]);
    }

    public function startCheckout(Workspace $workspace, PlanPrice $price, CheckoutOptions $options): CheckoutSession
    {
        $customer = $this->createCustomer($workspace);

        $params = [
            'customer' => $customer->gateway_customer_id,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $price->gateway_price_id,
                'quantity' => 1,
            ]],
            'success_url' => $options->successUrl,
            'cancel_url' => $options->cancelUrl,
            'metadata' => ['workspace_id' => $workspace->id, 'plan_price_id' => $price->id],
            'subscription_data' => ['metadata' => ['workspace_id' => $workspace->id, 'plan_price_id' => $price->id]],
        ];

        if ($options->trialDays !== null && $options->trialDays > 0) {
            $params['subscription_data']['trial_period_days'] = $options->trialDays;
        }

        if ($options->coupon !== null) {
            $params['discounts'] = [['coupon' => $options->coupon]];
        }

        $session = $this->stripe()->checkout->sessions->create($params);

        return new CheckoutSession($session->url, $session->id);
    }

    public function portalUrl(Workspace $workspace, string $returnUrl): ?string
    {
        $customer = GatewayCustomer::where('workspace_id', $workspace->id)
            ->where('gateway', 'stripe')
            ->first();

        if (! $customer) {
            return null;
        }

        $session = $this->stripe()->billingPortal->sessions->create([
            'customer' => $customer->gateway_customer_id,
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }

    public function refundPayment(Payment $payment): void
    {
        $this->stripe()->refunds->create([
            'payment_intent' => $payment->gateway_payment_id,
        ]);
    }

    public function cancel(Subscription $subscription, bool $atPeriodEnd = true): void
    {
        $this->stripe()->subscriptions->update($subscription->gateway_subscription_id, [
            'cancel_at_period_end' => $atPeriodEnd,
        ]);

        $subscription->cancel(! $atPeriodEnd);
    }

    public function resume(Subscription $subscription): void
    {
        $this->stripe()->subscriptions->update($subscription->gateway_subscription_id, [
            'cancel_at_period_end' => false,
        ]);

        $subscription->resume();
    }

    public function verifyWebhook(Request $request): array
    {
        $event = Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret')
        );

        return $event->toArray();
    }

    public function handleEvent(array $event): void
    {
        match ($event['type']) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event['data']['object']),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event['data']['object']),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event['data']['object']),
            'invoice.paid' => $this->handleInvoicePaid($event['data']['object']),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event['data']['object']),
            default => null,
        };
    }

    private function handleCheckoutCompleted(array $session): void
    {
        if (($session['mode'] ?? '') !== 'subscription') {
            return;
        }

        $workspaceId = $session['metadata']['workspace_id'] ?? null;
        $planPriceId = $session['metadata']['plan_price_id'] ?? null;

        if (! $workspaceId || ! $planPriceId) {
            return;
        }

        $price = PlanPrice::find($planPriceId);
        if (! $price) {
            return;
        }

        $stripeSubscription = $this->stripe()->subscriptions->retrieve($session['subscription']);

        $trialEnd = $stripeSubscription->trial_end
            ? Carbon::createFromTimestamp($stripeSubscription->trial_end)
            : null;

        $status = $stripeSubscription->status === 'trialing' ? 'trialing' : 'active';

        Subscription::create([
            'workspace_id' => $workspaceId,
            'plan_id' => $price->plan_id,
            'gateway' => 'stripe',
            'gateway_subscription_id' => $stripeSubscription->id,
            'gateway_customer_id' => $stripeSubscription->customer,
            'status' => $status,
            'trial_ends_at' => $trialEnd,
            'current_period_start' => Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end' => Carbon::createFromTimestamp($stripeSubscription->current_period_end),
        ]);

        app(EntitlementService::class)->flush((int) $workspaceId);
    }

    private function handleSubscriptionUpdated(array $stripeSubscription): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $stripeSubscription['id'])->first();

        if (! $subscription) {
            return;
        }

        $status = $stripeSubscription['status'];
        $cancelAtPeriodEnd = (bool) $stripeSubscription['cancel_at_period_end'];

        if ($status === 'active' && ! $cancelAtPeriodEnd) {
            $subscription->activate([
                'current_period_start' => Carbon::createFromTimestamp($stripeSubscription['current_period_start']),
                'current_period_end' => Carbon::createFromTimestamp($stripeSubscription['current_period_end']),
            ]);
        } elseif ($status === 'past_due') {
            $subscription->markPastDue();
        } else {
            $subscription->update([
                'status' => $status,
                'cancel_at_period_end' => $cancelAtPeriodEnd,
            ]);
            app(EntitlementService::class)->flush($subscription->workspace_id);
        }
    }

    private function handleSubscriptionDeleted(array $stripeSubscription): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $stripeSubscription['id'])->first();

        $subscription?->cancel(immediately: true);
    }

    private function handleInvoicePaid(array $invoice): void
    {
        // Phase 7 (T-019): generate Invoice record. For now sync period.
        $subId = $invoice['subscription'] ?? null;
        if (! $subId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subId)->first();
        if (! $subscription) {
            return;
        }

        $subscription->update([
            'status' => 'active',
            'current_period_start' => Carbon::createFromTimestamp($invoice['period_start']),
            'current_period_end' => Carbon::createFromTimestamp($invoice['period_end']),
        ]);
        app(EntitlementService::class)->flush($subscription->workspace_id);
    }

    private function handleInvoicePaymentFailed(array $invoice): void
    {
        $subId = $invoice['subscription'] ?? null;
        if (! $subId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subId)->first();
        $subscription?->markPastDue();
    }
}
