@extends('tyro-dashboard::layouts.user')

@section('title', 'Payment Methods')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.billing') }}">Billing</a>
<span class="breadcrumb-separator">/</span>
<span>Payment Methods</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payment Methods</h1>
            <p class="page-description">Manage how {{ $workspace->name }} pays for its subscription.</p>
        </div>
        @if($subscription)
            <x-ui.badge variant="info">{{ ucfirst($subscription->gateway) }}</x-ui.badge>
        @endif
    </div>
</div>


@if(in_array('portal', $gateway->capabilities()))
<div class="card">
    <div class="card-header"><h3 class="card-title">Billing portal</h3></div>
    <div class="card-body">
        <p class="text-sm text-[var(--muted-foreground)] mb-4">
            Cards and payment methods for {{ ucfirst($gateway->key()) }} are managed securely in the
            billing portal. Updating a card there applies to future renewals automatically.
        </p>
        <x-ui.button href="{{ route('dashboard.billing.portal') }}" variant="primary">Open {{ ucfirst($gateway->key()) }} billing portal</x-ui.button>
    </div>
</div>
@elseif($lastPayment)
<div class="card">
    <div class="card-header"><h3 class="card-title">Last payment</h3></div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm text-[var(--muted-foreground)]">Amount</div>
                <div class="font-semibold">{{ strtoupper($lastPayment->currency) }} {{ number_format($lastPayment->amount / 100, 2) }}</div>
            </div>
            <div>
                <div class="text-sm text-[var(--muted-foreground)]">Date</div>
                <div class="font-semibold">{{ optional($lastPayment->created_at)->format('M j, Y') }}</div>
            </div>
            <div>
                <div class="text-sm text-[var(--muted-foreground)]">Status</div>
                <div class="font-semibold">{{ ucfirst($lastPayment->status) }}</div>
            </div>
        </div>
        <p class="text-sm text-[var(--muted-foreground)] mt-4">
            SSLCommerz payments are made per-invoice — card details are never stored on our servers.
        </p>
    </div>
</div>
@else
<div class="card">
    <div class="card-body">
        <x-ui.empty-state
            title="No payment method yet"
            description="Subscribe to a plan to make your first payment." />
    </div>
</div>
@endif
@endsection
