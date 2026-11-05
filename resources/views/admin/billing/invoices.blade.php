@extends('tyro-dashboard::layouts.admin')

@section('title', 'Invoices')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Invoices</h1>
        </div>
    </div>
</div>

<form method="GET" class="mb-4 flex gap-2" style="max-width: 400px;">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search by invoice # or workspace…"
           class="form-input flex-1">
    <button type="submit" class="btn btn-secondary">Search</button>
    @if(request('search'))
        <a href="{{ route('admin.billing.invoices') }}" class="btn btn-secondary">Clear</a>
    @endif
</form>


<div class="card">
    <table class="table w-full">
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
            @forelse($invoices as $invoice)
            <tr>
                <td>{{ $invoice->number }}</td>
                <td>{{ $invoice->workspace?->name }}</td>
                <td>{{ $invoice->issued_at?->format('M j, Y') ?? $invoice->created_at->format('M j, Y') }}</td>
                <td>{{ $invoice->formattedAmount() }}</td>
                <td><span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : 'secondary' }}">{{ ucfirst($invoice->status) }}</span></td>
                <td class="flex gap-2">
                    @if($invoice->pdf_path)
                        <a href="{{ route('admin.billing.invoices.download', $invoice) }}" class="btn btn-sm btn-secondary">PDF</a>
                    @endif
                    <form method="POST" action="{{ route('admin.billing.invoices.regenerate', $invoice) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-secondary">Regenerate</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-gray-500 text-center py-4">No invoices found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $invoices->links() }}
</div>
@endsection
