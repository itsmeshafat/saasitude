@extends('tyro-dashboard::layouts.admin')

@section('title', 'Workspaces')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Workspaces</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Workspaces</h1>
            <p class="page-description">Search, inspect and manage customer workspaces.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Name or owner email…"
                   class="form-input" style="min-width: 240px;">
            <x-ui.button variant="outline" type="submit">Search</x-ui.button>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-body">
        @forelse($workspaces as $workspace)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0">
                <div>
                    <a href="{{ route('admin.workspaces.show', $workspace) }}" class="font-semibold hover:underline">{{ $workspace->name }}</a>
                    <div class="text-sm text-[var(--muted-foreground)]">
                        {{ $workspace->members_count }} member(s) ·
                        {{ $workspace->latestSubscription?->plan?->name ?? 'No plan' }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($workspace->isSuspended())
                        <x-ui.badge variant="danger">Suspended</x-ui.badge>
                    @endif
                    @if($workspace->latestSubscription)
                        <x-ui.status-pill :status="$workspace->latestSubscription->status" />
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No workspaces found" description="Try a different search term." />
        @endforelse

        {{ $workspaces->links() }}
    </div>
</div>
@endsection
