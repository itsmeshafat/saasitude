@extends('tyro-dashboard::layouts.admin')

@section('title', $ticket->number)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('admin.support.index') }}">Support Inbox</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $ticket->number }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $ticket->subject }}</h1>
            <p class="page-description">
                {{ $ticket->number }} · {{ $ticket->user?->email }} · workspace {{ $ticket->workspace?->name ?? '—' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.badge variant="{{ $ticket->status === 'open' ? 'success' : ($ticket->status === 'pending' ? 'warning' : 'muted') }}">{{ ucfirst($ticket->status) }}</x-ui.badge>
            @if($ticket->status !== 'closed' && $user->hasPrivilege('support.reply'))
                <form method="POST" action="{{ route('admin.support.close', $ticket) }}"
                      x-data @submit.prevent="showDanger('Close ticket?', 'The customer will be notified that this ticket is closed.', () => $el.submit())">
                    @csrf
                    <x-ui.button variant="danger" size="sm" type="submit">Close ticket</x-ui.button>
                </form>
            @endif
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header"><h3 class="card-title">Conversation</h3></div>
    <div class="card-body">
        @forelse($ticket->messages as $message)
            <div class="mb-4 {{ $message->is_staff ? 'bg-[var(--muted)] rounded-lg p-4' : '' }}">
                <div class="text-xs text-[var(--muted-foreground)] mb-1">
                    {{ $message->is_staff ? 'Staff' : $message->user?->name }} · {{ $message->created_at?->diffForHumans() }}
                </div>
                <p class="text-sm whitespace-pre-line">{{ $message->body }}</p>
            </div>
        @empty
            <p class="text-sm text-[var(--muted-foreground)]">No messages.</p>
        @endforelse

        @if($ticket->status !== 'closed' && $user->hasPrivilege('support.reply'))
            <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="mt-4">
                @csrf
                <textarea name="body" rows="3" class="form-input" placeholder="Write a reply…" required></textarea>
                @error('body')<p class="text-sm text-[var(--destructive)] mt-1">{{ $message }}</p>@enderror
                <div class="mt-2">
                    <x-ui.button variant="primary" type="submit">Send reply</x-ui.button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
