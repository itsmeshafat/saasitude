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
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="gateway" class="form-input">
                <option value="">All gateways</option>
                @foreach($gateways as $gateway)
                    <option value="{{ $gateway }}" @selected($filters['gateway'] === $gateway)>{{ ucfirst($gateway) }}</option>
                @endforeach
            </select>
            <select name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-ui.button variant="outline" type="submit">Filter</x-ui.button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        @forelse($events as $event)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0">
                <div>
                    <a href="{{ route('admin.webhook-events.show', $event) }}" class="font-semibold hover:underline">{{ $event->event_id }}</a>
                    <div class="text-sm text-[var(--muted-foreground)]">
                        {{ ucfirst($event->gateway) }} · attempts {{ $event->attempts }}
                        · {{ optional($event->created_at)->format('M j, Y H:i') }}
                    </div>
                    @if($event->error_message)
                        <div class="text-xs text-[var(--destructive)]">{{ Str::limit($event->error_message, 120) }}</div>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if($event->status === 'processed')
                        <x-ui.badge variant="success">Processed</x-ui.badge>
                    @elseif($event->status === 'failed')
                        <x-ui.badge variant="danger">Failed</x-ui.badge>
                    @else
                        <x-ui.badge variant="warning">Pending</x-ui.badge>
                    @endif
                    @if($user->hasPrivilege('webhooks.replay'))
                        <form method="POST" action="{{ route('admin.webhook-events.replay', $event) }}">
                            @csrf
                            <x-ui.button variant="outline" size="sm" type="submit">Replay</x-ui.button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No webhook events" description="Gateway notifications will appear here as they arrive." />
        @endforelse

        {{ $events->links() }}
    </div>
</div>
@endsection
