@extends('tyro-dashboard::layouts.user')

@section('title', 'Notifications')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Notifications</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-description">{{ $unreadCount }} unread.</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('dashboard.notifications.read-all') }}">
                @csrf
                <x-ui.button variant="outline" type="submit">Mark all as read</x-ui.button>
            </form>
        @endif
    </div>
</div>


<div class="card mb-6">
    <div class="card-body">
        @forelse($notifications as $notification)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0 {{ $notification->read_at ? 'opacity-70' : '' }}">
                <div>
                    <div class="font-semibold">{{ $notification->title }}
                        @unless($notification->read_at)
                            <x-ui.badge variant="info">New</x-ui.badge>
                        @endunless
                    </div>
                    @if($notification->body)
                        <div class="text-sm text-[var(--muted-foreground)]">{{ $notification->body }}</div>
                    @endif
                    <div class="text-xs text-[var(--muted-foreground)] mt-1">{{ $notification->created_at?->diffForHumans() }}</div>
                </div>
                <div class="flex items-center gap-2">
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}"><x-ui.button variant="outline" size="sm">View</x-ui.button></a>
                    @endif
                    @unless($notification->read_at)
                        <form method="POST" action="{{ route('dashboard.notifications.read', $notification->id) }}">
                            @csrf
                            <x-ui.button variant="ghost" size="sm" type="submit">Mark read</x-ui.button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No notifications" description="Billing and team activity will appear here." />
        @endforelse

        {{ $notifications->links() }}
    </div>
</div>
@endsection
