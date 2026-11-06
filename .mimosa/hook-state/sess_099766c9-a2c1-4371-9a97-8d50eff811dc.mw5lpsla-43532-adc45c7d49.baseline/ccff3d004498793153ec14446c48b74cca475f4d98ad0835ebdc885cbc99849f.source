@extends('tyro-dashboard::layouts.admin')

@section('title', 'Webhook Events')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Webhook Events</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Webhook Events</h1>
            <p class="page-description">Inbound gateway notifications. Replay is idempotent — duplicates are no-ops.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters-bar">
            <div class="filter-group">
                <span class="filter-label">Gateway</span>
                <select name="gateway" class="form-select">
                    <option value="">All</option>
                    @foreach($gateways as $gateway)
                        <option value="{{ $gateway }}" @selected($filters['gateway'] === $gateway)>{{ ucfirst($gateway) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Status</span>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button variant="primary" size="sm" type="submit">Filter</x-ui.button>
        </form>
    </div>

    @if($events->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No webhook events" description="Gateway notifications will appear here as they arrive." />
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Gateway</th>
                        <th>Attempts</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr>
                            <td>
                                <a href="{{ route('admin.webhook-events.show', $event) }}" class="font-semibold hover:underline">{{ $event->event_id }}</a>
                                @if($event->error_message)
                                    <div class="text-xs text-[var(--destructive)]">{{ Str::limit($event->error_message, 80) }}</div>
                                @endif
                            </td>
                            <td><x-ui.badge variant="info">{{ ucfirst($event->gateway) }}</x-ui.badge></td>
                            <td>{{ $event->attempts }}</td>
                            <td>{{ optional($event->created_at)->format('M j, Y H:i') }}</td>
                            <td>
                                @if($event->status === 'processed')
                                    <x-ui.badge variant="success">Processed</x-ui.badge>
                                @elseif($event->status === 'failed')
                                    <x-ui.badge variant="danger">Failed</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning">Pending</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                @if($user->hasPrivilege('webhooks.replay'))
                                    <form method="POST" action="{{ route('admin.webhook-events.replay', $event) }}"
                                          x-data @submit.prevent="showConfirm('Replay event?', 'The stored payload will be re-queued. Processing is idempotent — duplicates are no-ops.', () => $el.submit())">
                                        @csrf
                                        <x-ui.button variant="outline" size="sm" type="submit">Replay</x-ui.button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $events->links() }}
@endsection
