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
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters-bar" style="width: 100%;">
            <div class="search-box">
                <input type="text" name="search" value="{{ $search }}" placeholder="Gateway ref or workspace…" class="form-input">
            </div>
            <div class="filter-group">
                <span class="filter-label">Status</span>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button variant="outline" size="sm" type="submit">Filter</x-ui.button>
        </form>
    </div>

    @if($payments->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No payments found" description="Adjust the filters and try again." />
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Workspace</th>
                        <th>Gateway</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td class="font-semibold">{{ strtoupper($payment->currency) }} {{ number_format($payment->amount / 100, 2) }}</td>
                            <td>{{ $payment->workspace->name ?? '—' }}</td>
                            <td><x-ui.badge variant="info">{{ ucfirst($payment->gateway) }}</x-ui.badge></td>
                            <td>{{ optional($payment->created_at)->format('M j, Y') }}</td>
                            <td>
                                @if($payment->status === 'succeeded')
                                    <x-ui.badge variant="success">Succeeded</x-ui.badge>
                                @elseif($payment->status === 'refunded')
                                    <x-ui.badge variant="warning">Refunded</x-ui.badge>
                                @elseif($payment->status === 'failed')
                                    <x-ui.badge variant="danger">Failed</x-ui.badge>
                                @else
                                    <x-ui.badge variant="muted">{{ ucfirst($payment->status) }}</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                @if($payment->status === 'succeeded' && $user->hasPrivilege('billing.refund'))
                                    <form method="POST" action="{{ route('admin.payments.refund', $payment) }}"
                                          x-data @submit.prevent="showDanger('Refund payment?', 'This issues a full refund of '.strtoupper($payment->currency).' '.number_format($payment->amount / 100, 2).' and cannot be undone.', () => $el.submit())">
                                        @csrf
                                        <x-ui.button variant="danger" size="sm" type="submit">Refund</x-ui.button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $payments->links() }}
@endsection
