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

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">$</div>
        <div class="stat-label">MRR</div>
        <div class="stat-value">${{ number_format($mrrMinor / 100, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">↗</div>
        <div class="stat-label">ARR</div>
        <div class="stat-value">${{ number_format($arrMinor / 100, 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">↓</div>
        <div class="stat-label">Churn rate</div>
        <div class="stat-value">{{ $churnRate }}%</div>
        <div class="stat-label">canceled / active at month start</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">✓</div>
        <div class="stat-label">Trial conversion</div>
        <div class="stat-value">{{ $trialConversionRate }}%</div>
        <div class="stat-label">activated / started this month</div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">◆</div>
        <div class="stat-label">Active subscriptions</div>
        <div class="stat-value">{{ $activeCount }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">⏳</div>
        <div class="stat-label">Trials in progress</div>
        <div class="stat-value">{{ $trialCount }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $failedPayments > 0 ? 'stat-icon-danger' : 'stat-icon-success' }}">!</div>
        <div class="stat-label">Failed payments (month)</div>
        <div class="stat-value">{{ $failedPayments }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">⌂</div>
        <div class="stat-label">Workspaces</div>
        <div class="stat-value">{{ $workspaceCount }}</div>
        <a href="{{ route('admin.workspaces.index') }}" class="stat-label" style="text-decoration:underline">Manage →</a>
    </div>
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
@endsection
