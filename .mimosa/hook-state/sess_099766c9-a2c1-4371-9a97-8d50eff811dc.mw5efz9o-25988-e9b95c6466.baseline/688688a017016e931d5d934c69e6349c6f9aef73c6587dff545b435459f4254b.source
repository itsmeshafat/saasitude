<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Workspace;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWorkspacesController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('search');

        $workspaces = Workspace::query()
            ->withCount('members')
            ->with(['owner', 'latestSubscription.plan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('owner', fn ($owner) => $owner->where('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.workspaces-admin', ['workspaces' => $workspaces, 'search' => $search]);
    }

    public function show(Workspace $workspace): View
    {
        $workspace->loadCount('members')
            ->load([
                'owner',
                'latestSubscription.plan',
                'subscriptions.plan' => fn ($q) => $q->latest('id')->limit(10),
                'payments' => fn ($q) => $q->latest()->limit(10),
                'invoices' => fn ($q) => $q->latest()->limit(10),
            ]);

        return view('dashboard.workspaces-admin-detail', [
            'workspace' => $workspace,
            'plans' => Plan::where('is_public', true)->orderBy('id')->get(),
        ]);
    }

    public function suspend(Request $request, Workspace $workspace): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('workspaces.manage'), 403);

        $workspace->update(['suspended_at' => now()]);
        TyroAudit::log('workspace.suspended', $workspace, null, ['suspended' => true]);

        return redirect()->route('admin.workspaces.show', $workspace)
            ->with('success', 'Workspace suspended.');
    }

    public function restore(Request $request, Workspace $workspace): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('workspaces.manage'), 403);

        $workspace->update(['suspended_at' => null]);
        TyroAudit::log('workspace.restored', $workspace, null, ['suspended' => false]);

        return redirect()->route('admin.workspaces.show', $workspace)
            ->with('success', 'Workspace restored.');
    }

    /** Force-move a workspace onto a plan immediately (admin override, audited). */
    public function forcePlan(Request $request, Workspace $workspace): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('workspaces.manage'), 403);
        abort_if(session('impersonator_id'), 403, 'Billing actions are blocked while impersonating.');

        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'trial_days' => 'nullable|integer|min:0|max:365',
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        // End any existing subscription rows, then create an app-managed one.
        $workspace->subscriptions()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->update(['status' => 'canceled', 'canceled_at' => now()]);

        $trialDays = (int) ($data['trial_days'] ?? 0);

        $subscription = Subscription::create([
            'workspace_id' => $workspace->id,
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => $trialDays > 0 ? 'trialing' : 'active',
            'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        TyroAudit::log('workspace.force_plan', $subscription, null, [
            'plan' => $plan->name,
            'trial_days' => $trialDays,
            'admin_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.workspaces.show', $workspace)
            ->with('success', "Workspace moved to {$plan->name}.");
    }
}
