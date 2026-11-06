<?php

namespace App\Http\Controllers;

use App\Services\EntitlementService;
use App\Services\GatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingOverviewController extends Controller
{
    public function show(Request $request, EntitlementService $entitlements, GatewayManager $gateways): View|RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.workspace-settings');
        }

        $subscription = $workspace->activeSubscription()?->load('plan.planFeatures.feature');
        $context = $entitlements->for($workspace);

        $gateway = $subscription ? $gateways->resolve($subscription->gateway) : $gateways->default();

        $meters = collect($subscription?->plan->planFeatures ?? [])
            ->filter(fn ($pf) => $pf->feature?->type === 'metered')
            ->map(function ($pf) use ($context) {
                $metered = $context->metered($pf->feature->key);

                return [
                    'label' => $pf->feature->name,
                    'used' => $metered['used'],
                    'limit' => $metered['limit'],
                ];
            })->values();

        return view('dashboard.billing', [
            'workspace' => $workspace,
            'subscription' => $subscription,
            'meters' => $meters,
            'hasPortal' => in_array('portal', $gateway->capabilities()),
        ]);
    }
}
