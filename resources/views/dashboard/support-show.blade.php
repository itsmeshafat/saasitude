@extends('tyro-dashboard::layouts.user')

@section('title', $ticket->number)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.support') }}">Support</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $ticket->number }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $ticket->subject }}</h1>
            <p class="page-description">
                {{ $ticket->number }} · {{ ucfirst($ticket->category) }} · opened {{ $ticket->created_at?->format('M j, Y') }}
            </p>
        </div>
        <x-ui.badge variant="{{ $ticket->status === 'open' ? 'success' : ($ticket->status === 'pending' ? 'warning' : 'muted') }}">{{ ucfirst($ticket->status) }}</x-ui.badge>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Conversation</h3></div>
    <div class="card-body">
        @forelse($ticket->messages as $message)
            <div class="mb-4 {{ $message->is_staff ? 'bg-[var(--muted)] rounded-lg p-4' : '' }}">
                <div class="text-xs text-[var(--muted-foreground)] mb-1">
                    {{ $message->is_staff ? 'Support team' : $message->user?->name }}
                    · {{ $message->created_at?->diffForHumans() }}
                </div>
                <p class="text-sm whitespace-pre-line">{{ $message->body }}</p>
            </div>
        @empty
            <p class="text-sm text-[var(--muted-foreground)]">No messages.</p>
        @endforelse

        @unless($ticket->status === 'closed')
            <form method="POST" action="{{ route('dashboard.support.reply', $ticket) }}" class="mt-4">
                @csrf
                <textarea name="body" rows="3" class="form-input" placeholder="Write a reply…" required></textarea>
                @error('body')<p class="text-sm text-[var(--destructive)] mt-1">{{ $message }}</p>@enderror
                <div class="mt-2">
                    <x-ui.button variant="primary" type="submit">Send reply</x-ui.button>
                </div>
            </form>
        @endunless
    </div>
</div>
@endsection
