@extends('tyro-dashboard::layouts.user')

@section('title', 'Checkout Cancelled')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Checkout cancelled</h1>
            <p class="page-description">No charge was made — nothing to undo.</p>
        </div>
    </div>
</div>

<div class="card" style="max-width: 640px;">
    <div class="card-body">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-xl"
                 style="background: var(--muted); color: var(--muted-foreground);">↩</div>
            <div>
                <h3 class="card-title mb-1">Your checkout was cancelled</h3>
                <p class="text-sm text-[var(--muted-foreground)]">
                    You exited the payment flow before completing it. Your current plan and billing
                    are unchanged — you can restart checkout whenever you're ready.
                </p>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <x-ui.button href="{{ route('dashboard.billing.checkout') }}" variant="primary">Retry checkout</x-ui.button>
            <x-ui.button href="{{ route('dashboard.plans') }}" variant="outline">Compare plans</x-ui.button>
            <x-ui.button href="{{ route('dashboard.billing') }}" variant="ghost">Back to billing</x-ui.button>
        </div>
    </div>
</div>
@endsection
