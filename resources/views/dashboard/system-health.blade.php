@extends('tyro-dashboard::layouts.admin')

@section('title', 'System Health')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>System Health</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">System Health</h1>
            <p class="page-description">Queue, scheduler, database and disk at a glance.</p>
        </div>
    </div>
</div>

<div class="card mb-6">
    <div class="card-body">
        @forelse($checks as $check)
            <div class="flex items-center justify-between py-3 border-b border-[var(--border)] last:border-0">
                <span class="font-medium">{{ $check['label'] }}</span>
                <span class="flex items-center gap-2">
                    <span>{{ $check['value'] }}</span>
                    @if($check['ok'])
                        <x-ui.badge variant="success">OK</x-ui.badge>
                    @else
                        <x-ui.badge variant="danger">Check</x-ui.badge>
                    @endif
                </span>
            </div>
        @empty
            <x-ui.empty-state title="No checks available" />
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Recent failed jobs</h3></div>
    <div class="card-body">
        @forelse($failedRecent as $job)
            <div class="py-2 border-b border-[var(--border)] last:border-0">
                <div class="text-sm font-medium">{{ $job->queue }}</div>
                <div class="text-xs text-[var(--muted-foreground)]">
                    {{ \Illuminate\Support\Str::limit($job->exception, 160) }}
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No failed jobs" description="Nothing in the failed-jobs table." />
        @endforelse
    </div>
</div>
@endsection
