@extends('tyro-dashboard::layouts.user')

@section('title', 'Data & Privacy')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Data &amp; Privacy</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Data &amp; Privacy</h1>
            <p class="page-description">Export everything we hold about you, or delete your account.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Export my data</h3></div>
        <div class="card-body">
            <p class="text-sm text-[var(--muted-foreground)] mb-4">
                A portable JSON file with your account, workspaces, memberships,
                subscriptions, payments and invoices. Exports are kept for 7 days.
            </p>
            <form method="POST" action="{{ route('dashboard.data-privacy.export') }}">
                @csrf
                <x-ui.button variant="primary" type="submit">Request export (JSON)</x-ui.button>
            </form>

            @if($exports->isNotEmpty())
                <div class="table-container mt-4">
                    <table class="table table-compact">
                        <tbody>
                            @foreach($exports as $export)
                                <tr>
                                    <td>
                                        Requested {{ $export->created_at?->format('M j, Y H:i') }}
                                        <div class="text-xs text-[var(--muted-foreground)]">
                                            @if($export->status === 'ready' && ! $export->expired())
                                                Expires {{ $export->expires_at?->format('M j, Y') }}
                                            @elseif($export->status === 'pending')
                                                Processing…
                                            @elseif($export->status === 'failed')
                                                Failed — try again
                                            @else
                                                Expired
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        @if($export->isReady())
                                            <x-ui.button href="{{ route('dashboard.data-privacy.download', $export) }}" variant="secondary" size="sm">Download</x-ui.button>
                                        @elseif($export->status === 'pending')
                                            <x-ui.badge variant="warning">Processing</x-ui.badge>
                                        @elseif($export->status === 'failed')
                                            <x-ui.badge variant="danger">Failed</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="muted">Expired</x-ui.badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Delete account</h3></div>
        <div class="card-body">
            <p class="text-sm text-[var(--muted-foreground)] mb-4">
                Permanently deletes your account and personal data. You must first cancel any
                active subscription and transfer ownership of shared workspaces.
            </p>
            <form method="POST" action="{{ route('dashboard.data-privacy.delete') }}"
                  x-data @submit.prevent="showDanger('Delete your account?', 'This permanently deletes your account and personal data. This cannot be undone.', () => $el.submit())">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="password">Confirm your password</label>
                    <input type="password" name="password" id="password" class="form-input" required autocomplete="current-password">
                    @error('deleteAccount.password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <x-ui.button variant="danger" type="submit">Delete my account</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
