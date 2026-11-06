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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SSLCommerzGateway implements PaymentGateway
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.sslcommerz.sandbox')
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function key(): string
    {
        return 'sslcommerz';
    }

    public function capabilities(): array
    {
        return ['app_driven_renewal'];
    }

    public function createCustomer(Workspace $workspace): GatewayCustomer
    {
        return GatewayCustomer::firstOrCreate(
            ['workspace_id' => $workspace->id, 'gateway' => 'sslcommerz'],
            ['gateway_customer_id' => 'ws_'.$workspace->id],
        );
    }

    public function startCheckout(Workspace $workspace, PlanPrice $price, CheckoutOptions $options): CheckoutSession
    {
        $tranId = 'ssl_'.Str::random(16);

        $owner = $workspace->owner;
        $nameParts = explode(' ', $owner?->name ?? 'Customer', 2);

        $params = [
            'store_id' => config('services.sslcommerz.store_id'),
            'store_passwd' => config('services.sslcommerz.store_password'),
            'total_amount' => number_format($price->amount / 100, 2, '.', ''),
            'currency' => strtoupper($price->currency),
            'tran_id' => $tranId,
            'success_url' => $options->successUrl,
            'fail_url' => $options->cancelUrl,
            'cancel_url' => $options->cancelUrl,
            'cus_name' => $owner?->name ?? $workspace->name,
            'cus_email' => $owner?->email ?? 'unknown@example.com',
            'cus_phone' => '01700000000',
            'cus_add1' => 'Bangladesh',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'shipping_method' => 'NO',
            'product_name' => $price->plan->name ?? 'Subscription',
            'product_category' => 'SaaS',
            'product_profile' => 'general',
            'value_a' => $workspace->id,
            'value_b' => $price->id,
            'value_c' => $tranId,
        ];

        $response = Http::asForm()
            ->post("{$this->baseUrl}/gwprocess/v4/api.php", $params);

        $data = $response->json();

        if (($data['status'] ?? '') !== 'SUCCESS') {
            throw new \RuntimeException('SSLCommerz init failed: '.($data['failedreason'] ?? 'unknown'));
        }

        // Store pending tran_id for verification on IPN
        \Cache::put("sslcommerz_pending_{$tranId}", [
            'workspace_id' => $workspace->id,
            'plan_price_id' => $price->id,
            'amount' => $price->amount,
            'currency' => $price->currency,
        ], now()->addHour());

        return new CheckoutSession($data['GatewayPageURL'], $tranId);
    }

    public function portalUrl(Workspace $workspace, string $returnUrl): ?string
    {
        return null; // SSLCommerz has no portal
    }

    public function refundPayment(Payment $payment): void
    {
        throw new \RuntimeException('SSLCommerz refunds must be issued via the refund API / merchant panel.');
    }

    public function cancel(Subscription $subscription, bool $atPeriodEnd = true): void
    {
        $subscription->cancel(! $atPeriodEnd);
    }

    public function resume(Subscription $subscription): void
    {
        $subscription->resume();
    }

    public function verifyWebhook(Request $request): array
    {
        $storeId = config('services.sslcommerz.store_id');
        $storePass = config('services.sslcommerz.store_password');

        $valId = $request->input('val_id');
        $status = $request->input('status');
        $tranId = $request->input('tran_id');
        $currency = $request->input('currency');
        $amount = $request->input('amount');
        $verifySign = $request->input('verify_sign');
        $verifyKey = $request->input('verify_key');

        if (! $valId || ! $status || ! $tranId || ! $verifySign || ! $verifyKey) {
            throw new \RuntimeException('Missing IPN fields');
        }

        // Hash verification: sort verify_key fields + store_passwd, then md5
        $keys = explode(',', $verifyKey);
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $request->input($key);
        }
        ksort($data);
        $data['store_passwd'] = md5($storePass);
        $hashString = implode('', $data);

        if (md5($hashString) !== $verifySign) {
            throw new \RuntimeException('Invalid SSLCommerz IPN hash');
        }

        // Server-side validation — required per ADR security note
        $validationUrl = config('services.sslcommerz.sandbox')
            ? 'https://sandbox.sslcommerz.com/validator/api/validationDetails.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationDetails.php';

        $validation = Http::get($validationUrl, [
            'val_id' => $valId,
            'store_id' => $storeId,
            'store_passwd' => $storePass,
            'v' => 1,
            'format' => 'json',
        ])->json();

        if (($validation['status'] ?? '') !== 'VALID' && ($validation['status'] ?? '') !== 'VALIDATED') {
            throw new \RuntimeException('SSLCommerz validation failed: '.($validation['status'] ?? 'unknown'));
        }

        // Amount/currency tamper check
        $pending = \Cache::get("sslcommerz_pending_{$tranId}");
        if ($pending) {
            $expectedAmount = number_format($pending['amount'] / 100, 2, '.', '');
            $expectedCurrency = strtoupper($pending['currency']);

            if ((string) $amount !== $expectedAmount || strtoupper((string) $currency) !== $expectedCurrency) {
                throw new \RuntimeException('SSLCommerz amount/currency mismatch');
            }
        }

        return [
            'event_type' => 'transaction.completed',
            'notification_id' => 'ssl_'.$tranId,
            'data' => array_merge($request->all(), [
                'validation' => $validation,
                'pending_meta' => $pending,
            ]),
        ];
    }

    public function handleEvent(array $event): void
    {
        if (($event['event_type'] ?? '') !== 'transaction.completed') {
            return;
        }

        $data = $event['data'] ?? [];
        $pending = $data['pending_meta'] ?? null;

        if (! $pending) {
            return;
        }

        $workspaceId = $pending['workspace_id'];
        $planPriceId = $pending['plan_price_id'];

        $price = PlanPrice::find($planPriceId);
        if (! $price) {
            return;
        }

        // Find or create subscription (SSLCommerz is app-driven renewal)
        $subscription = Subscription::where('workspace_id', $workspaceId)
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->latest()
            ->first();

        if ($subscription) {
            $subscription->activate([
                'plan_id' => $price->plan_id,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
        } else {
            Subscription::create([
                'workspace_id' => $workspaceId,
                'plan_id' => $price->plan_id,
                'gateway' => 'sslcommerz',
                'gateway_subscription_id' => $data['tran_id'] ?? null,
                'gateway_customer_id' => 'ws_'.$workspaceId,
                'status' => 'active',
                'app_driven_renewal' => true,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
        }

        app(EntitlementService::class)->flush((int) $workspaceId);
    }
}
