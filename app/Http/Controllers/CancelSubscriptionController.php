<?php

namespace App\Http\Controllers;

use App\Actions\CancelSubscription;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CancelSubscriptionController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.workspace-settings');
        }

        $subscription = $workspace->activeSubscription()?->load('plan');

        if (! $subscription) {
            return redirect()->route('dashboard.billing')
                ->with('error', 'You need an active subscription before you can cancel one.');
        }

        // Offer a cheaper plan as a downgrade alternative, if one exists.
        $downgradeOptions = Plan::query()
            ->where('is_public', true)
            ->where('is_archived', false)
            ->whereKeyNot($subscription->plan_id)
            ->orderBy('id')
            ->get();

        return view('dashboard.cancel-subscription', [
            'workspace' => $workspace,
            'subscription' => $subscription,
            'downgradeOptions' => $downgradeOptions,
        ]);
    }

    public function cancel(Request $request, CancelSubscription $action): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|in:too_expensive,missing_features,switching_competitor,pausing,other',
            'immediately' => 'nullable|boolean',
        ]);

        $workspace = $request->user()->currentWorkspace();

        if (! $workspace) {
            return redirect()->route('dashboard.workspace-settings');
        }

        try {
            $action->execute(
                $workspace,
                $request->input('reason'),
                (bool) $request->boolean('immediately'),
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('dashboard.cancel-subscription')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.billing')
            ->with('success', 'Your subscription will be canceled at the end of the current billing period.');
    }
}
