@extends('tyro-dashboard::layouts.admin')

@section('title', 'Webhook Event')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('admin.webhook-events.index') }}">Webhook Events</a>
<span class="breadcrumb-separator">/</span>
<span>{{ Str::limit($event->event_id, 20) }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Webhook Event</h1>
            <p class="page-description">{{ $event->event_id }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($event->status === 'processed')
                <x-ui.badge variant="success">Processed</x-ui.badge>
            @elseif($event->status === 'failed')
                <x-ui.badge variant="danger">Failed</x-ui.badge>
            @else
                <x-ui.badge variant="warning">Pending</x-ui.badge>
            @endif
            @if($user->hasPrivilege('webhooks.replay'))
                <form method="POST" action="{{ route('admin.webhook-events.replay', $event) }}"
                      x-data @submit.prevent="showConfirm('Replay event?', 'The stored payload will be re-queued. Processing is idempotent — duplicates are no-ops.', () => $el.submit())">
                    @csrf
                    <x-ui.button variant="outline" size="sm" type="submit">Replay</x-ui.button>
                </form>
            @endif
        </div>
    </div>
</div>

@if($event->error_message)
    <div class="alert alert-error mb-4">{{ $event->error_message }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="card xl:col-span-1">
        <div class="card-header"><h3 class="card-title">Metadata</h3></div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-compact">
                    <tbody>
                        <tr><td>Gateway</td><td style="text-align:right"><x-ui.badge variant="info">{{ ucfirst($event->gateway) }}</x-ui.badge></td></tr>
                        <tr><td>Status</td><td style="text-align:right">{{ $event->status }}</td></tr>
                        <tr><td>Attempts</td><td style="text-align:right">{{ $event->attempts }}</td></tr>
                        <tr><td>Received</td><td style="text-align:right">{{ $event->created_at?->format('M j, Y H:i:s') }}</td></tr>
                        <tr><td>Processed</td><td style="text-align:right">{{ $event->processed_at?->format('M j, Y H:i:s') ?? '—' }}</td></tr>
                        <tr><td>Event ID</td><td style="text-align:right; word-break: break-all;">{{ $event->event_id }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card xl:col-span-2">
        <div class="card-header">
            <h3 class="card-title">Payload</h3>
            <button type="button" class="btn btn-sm btn-secondary" onclick="copyPayload(this)">Copy JSON</button>
        </div>
        <div class="card-body">
            <pre class="text-xs overflow-x-auto rounded-md bg-[var(--muted)] p-4" style="max-height: 60vh;"><code id="payload-json">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
        </div>
    </div>
</div>

<script>
    function copyPayload(btn) {
        var text = document.getElementById('payload-json').textContent;
        navigator.clipboard.writeText(text).then(function () {
            var original = btn.textContent;
            btn.textContent = 'Copied ✓';
            setTimeout(function () { btn.textContent = original; }, 1500);
        });
    }
</script>
@endsection
