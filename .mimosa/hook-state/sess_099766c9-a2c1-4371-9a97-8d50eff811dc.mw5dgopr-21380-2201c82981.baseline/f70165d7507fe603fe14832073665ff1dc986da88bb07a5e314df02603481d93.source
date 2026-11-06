<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CancelSubscription as CancelSubscriptionAction;
use App\DTOs\CheckoutOptions;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Resources\Api\V1\SubscriptionResource;
use App\Models\PlanPrice;
use App\Services\EntitlementService;
use App\Services\GatewayManager;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BillingApiController extends Controller
{
    private function workspace(Request $request)
    {
        return $request->attributes->get('workspace');
    }

    public function subscription(Request $request): JsonResponse
    {
        $subscription = $this->workspace($request)->activeSubscription()?->load('plan.planFeatures.feature', 'plan.prices');

        if ($subscription === null) {
            return response()->json([
                'message' => 'No active subscription.',
                'code' => 'no_subscription',
                'errors' => [],
                'data' => null,
            ]);
        }

        return (new SubscriptionResource($subscription))->response();
    }

    /** Resolved entitlements + metered usage for the workspace. */
    public function usage(Request $request, EntitlementService $entitlements): JsonResponse
    {
        $context = $entitlements->for($this->workspace($request));
        $subscription = $this->workspace($request)->activeSubscription()?->load('plan.planFeatures.feature');

        $features = collect($subscription?->plan->planFeatures ?? [])
            ->filter(fn ($pf) => $pf->feature !== null)
            ->mapWithKeys(function ($pf) use ($context) {
                $feature = $pf->feature;

                return [$feature->key => match ($feature->type) {
                    'boolean' => ['type' => 'boolean', 'enabled' => $context->can($feature->key)],
                    'limit' => ['type' => 'limit', 'limit' => $context->limit($feature->key)],
                    'metered' => ['type' => 'metered'] + $context->metered($feature->key),
                    default => ['type' => $feature->type],
                }];
            });

        return response()->json(['data' => $features->all()]);
    }

    public function invoices(Request $request): AnonymousResourceCollection
    {
        return InvoiceResource::collection(
            $this->workspace($request)->invoices()->orderByDesc('id')->cursorPaginate(15),
        );
    }

    public function payments(Request $request): AnonymousResourceCollection
    {
        return PaymentResource::collection(
            $this->workspace($request)->payments()->orderByDesc('id')->cursorPaginate(15),
        );
    }

    /**
     * Create a checkout session for mobile. Returns a hosted-checkout URL to
     * open in an in-app browser (SFSafariViewController / Chrome Custom Tab) —
     * never a native card form. Requires an Idempotency-Key header.
     */
    public function checkout(Request $request, GatewayManager $gateways, SettingsService $settings): JsonResponse
    {
        $validated = $request->validate([
            'plan_price_id' => ['required', 'exists:plan_prices,id'],
            'coupon' => ['nullable', 'string', 'max:50'],
        ]);

        $workspace = $this->workspace($request);
        $price = PlanPrice::findOrFail($validated['plan_price_id']);
        $gateway = $gateways->forCurrency($price->currency);

        $session = $gateway->startCheckout(
            $workspace,
            $price,
            new CheckoutOptions(
                successUrl: config('app.url').'/dashboard/billing/success?session_id={CHECKOUT_SESSION_ID}',
                cancelUrl: config('app.url').'/dashboard/billing/checkout?currency='.$price->currency,
                trialDays: (int) $settings->get('trial_length_days', 14),
                coupon: $validated['coupon'] ?? null,
            ),
        );

        return response()->json([
            'message' => 'Checkout session created.',
            'code' => 'checkout_created',
            'errors' => [],
            'data' => [
                'checkout_url' => $session->url,
                'session_id' => $session->sessionId,
                'gateway' => $gateway->key(),
            ],
        ], 201);
    }

    public function cancel(Request $request, CancelSubscriptionAction $action): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'in:too_expensive,missing_features,switching_competitor,pausing,other'],
        ]);

        try {
            $action->execute($this->workspace($request), $validated['reason']);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => 'cancel_failed',
                'errors' => [],
            ], 422);
        }

        return response()->json([
            'message' => 'Subscription will be canceled at the end of the billing period.',
            'code' => 'subscription_cancelling',
            'errors' => [],
        ]);
    }

    public function resume(Request $request, GatewayManager $gateways): JsonResponse
    {
        $subscription = $this->workspace($request)->activeSubscription();

        if ($subscription === null || ! $subscription->cancel_at_period_end) {
            return response()->json([
                'message' => 'No scheduled cancellation to resume.',
                'code' => 'nothing_to_resume',
                'errors' => [],
            ], 422);
        }

        $gateways->resolve($subscription->gateway)->resume($subscription);

        return response()->json([
            'message' => 'Subscription resumed.',
            'code' => 'subscription_resumed',
            'errors' => [],
        ]);
    }

    public function changePlan(Request $request, GatewayManager $gateways): JsonResponse
    {
        $validated = $request->validate([
            'plan_price_id' => ['required', 'exists:plan_prices,id'],
        ]);

        $workspace = $this->workspace($request);
        $subscription = $workspace->activeSubscription();

        if ($subscription === null) {
            return response()->json([
                'message' => 'No active subscription to change.',
                'code' => 'no_subscription',
                'errors' => [],
            ], 422);
        }

        // Changing plan is a new checkout (proration handled by the gateway).
        return $this->checkout($request, $gateways, app(SettingsService::class));
    }
}
