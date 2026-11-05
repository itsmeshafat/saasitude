<?php

namespace Tests\Fakes;

use App\Contracts\PaymentGateway;
use App\DTOs\CheckoutOptions;
use App\DTOs\CheckoutSession;
use App\Models\GatewayCustomer;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Http\Request;

class FakeGateway implements PaymentGateway
{
    public bool $cancelled = false;

    public bool $resumed = false;

    public function key(): string
    {
        return 'stripe';
    }

    public function capabilities(): array
    {
        return ['checkout', 'portal', 'refunds'];
    }

    public function createCustomer(Workspace $workspace): GatewayCustomer
    {
        return $workspace->gatewayCustomers()->create([
            'gateway' => $this->key(),
            'gateway_customer_id' => 'cus_fake_'.uniqid(),
        ]);
    }

    public function startCheckout(Workspace $workspace, PlanPrice $price, CheckoutOptions $options): CheckoutSession
    {
        return new CheckoutSession('https://checkout.example/fake', 'fake_session');
    }

    public function portalUrl(Workspace $workspace, string $returnUrl): ?string
    {
        return 'https://portal.example/fake';
    }

    public function cancel(Subscription $subscription, bool $atPeriodEnd = true): void
    {
        $this->cancelled = true;
        $subscription->cancel(immediately: ! $atPeriodEnd);
    }

    public function resume(Subscription $subscription): void
    {
        $this->resumed = true;
        $subscription->resume();
    }

    public function verifyWebhook(Request $request): array
    {
        return [];
    }

    public function handleEvent(array $event): void {}
}
