@extends('tyro-dashboard::layouts.admin')

@section('title', $workspace->name)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('admin.workspaces.index') }}">Workspaces</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $workspace->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $workspace->name }}</h1>
            <p class="page-description">
                Owner: {{ $workspace->owner->email }} · {{ $workspace->members_count }} member(s)
                @if($workspace->isSuspended()) · <span class="text-[var(--destructive)]">Suspended</span>@endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($workspace->isSuspended())
                <form method="POST" action="{{ route('admin.workspaces.restore', $workspace) }}">
                    @csrf
                    <x-ui.button variant="outline" type="submit">Restore</x-ui.button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.workspaces.suspend', $workspace) }}"
                      x-data @submit.prevent="showDanger('Suspend workspace?', 'Members lose access until it is restored.', () => $el.submit())">
                    @csrf
                    <x-ui.button variant="danger" type="submit">Suspend</x-ui.button>
                </form>
            @endif
        </div>
    </div>
</div>


<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Subscription</h3></div>
        <div class="card-body">
            @if($workspace->activeSubscription)
                <div class="flex items-center gap-2 mb-3">
                    <span class="font-semibold">{{ $workspace->activeSubscription->plan->name }}</span>
                    <x-ui.status-pill :status="$workspace->activeSubscription->status" />
                    <x-ui.badge variant="info">{{ ucfirst($workspace->activeSubscription->gateway) }}</x-ui.badge>
                </div>
                <div class="text-sm text-[var(--muted-foreground)]">
                    Period: {{ optional($workspace->activeSubscription->current_period_start)->format('M j') }}
                    → {{ optional($workspace->activeSubscription->current_period_end)->format('M j, Y') }}
                </div>
            @else
                <p class="text-sm text-[var(--muted-foreground)]">No active subscription.</p>
            @endif

            <form method="POST" action="{{ route('admin.workspaces.force-plan', $workspace) }}" class="mt-4 flex flex-wrap gap-2 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium mb-1" for="plan_id">Force plan</label>
                    <select name="plan_id" id="plan_id" class="form-input" required>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" for="trial_days">Trial days</label>
                    <input type="number" name="trial_days" id="trial_days" min="0" max="365" value="0" class="form-input" style="width: 100px;">
                </div>
                <x-ui.button variant="outline" type="submit">Apply</x-ui.button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent payments</h3></div>
        <div class="card-body">
            @forelse($workspace->payments as $payment)
                <div class="flex justify-between py-2 border-b border-[var(--border)] last:border-0 text-sm">
                    <span>{{ strtoupper($payment->currency) }} {{ number_format($payment->amount / 100, 2) }}</span>
                    <span class="text-[var(--muted-foreground)]">{{ optional($payment->created_at)->format('M j, Y') }} · {{ $payment->status }}</span>
                </div>
            @empty
                <p class="text-sm text-[var(--muted-foreground)]">No payments yet.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Subscription history</h3></div>
        <div class="card-body">
            @forelse($workspace->subscriptions as $subscription)
                <div class="flex justify-between py-2 border-b border-[var(--border)] last:border-0 text-sm">
                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="hover:underline">{{ $subscription->plan->name }}</a>
                    <span class="text-[var(--muted-foreground)]">{{ $subscription->status }}</span>
                </div>
            @empty
                <p class="text-sm text-[var(--muted-foreground)]">No subscriptions yet.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent invoices</h3></div>
        <div class="card-body">
            @forelse($workspace->invoices as $invoice)
                <div class="flex justify-between py-2 border-b border-[var(--border)] last:border-0 text-sm">
                    <span>{{ $invoice->number }}</span>
                    <span class="text-[var(--muted-foreground)]">{{ optional($invoice->created_at)->format('M j, Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-[var(--muted-foreground)]">No invoices yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
