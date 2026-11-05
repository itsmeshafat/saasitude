@extends('tyro-dashboard::layouts.user')

@section('title', 'Payment Received')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Payment received</h1>
            <p class="page-description">
                We're processing your payment — please don't refresh this page.
                Your subscription is confirmed by our payment provider, which usually
                takes less than a minute.
            </p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-sm text-[var(--muted-foreground)]">
            This page only shows that you completed checkout. Your plan activates once the
            payment is verified — you can check the current status on your billing page.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('dashboard.billing') }}"><x-ui.button variant="primary">Go to billing</x-ui.button></a>
            <a href="{{ route($dashboardRoute::name('index')) }}"><x-ui.button variant="outline">Go to dashboard</x-ui.button></a>
        </div>
    </div>
</div>
@endsection
