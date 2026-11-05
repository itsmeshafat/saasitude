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
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters-bar" style="width: 100%;">
            <div class="filter-group">
                <span class="filter-label">Status</span>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Gateway</span>
                <select name="gateway" class="form-select">
                    <option value="">All</option>
                    @foreach($gateways as $gateway)
                        <option value="{{ $gateway }}" @selected($filters['gateway'] === $gateway)>{{ ucfirst($gateway) }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button variant="outline" size="sm" type="submit">Filter</x-ui.button>
        </form>
    </div>

    @if($subscriptions->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No subscriptions found" description="Adjust the filters and try again." />
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Workspace</th>
                        <th>Plan</th>
                        <th>Owner</th>
                        <th>Renews</th>
                        <th>Gateway</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptions as $subscription)
                        <tr>
                            <td>
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="font-semibold hover:underline">
                                    {{ $subscription->workspace->name }}
                                </a>
                            </td>
                            <td>{{ $subscription->plan->name }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-cell-avatar">{{ substr($subscription->workspace->owner->name ?? '?', 0, 1) }}</div>
                                    <span>{{ $subscription->workspace->owner->email }}</span>
                                </div>
                            </td>
                            <td>{{ optional($subscription->current_period_end)->format('M j, Y') ?? '—' }}</td>
                            <td><x-ui.badge variant="info">{{ ucfirst($subscription->gateway) }}</x-ui.badge></td>
                            <td><x-ui.status-pill :status="$subscription->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $subscriptions->links() }}
@endsection
