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
        <x-ui.button href="{{ route('admin.exports.report') }}" variant="primary">Download monthly report (PDF)</x-ui.button>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="stat-label">Revenue this month</div>
        <div class="stat-value">${{ number_format($revenueThisMonth / 100, 2) }}</div>
        <span class="stat-change {{ ($revenueDeltaPct ?? 0) >= 0 ? 'stat-change-up' : 'stat-change-down' }}">
            {{ ($revenueDeltaPct ?? 0) >= 0 ? '▲' : '▼' }} {{ $revenueDeltaPct !== null ? abs($revenueDeltaPct).'%' : '—' }} <span class="opacity-70">vs last month</span>
        </span>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
        </div>
        <div class="stat-label">MRR (active plans)</div>
        <div class="stat-value">${{ number_format($mrrMinor / 100, 2) }}</div>
        <span class="stat-change stat-change-up">ARR ${{ number_format($arrMinor / 100, 0) }}</span>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
        </div>
        <div class="stat-label">Churn rate</div>
        <div class="stat-value">{{ $churnRate }}%</div>
        <span class="stat-change stat-change-down">canceled / active at start</span>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="stat-label">Trial conversion</div>
        <div class="stat-value">{{ $trialConversionRate }}%</div>
        <span class="stat-change stat-change-up">activated / started</span>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
        </div>
        <div class="stat-label">Active subscriptions</div>
        <div class="stat-value">{{ $activeCount }}</div>
        <span class="stat-change">{{ $trialCount }} trialing</span>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="stat-label">Trials in progress</div>
        <div class="stat-value">{{ $trialCount }}</div>
        <span class="stat-change">currently trialing</span>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $failedPayments > 0 ? 'stat-icon-danger' : 'stat-icon-success' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <div class="stat-label">Failed payments</div>
        <div class="stat-value">{{ $failedPayments }}</div>
        <span class="stat-change">this month</span>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
        </div>
        <div class="stat-label">Workspaces</div>
        <div class="stat-value">{{ $workspaceCount }}</div>
        <a href="{{ route('admin.workspaces.index') }}" class="stat-change underline">Manage →</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="card md:col-span-2">
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

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="card md:col-span-2">
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
