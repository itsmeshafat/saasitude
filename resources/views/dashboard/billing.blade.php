@extends('tyro-dashboard::layouts.user')

@section('title', 'Billing')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Billing</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Billing</h1>
            <p class="page-description">Plan, usage and payment details for {{ $workspace->name }}.</p>
        </div>
    </div>
</div>


@if($subscription)
<div class="card mb-6">
    <div class="card-header flex flex-wrap items-center justify-between gap-3">
        <h3 class="card-title">Current plan</h3>
        <div class="flex items-center gap-2">
            <x-ui.badge variant="info">{{ strtoupper($subscription->gateway) }}</x-ui.badge>
            <x-ui.status-pill :status="$subscription->status" />
        </div>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm text-[var(--muted-foreground)]">Plan</div>
                <div class="font-semibold text-lg">{{ $subscription->plan->name }}</div>
            </div>
            <div>
                <div class="text-sm text-[var(--muted-foreground)]">
                    {{ $subscription->cancel_at_period_end ? 'Ends on' : 'Next renewal' }}
                </div>
                <div class="font-semibold text-lg">{{ optional($subscription->current_period_end)->format('M j, Y') ?? '—' }}</div>
            </div>
            @if($subscription->status === 'trialing' && $subscription->trial_ends_at)
            <div>
                <div class="text-sm text-[var(--muted-foreground)]">Trial ends</div>
                <div class="font-semibold text-lg">{{ $subscription->trial_ends_at->format('M j, Y') }}</div>
                <div class="text-xs text-[var(--muted-foreground)]">
                    {{ $subscription->trial_ends_at->diffForHumans() }}
                </div>
            </div>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 mt-6">
            <a href="{{ route('dashboard.plans') }}"><x-ui.button variant="primary">Change plan</x-ui.button></a>
            @if($hasPortal)
                <a href="{{ route('dashboard.billing.portal') }}"><x-ui.button variant="outline">Manage billing portal</x-ui.button></a>
            @endif
            <a href="{{ route('dashboard.billing.invoices') }}"><x-ui.button variant="outline">Invoices</x-ui.button></a>
            @if($subscription->isActive() && !$subscription->cancel_at_period_end)
                <a href="{{ route('dashboard.cancel-subscription') }}"><x-ui.button variant="danger">Cancel subscription</x-ui.button></a>
            @endif
        </div>
    </div>
</div>

@if($meters->isNotEmpty())
<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Usage this period</h3></div>
    <div class="card-body space-y-4">
        @foreach($meters as $meter)
            <x-ui.usage-meter
                :label="$meter['label']"
                :used="$meter['used']"
                :limit="$meter['limit']" />
        @endforeach
    </div>
</div>
@endif
@else
<div class="card">
    <div class="card-body">
        <x-ui.empty-state
            title="No active plan"
            description="Choose a plan to unlock features for this workspace." />
        <div class="mt-4 flex justify-center">
            <a href="{{ route('dashboard.plans') }}"><x-ui.button>View plans</x-ui.button></a>
        </div>
    </div>
</div>
@endif
@endsection
