@extends('tyro-dashboard::layouts.admin')

@section('title', 'Invoices')

@section('breadcrumb')
{{-- This view renders outside the dashboard/* composer scope, so no $dashboardRoute here. --}}
<a href="{{ url('/dashboard') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Invoices</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Invoices</h1>
            <p class="page-description">All invoices across workspaces.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters-bar">
            <div class="search-box">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by invoice # or workspace…" class="form-input">
            </div>
            <x-ui.button variant="outline" size="sm" type="submit">Search</x-ui.button>
            @if(request('search'))
                <a href="{{ route('admin.billing.invoices') }}" class="filter-label">Clear</a>
            @endif
            <x-ui.button href="{ route('admin.exports.csv', 'invoices', request()->query()) }" variant="outline" size="sm" class="ml-auto">Export CSV</x-ui.button>
        </form>
    </div>

    @if($invoices->isEmpty())
        <div class="card-body">
            <x-ui.empty-state title="No invoices found" description="Invoices will appear here as payments are received." />
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Workspace</th>
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
                            <td>{{ $invoice->workspace?->name }}</td>
                            <td>{{ $invoice->issued_at?->format('M j, Y') ?? $invoice->created_at->format('M j, Y') }}</td>
                            <td>{{ $invoice->formattedAmount() }}</td>
                            <td>
                                <x-ui.badge variant="{{ $invoice->status === 'paid' ? 'success' : 'secondary' }}">{{ ucfirst($invoice->status) }}</x-ui.badge>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if($invoice->pdf_path)
                                        <x-ui.button href="{{ route('admin.billing.invoices.download', $invoice) }}" variant="secondary" size="sm">PDF</x-ui.button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.billing.invoices.regenerate', $invoice) }}"
                                          x-data @submit.prevent="showConfirm('Regenerate PDF?', 'The stored PDF will be rebuilt from the current invoice data.', () => $el.submit())">
                                        @csrf
                                        <x-ui.button type="submit" variant="secondary" size="sm">Regenerate</x-ui.button>
                                    </form>
                                </div>
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
