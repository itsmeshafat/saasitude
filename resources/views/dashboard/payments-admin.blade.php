@extends('tyro-dashboard::layouts.admin')

@section('title', 'Payments & Refunds')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Payments</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payments & Refunds</h1>
            <p class="page-description">All payments across gateways. Refunds require the billing.refund privilege.</p>
        </div>
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Gateway ref or workspace…" class="form-input">
            <select name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-ui.button variant="outline" type="submit">Filter</x-ui.button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        @forelse($payments as $payment)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-[var(--border)] last:border-0">
                <div>
                    <div class="font-semibold">{{ strtoupper($payment->currency) }} {{ number_format($payment->amount / 100, 2) }}</div>
                    <div class="text-sm text-[var(--muted-foreground)]">
                        {{ $payment->workspace->name ?? '—' }}
                        · {{ ucfirst($payment->gateway) }}
                        · {{ optional($payment->created_at)->format('M j, Y') }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($payment->status === 'succeeded')
                        <x-ui.badge variant="success">Succeeded</x-ui.badge>
                        @if($user->hasPrivilege('billing.refund'))
                            <form method="POST" action="{{ route('admin.payments.refund', $payment) }}"
                                  x-data @submit.prevent="showDanger('Refund payment?', 'This issues a full refund of '.strtoupper($payment->currency).' '.number_format($payment->amount / 100, 2).' and cannot be undone.', () => $el.submit())">
                                @csrf
                                <x-ui.button variant="danger" size="sm" type="submit">Refund</x-ui.button>
                            </form>
                        @endif
                    @elseif($payment->status === 'refunded')
                        <x-ui.badge variant="warning">Refunded</x-ui.badge>
                    @elseif($payment->status === 'failed')
                        <x-ui.badge variant="danger">Failed</x-ui.badge>
                    @else
                        <x-ui.badge variant="muted">{{ ucfirst($payment->status) }}</x-ui.badge>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No payments found" description="Adjust the filters and try again." />
        @endforelse

        {{ $payments->links() }}
    </div>
</div>
@endsection
