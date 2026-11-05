@extends('tyro-dashboard::layouts.admin')

@section('title', 'Support Inbox')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Support Inbox</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Support Inbox</h1>
            <p class="page-description">Replying requires the support.reply privilege.</p>
        </div>
    </div>
</div>

<div class="tabs">
    <a href="{{ route('admin.support.index') }}" class="tab-link {{ $filterStatus === '' ? 'active' : '' }}">All ({{ $statusCounts->sum() }})</a>
    @foreach(['open', 'pending', 'closed'] as $status)
        <a href="{{ route('admin.support.index', ['status' => $status]) }}" class="tab-link {{ $filterStatus === $status ? 'active' : '' }}">
            {{ ucfirst($status) }} ({{ $statusCounts[$status] ?? 0 }})
        </a>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters-bar" style="width: 100%;">
            <div class="filter-group">
                <span class="filter-label">Status</span>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['open', 'pending', 'closed'] as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button variant="outline" size="sm" type="submit">Filter</x-ui.button>
        </form>
    </div>

    @if($tickets->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No tickets" description="Support requests will appear here." />
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>From</th>
                        <th>Workspace</th>
                        <th>Updated</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('admin.support.show', $ticket) }}" class="font-semibold hover:underline">{{ $ticket->subject }}</a>
                                <div class="text-sm text-[var(--muted-foreground)]">{{ $ticket->number }}</div>
                            </td>
                            <td>{{ $ticket->user?->email ?? $ticket->guest_email ?? '—' }}</td>
                            <td>{{ $ticket->workspace?->name ?? '—' }}</td>
                            <td>{{ $ticket->updated_at?->diffForHumans() }}</td>
                            <td>
                                <x-ui.badge variant="{{ $ticket->status === 'open' ? 'success' : ($ticket->status === 'pending' ? 'warning' : 'muted') }}">{{ ucfirst($ticket->status) }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $tickets->links() }}
@endsection
