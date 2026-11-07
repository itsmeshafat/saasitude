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

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header"><h3 class="card-title">State timeline</h3></div>
        @if($timeline->isEmpty())
            <div class="card-body"><x-ui.empty-state title="No events recorded" /></div>
        @else
            <div class="table-container">
                <table class="table table-compact">
                    <tbody>
                        @foreach($timeline as $entry)
                            <tr>
                                <td class="font-medium">{{ $entry['event'] }}@if($entry['detail']) <span class="text-[var(--muted-foreground)]">({{ $entry['detail'] }})</span>@endif</td>
                                <td class="text-right">{{ optional($entry['at'])->format('M j, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Details</h3></div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-compact">
                    <tbody>
                        <tr><td>Status</td><td class="text-right"><x-ui.status-pill :status="$subscription->status" /></td></tr>
                        <tr><td>Trial ends</td><td class="text-right">{{ optional($subscription->trial_ends_at)->format('M j, Y') ?? '—' }}</td></tr>
                        <tr><td>Current period</td><td class="text-right">{{ optional($subscription->current_period_start)->format('M j') }} → {{ optional($subscription->current_period_end)->format('M j, Y') }}</td></tr>
                        <tr><td>Cancels at period end</td><td class="text-right">{{ $subscription->cancel_at_period_end ? 'Yes' : 'No' }}</td></tr>
                        <tr><td>App-driven renewal</td><td class="text-right">{{ $subscription->app_driven_renewal ? 'Yes' : 'No' }}</td></tr>
                        @if($subscription->metadata['cancellation_reason'] ?? null)
                            <tr><td>Cancel reason</td><td class="text-right">{{ $subscription->metadata['cancellation_reason'] }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
