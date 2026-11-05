@extends('tyro-dashboard::layouts.admin')

@section('title', 'Email Templates')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Email Templates</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Email Templates</h1>
            <p class="page-description">Preview every transactional email with sample data. Test sending requires the settings.manage privilege.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @forelse($templates as $template)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0">
                <a href="{{ route('admin.email-templates.show', $template['key']) }}" class="font-semibold hover:underline">{{ $template['subject'] }}</a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.email-templates.show', $template['key']) }}">
                        <x-ui.button variant="outline" size="sm">Preview</x-ui.button>
                    </a>
                    @if($user->hasPrivilege('settings.manage'))
                        <form method="POST" action="{{ route('admin.email-templates.send-test', $template['key']) }}">
                            @csrf
                            <x-ui.button variant="ghost" size="sm" type="submit">Send test to me</x-ui.button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No templates" />
        @endforelse
    </div>
</div>
@endsection
