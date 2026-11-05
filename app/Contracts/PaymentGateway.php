<?php

namespace App\Contracts;

use App\DTOs\CheckoutOptions;
use App\DTOs\CheckoutSession;
use App\Models\GatewayCustomer;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function key(): string;

    public function capabilities(): array;

    public function createCustomer(Workspace $workspace): GatewayCustomer;

    public function startCheckout(Workspace $workspace, PlanPrice $price, CheckoutOptions $options): CheckoutSession;

    public function portalUrl(Workspace $workspace, string $returnUrl): ?string;

    /** Refund a succeeded payment at the gateway. Throws on failure. */
    public function refundPayment(Payment $payment): void;

    public function cancel(Subscription $subscription, bool $atPeriodEnd = true): void;

    public function resume(Subscription $subscription): void;

    public function verifyWebhook(Request $request): array;

    public function handleEvent(array $event): void;
}
