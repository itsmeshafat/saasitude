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
            <p class="page-description">
                {{ ucfirst($event->gateway) }} · {{ $event->event_id }}
                · status {{ $event->status }} · attempts {{ $event->attempts }}
                @if($event->error_message) · error: {{ $event->error_message }} @endif
            </p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Payload</h3></div>
    <div class="card-body">
        <pre class="text-xs overflow-x-auto rounded-md bg-[var(--muted)] p-4"><code>{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
    </div>
</div>
@endsection
