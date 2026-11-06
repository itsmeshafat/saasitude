@extends('tyro-dashboard::layouts.user')

@section('title', 'Invoices')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.billing') }}">Billing</a>
<span class="breadcrumb-separator">/</span>
<span>Invoices</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Invoices</h1>
            <p class="page-description">Download receipts for your payments.</p>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">$</div>
        <div class="stat-label">Paid this year</div>
        <div class="stat-value">${{ number_format($paidThisYear / 100, 2) }}</div>
        <div class="stat-label">{{ now()->format('Y') }} · paid invoices</div>
    </div>
</div>

<div class="card">
    @if($invoices->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No invoices yet" description="Invoices appear here after your first payment.">
                <x-slot:action>
                    <x-ui.button href="{{ route('dashboard.plans') }}" variant="primary">View plans</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $invoice->issued_at?->format('M j, Y') ?? $invoice->created_at->format('M j, Y') }}</td>
                            <td>{{ $invoice->formattedAmount() }}</td>
                            <td>
                                <x-ui.badge variant="{{ $invoice->status === 'paid' ? 'success' : 'secondary' }}">{{ ucfirst($invoice->status) }}</x-ui.badge>
                            </td>
                            <td>
                                @if($invoice->pdf_path)
                                    <x-ui.button href="{{ route('dashboard.billing.invoices.download', $invoice) }}" variant="secondary" size="sm">Download PDF</x-ui.button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $invoices->links() }}
@endsection
