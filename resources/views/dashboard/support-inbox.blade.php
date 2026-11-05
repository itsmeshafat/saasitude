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
        <form method="GET" class="flex gap-2">
            <select name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach(['open', 'pending', 'closed'] as $status)
                    <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-ui.button variant="outline" type="submit">Filter</x-ui.button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        @forelse($tickets as $ticket)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0">
                <div>
                    <a href="{{ route('admin.support.show', $ticket) }}" class="font-semibold hover:underline">{{ $ticket->subject }}</a>
                    <div class="text-sm text-[var(--muted-foreground)]">
                        {{ $ticket->number }} · {{ $ticket->user?->email }} · {{ $ticket->workspace?->name }} ·
                        updated {{ $ticket->updated_at?->diffForHumans() }}
                    </div>
                </div>
                <x-ui.badge variant="{{ $ticket->status === 'open' ? 'success' : ($ticket->status === 'pending' ? 'warning' : 'muted') }}">{{ ucfirst($ticket->status) }}</x-ui.badge>
            </div>
        @empty
            <x-ui.empty-state title="No tickets" description="Support requests will appear here." />
        @endforelse

        {{ $tickets->links() }}
    </div>
</div>
@endsection
