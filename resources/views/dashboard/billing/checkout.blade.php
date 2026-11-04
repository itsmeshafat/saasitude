@extends('tyro-dashboard::layouts.user')

@section('title', 'Upgrade Plan')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Upgrade</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Choose a Plan</h1>
            <p class="page-description">Select the plan that fits your workspace.</p>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('dashboard.billing.checkout.start') }}">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach($prices->groupBy('plan_id') as $planId => $planPrices)
            @php $plan = $planPrices->first()->plan @endphp
            @foreach($planPrices as $price)
            <label class="card cursor-pointer border-2 hover:border-blue-500 transition-colors {{ old('plan_price_id') == $price->id ? 'border-blue-500' : 'border-transparent' }}" style="padding: 1.5rem;">
                <input type="radio" name="plan_price_id" value="{{ $price->id }}" class="sr-only" {{ old('plan_price_id') == $price->id ? 'checked' : '' }}>
                <div class="font-semibold text-lg">{{ $plan->name }}</div>
                <div class="text-gray-500 text-sm mt-1">{{ $plan->description }}</div>
                <div class="mt-3 font-bold text-xl">
                    {{ strtoupper($price->currency) }} {{ number_format($price->amount_minor / 100, 2) }}
                    <span class="text-sm font-normal text-gray-500">/ {{ $price->interval }}</span>
                </div>
            </label>
            @endforeach
        @endforeach
    </div>

    @error('plan_price_id')
        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
    @enderror

    <div class="mb-4" style="max-width: 320px;">
        <label class="block text-sm font-medium mb-1" for="coupon">Coupon code (optional)</label>
        <input type="text" id="coupon" name="coupon" value="{{ old('coupon') }}"
               class="form-input w-full" placeholder="PROMO2024">
    </div>

    <button type="submit" class="btn btn-primary">Continue to payment →</button>
</form>

@if(in_array('portal', $gateway->capabilities()))
    <div class="mt-6 text-sm text-gray-500">
        Already subscribed? <a href="{{ route('dashboard.billing.portal') }}" class="underline">Manage billing</a>
    </div>
@endif
@endsection
