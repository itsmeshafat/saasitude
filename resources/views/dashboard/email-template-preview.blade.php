@extends('tyro-dashboard::layouts.admin')

@section('title', 'Email Preview')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('admin.email-templates') }}">Email Templates</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $subject }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $subject }}</h1>
            <p class="page-description">Rendered with sample data.</p>
        </div>
        @if($user->hasPrivilege('settings.manage'))
            <form method="POST" action="{{ route('admin.email-templates.send-test', $key) }}">
                @csrf
                <x-ui.button variant="primary" type="submit">Send test to me</x-ui.button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <iframe srcdoc="{{ $html }}" title="{{ $subject }}" class="w-full rounded-md border border-[var(--border)]" style="height: 75vh; background: #f9fafb;"></iframe>
    </div>
</div>
@endsection
