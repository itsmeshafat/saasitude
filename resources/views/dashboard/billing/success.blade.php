@extends('tyro-dashboard::layouts.user')

@section('title', 'Payment Received')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payment received</h1>
            <p class="page-description">Thanks! Your checkout completed successfully.</p>
        </div>
    </div>
</div>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-2xl"
                 style="background: oklch(0.627 0.194 149 / 0.15); color: var(--success); border: 1px solid oklch(0.627 0.194 149 / 0.4);">✓</div>
            <div>
                <h3 class="card-title mb-2">What happens next</h3>
                <ol class="space-y-2 text-sm text-[var(--muted-foreground)] list-decimal pl-4">
                    <li>Your payment is verified by the gateway's webhook — usually under a minute.</li>
                    <li>Once verified, your plan activates automatically and this page's copy updates on your billing overview.</li>
                    <li>A receipt invoice will appear under <a href="{{ route('dashboard.billing.invoices') }}" class="text-[var(--primary)] hover:underline">Invoices</a>.</li>
                </ol>
            </div>
        </div>

        <div class="mt-6 rounded-lg p-4 text-sm" style="background: var(--muted);">
            <strong>Do not refresh or resubmit</strong> — the verification is automatic. If your plan
            hasn't activated after a few minutes, check Billing; the status there is always current.
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('dashboard.billing') }}"><x-ui.button variant="primary">Go to billing</x-ui.button></a>
            <a href="{{ route($dashboardRoute::name('index')) }}"><x-ui.button variant="outline">Go to dashboard</x-ui.button></a>
        </div>
    </div>
</div>
@endsection
