@extends('tyro-dashboard::layouts.user')

@section('title', 'Checkout')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.billing') }}">Billing</a>
<span class="breadcrumb-separator">/</span>
<span>Checkout</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Checkout</h1>
            <p class="page-description">
                Paying in {{ $currency }} via {{ ucfirst($gateway->key()) }}.
                @foreach($supportedCurrencies as $cur)
                    @if($cur !== $currency)
                        <a href="{{ route('dashboard.billing.checkout', ['currency' => $cur]) }}" class="underline">Switch to {{ $cur }}</a>
                    @endif
                @endforeach
            </p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('dashboard.billing.checkout.start') }}">
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                @foreach($prices->groupBy('plan_id') as $planId => $planPrices)
                    @php $plan = $planPrices->first()->plan @endphp
                    @foreach($planPrices as $price)
                        <label class="card p-6 cursor-pointer border-2 hover:border-[var(--primary)] transition-colors {{ old('plan_price_id') == $price->id ? 'border-[var(--primary)]' : 'border-transparent' }}">
                            <input type="radio" name="plan_price_id" value="{{ $price->id }}" class="sr-only" checked>
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-lg">{{ $plan->name }}</div>
                                <x-ui.badge variant="{{ $price->interval === 'year' ? 'success' : 'secondary' }}">{{ $price->interval === 'year' ? 'Annual · 2 months free' : 'Monthly' }}</x-ui.badge>
                            </div>
                            <div class="text-[var(--muted-foreground)] text-sm mt-1">{{ $plan->description }}</div>
                            <div class="mt-3 font-bold text-xl">
                                {{ strtoupper($price->currency) }} {{ number_format($price->amount_minor / 100, 2) }}
                                <span class="text-sm font-normal text-[var(--muted-foreground)]">/ {{ $price->interval }}</span>
                            </div>
                        </label>
                    @endforeach
                @endforeach
            </div>

            @error('plan_price_id')
                <p class="text-[var(--destructive)] text-sm mb-4">{{ $message }}</p>
            @enderror

            <div class="card max-w-md">
                <div class="card-header"><h3 class="card-title">Coupon</h3></div>
                <div class="card-body">
                    <label class="block text-sm font-medium mb-1" for="coupon">Coupon code (optional)</label>
                    <input type="text" id="coupon" name="coupon" value="{{ old('coupon') }}"
                           class="form-input" placeholder="e.g. SAVE20">
                    <p class="text-xs text-[var(--muted-foreground)] mt-2">Discounts are validated before payment and applied by the gateway.</p>
                </div>
            </div>
        </div>

        {{-- Order summary --}}
        <div class="card md:sticky md:top-24 self-start">
            <div class="card-header"><h3 class="card-title">Summary</h3></div>
            <div class="card-body">
                @php($selected = $prices->first(fn ($p) => (string) $p->id === (string) old('plan_price_id')) ?? $prices->first())
                @if($selected)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--muted-foreground)]">Plan</span>
                            <span class="font-medium">{{ $selected->plan->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--muted-foreground)]">Billing</span>
                            <span class="font-medium">{{ $selected->interval === 'year' ? 'Annual' : 'Monthly' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--muted-foreground)]">Price</span>
                            <span class="font-medium">{{ strtoupper($selected->currency) }} {{ number_format($selected->amount_minor / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--muted-foreground)]">Gateway</span>
                            <span class="font-medium">{{ ucfirst($gateway->key()) }}</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-[var(--border)]">
                            <span class="font-semibold">Due today</span>
                            <span class="font-bold text-lg">{{ strtoupper($selected->currency) }} {{ number_format($selected->amount_minor / 100, 2) }}</span>
                        </div>
                        <p class="text-xs text-[var(--muted-foreground)]">14-day free trial — you won't be charged until the trial ends. Cancel anytime.</p>
                    </div>
                @endif
                <div class="mt-4">
                    <x-ui.button type="submit" full>Continue to payment →</x-ui.button>
                </div>
            </div>
        </div>
    </div>
</form>

@if(in_array('portal', $gateway->capabilities()))
    <div class="mt-4 text-sm text-[var(--muted-foreground)]">
        Already subscribed? <a href="{{ route('dashboard.billing.portal') }}" class="underline">Manage billing</a>
    </div>
@endif
@endsection
