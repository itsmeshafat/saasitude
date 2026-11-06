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
    <div class="card-header">
        <form method="GET" class="filters-bar" onsubmit="return false;">
            <div class="search-box">
                <input type="text" id="template-search" placeholder="Filter templates…" class="form-input" autocomplete="off">
            </div>
        </form>
    </div>
    <div class="card-body" id="template-list">
        @forelse($templates as $template)
            <div class="template-row flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0" data-name="{{ strtolower($template['subject']) }}">
                <a href="{{ route('admin.email-templates.show', $template['key']) }}" class="font-semibold hover:underline">{{ $template['subject'] }}</a>
                <div class="flex items-center gap-2">
                    <x-ui.button href="{{ route('admin.email-templates.show', $template['key']) }}" variant="outline" size="sm">Preview</x-ui.button>
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
        <p id="template-empty" class="text-sm text-[var(--muted-foreground)] text-center py-6" hidden>No templates match your filter.</p>
    </div>
</div>

<script>
    document.getElementById('template-search').addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        var visible = 0;
        document.querySelectorAll('.template-row').forEach(function (row) {
            var show = row.dataset.name.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('template-empty').hidden = visible > 0;
    });
</script>
@endsection
