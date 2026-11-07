<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.workspace-settings');
        }

        $plans = Plan::with(['prices', 'planFeatures.feature'])
            ->where('is_public', true)
            ->where('is_archived', false)
            ->orderBy('id')
            ->get();

        $currentPlanId = $workspace->activeSubscription()?->plan_id;

        $currency = strtoupper((string) $request->query('currency', 'USD'));

        return view('dashboard.plans', [
            'workspace' => $workspace,
            'plans' => $plans,
            'currentPlanId' => $currentPlanId,
            'currency' => in_array($currency, ['USD', 'BDT']) ? $currency : 'USD',
        ]);
    }
}
