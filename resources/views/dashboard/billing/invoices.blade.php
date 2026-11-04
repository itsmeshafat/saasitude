@extends('tyro-dashboard::layouts.user')

@section('title', 'Invoices')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
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

@if($invoices->isEmpty())
    <p class="text-gray-500">No invoices yet.</p>
@else
    <div class="card">
        <table class="table w-full">
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
                    <td><span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : 'secondary' }}">{{ ucfirst($invoice->status) }}</span></td>
                    <td>
                        @if($invoice->pdf_path)
                            <a href="{{ route('dashboard.billing.invoices.download', $invoice) }}" class="btn btn-sm btn-secondary">
                                Download PDF
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
@endif
@endsection
