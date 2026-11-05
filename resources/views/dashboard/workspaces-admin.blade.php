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
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters-bar" style="width: 100%;">
            <div class="search-box">
                <input type="text" name="search" value="{{ $search }}" placeholder="Name or owner email…" class="form-input">
            </div>
            <x-ui.button variant="outline" size="sm" type="submit">Search</x-ui.button>
            @if($search !== '')
                <a href="{{ route('admin.workspaces.index') }}" class="filter-label">Clear</a>
            @endif
            <a href="{ route("admin.exports.csv', 'workspaces'", request()->query()) }" class="btn btn-sm btn-outline-btn" style="margin-left:auto">Export CSV</a>
        </form>
    </div>

    @if($workspaces->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No workspaces found" description="Try a different search term." />
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Workspace</th>
                        <th>Plan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workspaces as $workspace)
                        <tr>
                            <td>
                                <a href="{{ route('admin.workspaces.show', $workspace) }}" class="font-semibold hover:underline">{{ $workspace->name }}</a>
                                <div class="text-sm text-[var(--muted-foreground)]">{{ $workspace->members_count }} member(s)</div>
                            </td>
                            <td>{{ $workspace->latestSubscription?->plan?->name ?? '—' }}</td>
                            <td>
                                <div class="action-buttons">
                                    @if($workspace->isSuspended())
                                        <x-ui.badge variant="danger">Suspended</x-ui.badge>
                                    @endif
                                    @if($workspace->latestSubscription)
                                        <x-ui.status-pill :status="$workspace->latestSubscription->status" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $workspaces->links() }}
@endsection
