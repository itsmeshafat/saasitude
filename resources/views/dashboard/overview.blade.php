@extends('tyro-dashboard::layouts.admin')

@section('title', 'Overview')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Overview</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Overview</h1>
            <p class="page-description">Billing metrics for {{ now()->format('F Y') }}.</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="card"><div class="card-body">
        <div class="text-sm text-[var(--muted-foreground)]">MRR</div>
        <div class="text-2xl font-bold">${{ number_format($mrrMinor / 100, 2) }}</div>
    </div></div>
    <div class="card"><div class="card-body">
        <div class="text-sm text-[var(--muted-foreground)]">ARR</div>
        <div class="text-2xl font-bold">${{ number_format($arrMinor / 100, 0) }}</div>
    </div></div>
    <div class="card"><div class="card-body">
        <div class="text-sm text-[var(--muted-foreground)]">Churn rate</div>
        <div class="text-2xl font-bold">{{ $churnRate }}%</div>
        <div class="text-xs text-[var(--muted-foreground)]">canceled / active at month start</div>
    </div></div>
    <div class="card"><div class="card-body">
        <div class="text-sm text-[var(--muted-foreground)]">Trial conversion</div>
        <div class="text-2xl font-bold">{{ $trialConversionRate }}%</div>
        <div class="text-xs text-[var(--muted-foreground)]">activated / started this month</div>
    </div></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card"><div class="card-body">
        <div class="text-sm text-[var(--muted-foreground)]">Active subscriptions</div>
        <div class="text-xl font-semibold">{{ $activeCount }}</div>
    </div></div>
    <div class="card"><div class="card-body">
        <div class="text-sm text-[var(--muted-foreground)]">Trials in progress</div>
        <div class="text-xl font-semibold">{{ $trialCount }}</div>
    </div></div>
    <div class="card"><div class="card-body">
        <div class="text-sm text-[var(--muted-foreground)]">Failed payments (month)</div>
        <div class="text-xl font-semibold {{ $failedPayments > 0 ? 'text-[var(--destructive)]' : '' }}">{{ $failedPayments }}</div>
    </div></div>
</div>

<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Active subscriptions by gateway</h3></div>
    <div class="card-body">
        @forelse($byGateway as $gateway => $count)
            <div class="flex items-center justify-between py-2 border-b border-[var(--border)] last:border-0">
                <x-ui.badge variant="info">{{ ucfirst($gateway) }}</x-ui.badge>
                <span class="font-semibold">{{ $count }}</span>
            </div>
        @empty
            <x-ui.empty-state title="No subscriptions yet" description="Active and trialing subscriptions will appear here." />
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Workspaces</h3></div>
    <div class="card-body">
        <div class="text-2xl font-bold">{{ $workspaceCount }}</div>
        <a href="{{ route('admin.workspaces.index') }}" class="text-sm text-[var(--primary)] underline-offset-4 hover:underline">Manage workspaces →</a>
    </div>
</div>
@endsection
