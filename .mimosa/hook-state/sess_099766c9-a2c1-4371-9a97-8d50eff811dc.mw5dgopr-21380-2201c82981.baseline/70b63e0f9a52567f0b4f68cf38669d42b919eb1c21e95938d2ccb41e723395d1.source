<?php

namespace App\Http\Controllers;

use App\Services\GatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodsController extends Controller
{
    public function show(Request $request, GatewayManager $gateways): View|RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.workspace-settings');
        }

        $subscription = $workspace->activeSubscription();

        // Gateway of the active subscription; fall back to the default gateway.
        $gateway = $subscription
            ? $gateways->resolve($subscription->gateway)
            : $gateways->default();

        $lastPayment = $workspace->payments()->latest()->first();

        return view('dashboard.payment-methods', [
            'workspace' => $workspace,
            'subscription' => $subscription,
            'gateway' => $gateway,
            'lastPayment' => $lastPayment,
        ]);
    }
}
