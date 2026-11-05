@extends('tyro-dashboard::layouts.admin')

@section('title', 'Subscription')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $subscription->workspace->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $subscription->plan->name }} — {{ $subscription->workspace->name }}</h1>
            <p class="page-description">
                {{ $subscription->workspace->owner->email }}
                · gateway {{ ucfirst($subscription->gateway) }}
                @if($subscription->gateway_subscription_id) · ref {{ $subscription->gateway_subscription_id }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.status-pill :status="$subscription->status" />
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">State timeline</h3></div>
        <div class="card-body">
            @forelse($timeline as $entry)
                <div class="flex justify-between py-2 border-b border-[var(--border)] last:border-0 text-sm">
                    <span class="font-medium">{{ $entry['event'] }}@if($entry['detail']) <span class="text-[var(--muted-foreground)]">({{ $entry['detail'] }})</span>@endif</span>
                    <span class="text-[var(--muted-foreground)]">{{ optional($entry['at'])->format('M j, Y H:i') }}</span>
                </div>
            @empty
                <p class="text-sm text-[var(--muted-foreground)]">No events recorded.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Details</h3></div>
        <div class="card-body space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-[var(--muted-foreground)]">Status</span><span class="font-medium">{{ $subscription->status }}</span></div>
            <div class="flex justify-between"><span class="text-[var(--muted-foreground)]">Trial ends</span><span>{{ optional($subscription->trial_ends_at)->format('M j, Y') ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-[var(--muted-foreground)]">Current period</span><span>{{ optional($subscription->current_period_start)->format('M j') }} → {{ optional($subscription->current_period_end)->format('M j, Y') }}</span></div>
            <div class="flex justify-between"><span class="text-[var(--muted-foreground)]">Cancels at period end</span><span>{{ $subscription->cancel_at_period_end ? 'Yes' : 'No' }}</span></div>
            <div class="flex justify-between"><span class="text-[var(--muted-foreground)]">App-driven renewal</span><span>{{ $subscription->app_driven_renewal ? 'Yes' : 'No' }}</span></div>
            @if($subscription->metadata['cancellation_reason'] ?? null)
                <div class="flex justify-between"><span class="text-[var(--muted-foreground)]">Cancel reason</span><span>{{ $subscription->metadata['cancellation_reason'] }}</span></div>
            @endif
        </div>
    </div>
</div>
@endsection
