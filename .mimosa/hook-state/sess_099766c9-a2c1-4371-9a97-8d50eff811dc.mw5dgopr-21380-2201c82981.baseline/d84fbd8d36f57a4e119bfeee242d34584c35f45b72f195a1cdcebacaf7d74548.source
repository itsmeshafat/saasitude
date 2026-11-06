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
                <x-ui.button href="{{ route('dashboard.plans', ['currency' => $cur]) }}"
                             variant="{{ $currency === $cur ? 'secondary' : 'outline' }}"
                             size="sm">{{ $cur === 'USD' ? '$ USD' : '৳ BDT' }}</x-ui.button>
            @endforeach
        </div>
    </div>
</div>

<div class="mb-6 flex justify-center">
    <div class="inline-flex gap-1 p-1 rounded-[var(--radius)] border" style="border-color: var(--border); background: var(--muted);" id="interval-toggle">
        <button type="button" data-interval="month"
                class="btn btn-sm {{ request()->query('interval', 'month') !== 'year' ? 'btn-primary' : 'btn-ghost-btn' }}">Monthly</button>
        <button type="button" data-interval="year"
                class="btn btn-sm {{ request()->query('interval', 'month') === 'year' ? 'btn-primary' : 'btn-ghost-btn' }}">Annual</button>
    </div>
</div>


<div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="plan-cards">
    @foreach($plans as $plan)
        @php
            $price = $plan->prices->firstWhere('currency', $currency);
            $features = $plan->planFeatures->filter(fn ($pf) => $pf->feature);
        @endphp
        @if($price)
        <div data-plan-interval="{{ $price->interval }}" data-year-price="{{ number_format((($plan->prices->firstWhere('interval', 'year')->amount_minor) ?? $price->amount_minor * 10) / 100, 2, '.', '') }}">
            <x-ui.plan-card
                :plan="$plan"
                :price="number_format($price->amount_minor / 100, 2).' '.strtoupper($price->currency)"
                :featured="$plan->slug === 'pro'"
                :current="$plan->id === $currentPlanId"
                data-month-price="{{ number_format($price->amount_minor / 100, 2) }}">
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
                    <x-ui.button href="{{ route('dashboard.billing.checkout', ['currency' => $currency]) }}" variant="primary" class="w-full">Choose {{ $plan->name }}</x-ui.button>
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

<script>
    // Interval toggle: swaps the price line on each plan card (no framework needed).
    (function () {
        var toggle = document.getElementById('interval-toggle');
        var cards = document.getElementById('plan-cards');
        if (!toggle || !cards) return;

        toggle.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-interval]');
            if (!btn) return;

            toggle.querySelectorAll('[data-interval]').forEach(function (b) {
                var on = b === btn;
                b.classList.toggle('btn-primary', on);
                b.classList.toggle('btn-ghost-btn', !on);
            });

            cards.querySelectorAll('[data-plan-interval]').forEach(function (card) {
                var priceEl = card.querySelector('.plan-card-price');
                if (!priceEl) return;
                var holder = card.querySelector('[data-month-price]');
                var amount = btn.dataset.interval === 'year'
                    ? card.dataset.yearPrice
                    : (holder ? holder.dataset.monthPrice : card.dataset.monthPrice);
                // Replace only the leading amount in "12.00 USD" style text.
                priceEl.textContent = amount + ' ' + (priceEl.textContent.split(' ')[1] || 'USD');
            });
        });
    })();
</script>
@endsection
