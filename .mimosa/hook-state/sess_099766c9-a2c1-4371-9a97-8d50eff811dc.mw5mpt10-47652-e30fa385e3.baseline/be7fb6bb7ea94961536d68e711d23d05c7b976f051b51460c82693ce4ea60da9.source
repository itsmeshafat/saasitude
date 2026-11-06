<?php

namespace App\Http\Controllers;

use App\Actions\ApplyCoupon;
use App\Contracts\PaymentGateway;
use App\DTOs\CheckoutOptions;
use App\Models\PlanPrice;
use App\Services\GatewayManager;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function checkout(Request $request, GatewayManager $gateways): View|RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.workspace-settings');
        }

        $currency = strtoupper((string) $request->query('currency', 'USD'));
        $gateway = $gateways->forCurrency($currency);

        $prices = PlanPrice::with('plan')
            ->whereNotNull('gateway_price_id')
            ->where('gateway', $gateway->key())
            ->where('currency', $currency)
            ->get();

        return view('dashboard.billing.checkout', [
            'workspace' => $workspace,
            'prices' => $prices,
            'gateway' => $gateway,
            'currency' => $currency,
            'supportedCurrencies' => ['USD', 'BDT'],
        ]);
    }

    public function startCheckout(Request $request, GatewayManager $gateways, ApplyCoupon $coupons): RedirectResponse
    {
        $request->validate([
            'plan_price_id' => 'required|exists:plan_prices,id',
            'coupon' => 'nullable|string|max:50',
        ]);

        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.workspace-settings');
        }

        $price = PlanPrice::findOrFail($request->input('plan_price_id'));
        $gateway = $gateways->resolve($price->gateway);

        // Validate the coupon against this workspace and price before redirecting.
        // The redemption itself is recorded by the verified webhook, never here.
        if ($couponCode = $request->input('coupon')) {
            try {
                $coupons->execute($workspace, $couponCode, $price);
            } catch (\RuntimeException $e) {
                return redirect()->route('dashboard.billing.checkout', ['currency' => $price->currency])
                    ->withInput()
                    ->with('error', $e->getMessage());
            }
        }

        $trialDays = app(SettingsService::class)->get('trial_length_days', 14);

        $session = $gateway->startCheckout($workspace, $price, new CheckoutOptions(
            successUrl: route('dashboard.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
            cancelUrl: route('dashboard.billing.checkout', ['currency' => $price->currency]),
            trialDays: (int) $trialDays,
            coupon: $couponCode,
        ));

        return redirect()->away($session->url);
    }

    public function success(): View
    {
        return view('dashboard.billing.success');
    }

    public function cancel(): View
    {
        return view('dashboard.billing.cancel');
    }

    public function portal(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.billing.checkout');
        }

        $url = $gateway->portalUrl($workspace, route('dashboard.billing.checkout'));

        if (! $url) {
            return redirect()->route('dashboard.billing.checkout')
                ->with('error', 'No billing portal available for your payment method.');
        }

        return redirect()->away($url);
    }
}
