@extends('tyro-dashboard::layouts.admin')

@section('title', 'Subscriptions')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Subscriptions</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Subscriptions</h1>
            <p class="page-description">Filter by status and gateway.</p>
        </div>
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="gateway" class="form-input">
                <option value="">All gateways</option>
                @foreach($gateways as $gateway)
                    <option value="{{ $gateway }}" @selected($filters['gateway'] === $gateway)>{{ ucfirst($gateway) }}</option>
                @endforeach
            </select>
            <x-ui.button variant="outline" type="submit">Filter</x-ui.button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @forelse($subscriptions as $subscription)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0">
                <div>
                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="font-semibold hover:underline">
                        {{ $subscription->workspace->name }} · {{ $subscription->plan->name }}
                    </a>
                    <div class="text-sm text-[var(--muted-foreground)]">
                        {{ $subscription->workspace->owner->email }}
                        · renews {{ optional($subscription->current_period_end)->format('M j, Y') ?? '—' }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.badge variant="info">{{ ucfirst($subscription->gateway) }}</x-ui.badge>
                    <x-ui.status-pill :status="$subscription->status" />
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No subscriptions found" description="Adjust the filters and try again." />
        @endforelse

        {{ $subscriptions->links() }}
    </div>
</div>
@endsection
