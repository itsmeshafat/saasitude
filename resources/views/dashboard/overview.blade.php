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
        <a href="{{ route('admin.exports.report') }}">
            <x-ui.button variant="outline">Download monthly report (PDF)</x-ui.button>
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">$</div>
        <div class="stat-label">Revenue this month</div>
        <div class="stat-value">${{ number_format($revenueThisMonth / 100, 2) }}</div>
        @if($revenueDeltaPct !== null)
            <span class="stat-change {{ $revenueDeltaPct >= 0 ? 'stat-change-up' : 'stat-change-down' }}">
                {{ $revenueDeltaPct >= 0 ? '▲' : '▼' }} {{ abs($revenueDeltaPct) }}% vs last month
            </span>
        @endif
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">↗</div>
        <div class="stat-label">MRR (active plans)</div>
        <div class="stat-value">${{ number_format($mrrMinor / 100, 2) }}</div>
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

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="card xl:col-span-2">
        <div class="card-header"><h3 class="card-title">Revenue — last 12 months</h3></div>
        <div class="card-body">
            <x-chart.line :values="$revenueByMonth" :labels="$monthLabels" :height="200" />
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Payments by gateway</h3></div>
        <div class="card-body">
            <x-chart.donut
                :values="$gatewayMix->values()->all()"
                :labels="$gatewayMix->keys()->map(fn ($g) => ucfirst($g))->all()"
                :size="130" />
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="card xl:col-span-2">
        <div class="card-header"><h3 class="card-title">New subscriptions — last 12 months</h3></div>
        <div class="card-body">
            <x-chart.bars :values="$newSubsByMonth" :labels="$monthLabels" :height="150" />
        </div>
    </div>

    <div class="card">
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
</div>
@endsection
