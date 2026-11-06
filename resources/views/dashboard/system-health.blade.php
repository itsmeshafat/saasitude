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
            <p class="page-description">Queue, scheduler, database and disk at a glance. Auto-refreshes every 30s.</p>
        </div>
        <x-ui.button variant="outline" size="sm" onclick="location.reload()">Refresh now</x-ui.button>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon {{ $queueDepth < 100 ? 'stat-icon-success' : 'stat-icon-warning' }}">⧗</div>
        <div class="stat-label">Queue depth</div>
        <div class="stat-value">{{ $queueDepth }}</div>
        <div class="stat-label">pending jobs</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $failedJobs === 0 ? 'stat-icon-success' : 'stat-icon-danger' }}">!</div>
        <div class="stat-label">Failed jobs</div>
        <div class="stat-value">{{ $failedJobs }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">▤</div>
        <div class="stat-label">Database size</div>
        <div class="stat-value">{{ is_numeric($dbSizeMb) ? $dbSizeMb.' MB' : $dbSizeMb }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">◷</div>
        <div class="stat-label">Scheduler last run</div>
        <div class="stat-value" style="font-size: 1.1rem;">{{ $schedulerLastRun?->diffForHumans() ?? 'unknown' }}</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Disk usage</h3></div>
    <div class="card-body">
        <div class="flex justify-between text-sm mb-2">
            <span class="text-[var(--muted-foreground)]">{{ $diskFreeGb }} GB free of {{ $diskTotalGb }} GB</span>
            <span class="font-medium {{ $diskUsedPct > 90 ? 'text-[var(--destructive)]' : '' }}">{{ $diskUsedPct }}% used</span>
        </div>
        <div style="height: 10px; border-radius: 999px; background: var(--muted); overflow: hidden;">
            <div style="height: 100%; width: {{ min(100, $diskUsedPct) }}%; border-radius: 999px;
                        background: {{ $diskUsedPct > 90 ? 'var(--destructive)' : ($diskUsedPct > 75 ? 'var(--warning)' : 'var(--success)') }};"></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Checks</h3></div>
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
    @if($failedRecent->isEmpty())
        <div class="card-body"><x-ui.empty-state title="No failed jobs" description="Nothing in the failed-jobs table." /></div>
    @else
        <div class="table-container">
            <table class="table table-compact">
                <thead>
                    <tr><th>Queue</th><th>Failed at</th><th>Exception</th></tr>
                </thead>
                <tbody>
                    @foreach($failedRecent as $job)
                        <tr>
                            <td class="font-medium">{{ $job->queue }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($job->failed_at)->format('M j, Y H:i') }}</td>
                            <td class="text-sm text-[var(--muted-foreground)]">{{ \Illuminate\Support\Str::limit($job->exception, 140) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    // Auto-refresh every 30 seconds (only when the tab is visible).
    setInterval(function () {
        if (document.visibilityState === 'visible') location.reload();
    }, 30000);
</script>
@endsection
