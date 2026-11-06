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

<div class="tabs">
    <a href="{{ route('dashboard.notifications') }}" class="tab-link {{ $filter === 'all' ? 'active' : '' }}">All ({{ $totalCount }})</a>
    <a href="{{ route('dashboard.notifications', ['filter' => 'unread']) }}" class="tab-link {{ $filter === 'unread' ? 'active' : '' }}">Unread ({{ $unreadCount }})</a>
    <a href="{{ route('dashboard.notifications', ['filter' => 'read']) }}" class="tab-link {{ $filter === 'read' ? 'active' : '' }}">Read ({{ $totalCount - $unreadCount }})</a>
</div>


<div class="card mb-4">
    @if($notifications->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No notifications" description="Billing and team activity will appear here." />
        </div>
    @else
        <div class="table-container">
            <table class="table {{ $notifications->first()->read_at ? '' : '' }}">
                <tbody>
                    @foreach($notifications as $notification)
                        <tr class="{{ $notification->read_at ? 'table-no-hover opacity-70' : 'font-medium' }}">
                            <td>
                                {{ $notification->title }}
                                @unless($notification->read_at)
                                    <x-ui.badge variant="info">New</x-ui.badge>
                                @endunless
                                @if($notification->body)
                                    <div class="text-sm text-[var(--muted-foreground)]">{{ $notification->body }}</div>
                                @endif
                                <div class="text-xs text-[var(--muted-foreground)]">{{ $notification->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="text-right">
                                <div class="action-buttons">
                                    @if($notification->action_url)
                                        <x-ui.button href="{{ $notification->action_url }}" variant="outline" size="sm">View</x-ui.button>
                                    @endif
                                    @unless($notification->read_at)
                                        <form method="POST" action="{{ route('dashboard.notifications.read', $notification->id) }}">
                                            @csrf
                                            <x-ui.button variant="ghost" size="sm" type="submit">Mark read</x-ui.button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $notifications->links() }}
@endsection
