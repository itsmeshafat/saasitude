@extends('tyro-dashboard::layouts.user')

@section('title', 'Plans')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.billing') }}">Billing</a>
<span class="breadcrumb-separator">/</span>
<span>Plans</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Choose a Plan</h1>
            <p class="page-description">Pick the plan that fits {{ $workspace->name }}.</p>
        </div>
        <div class="flex items-center gap-2">
            @foreach(['USD', 'BDT'] as $cur)
                <a href="{{ route('dashboard.plans', ['currency' => $cur]) }}"
                   class="px-3 py-1.5 rounded-md text-sm font-medium border {{ $currency === $cur ? 'border-[var(--primary)] text-[var(--primary)]' : 'border-[var(--border)] text-[var(--muted-foreground)]' }}">
                    {{ $cur === 'USD' ? '$ USD' : '৳ BDT' }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<div x-data="{ interval: 'month' }" class="mb-6 flex justify-center">
    <div class="inline-flex rounded-lg border border-[var(--border)] p-1">
        <button type="button" @click="interval = 'month'"
                :class="interval === 'month' ? 'bg-[var(--primary)] text-[var(--primary-foreground)]' : 'text-[var(--muted-foreground)]'"
                class="px-4 py-1.5 rounded-md text-sm font-medium">Monthly</button>
        <button type="button" @click="interval = 'year'"
                :class="interval === 'year' ? 'bg-[var(--primary)] text-[var(--primary-foreground)]' : 'text-[var(--muted-foreground)]'"
                class="px-4 py-1.5 rounded-md text-sm font-medium">Annual</button>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
        @php
            $price = $plan->prices->firstWhere('currency', $currency);
            $features = $plan->planFeatures->filter(fn ($pf) => $pf->feature);
        @endphp
        @if($price)
        <div x-show="interval === '{{ $price->interval }}' || '{{ $price->interval }}' === 'month'">
            <x-ui.plan-card
                :plan="$plan"
                :price="number_format($price->amount_minor / 100, 2).' '.strtoupper($price->currency)"
                :featured="$plan->slug === 'pro'"
                :current="$plan->id === $currentPlanId">
                <ul class="space-y-2">
                    @foreach($features as $pf)
                        <li class="flex items-start gap-2">
                            <span class="text-[var(--primary)]">✓</span>
                            <span>{{ $pf->feature->name }}@if($pf->feature->type !== 'boolean') — {{ $pf->value ?? 'unlimited' }}@endif</span>
                        </li>
                    @endforeach
                </ul>
                @if($plan->id !== $currentPlanId)
                <div class="mt-4">
                    <a href="{{ route('dashboard.billing.checkout', ['currency' => $currency]) }}">
                        <x-ui.button variant="primary" class="w-full">Choose {{ $plan->name }}</x-ui.button>
                    </a>
                </div>
                @endif
            </x-ui.plan-card>
        </div>
        @endif
    @endforeach
</div>

@if($plans->isEmpty())
<div class="card">
    <div class="card-body">
        <x-ui.empty-state title="No plans available" description="No public plans are configured yet. Check back soon." />
    </div>
</div>
@endif
@endsection
