@extends('layouts.public')

@section('title', 'Pricing — plans for every stage')
@section('meta_description', 'Transparent SaaS pricing in USD and BDT. Monthly or annual billing, 14-day free trial, cancel anytime.')

@section('content')

{{-- Prices are rendered server-side per interval; the toggles swap visibility (no JS framework needed). --}}
<section class="pricing">
    <h1>Simple, transparent pricing</h1>
    <p>USD via Stripe/Paddle · ৳BDT via SSLCommerz · 14-day free trial</p>

    <div class="controls">
        <div class="pill" role="group" aria-label="Billing interval">
            <button type="button" class="on" data-toggle="interval" data-value="month">Monthly</button>
            <button type="button" data-toggle="interval" data-value="year">Annual −17%</button>
        </div>
        <div class="pill" role="group" aria-label="Currency">
            <button type="button" class="on" data-toggle="currency" data-value="USD">$ USD</button>
            <button type="button" data-toggle="currency" data-value="BDT">৳ BDT</button>
        </div>
    </div>

    <div class="plans" id="plan-grid" data-interval="month" data-currency="USD">
        @foreach($plans as $plan)
            <div class="plan glass {{ $plan->slug === 'starter' ? 'featured' : '' }}">
                @if($plan->slug === 'starter')<span class="pop">Most popular</span>@endif
                <h2>{{ $plan->name }}</h2>
                @foreach(['month', 'year'] as $interval)
                    @foreach(['USD', 'BDT'] as $currency)
                        @php($price = $plan->prices->first(fn ($p) => $p->currency === $currency && $p->interval === $interval))
                        @if($price)
                            <div class="price" data-price
                                 data-interval="{{ $interval }}" data-currency="{{ $currency }}"
                                 {{ $interval !== 'month' || $currency !== 'USD' ? 'hidden' : '' }}>
                                {{ $currency === 'USD' ? '$' : '৳' }}{{ number_format($price->amount_minor / 100, 0) }}<small>/{{ $interval === 'year' ? 'yr' : 'mo' }}</small>
                            </div>
                        @endif
                    @endforeach
                @endforeach
                <div class="missing" data-missing hidden>Not available in this billing option</div>
                <p style="font-size:14px;color:var(--muted-foreground);margin-top:8px">{{ $plan->description }}</p>
                <ul>
                    @foreach($plan->planFeatures->filter(fn($pf) => $pf->feature) as $pf)
                        <li>{{ $pf->feature->name }}@if($pf->feature->type !== 'boolean') —
                            @if($pf->value === '-1') unlimited @else {{ $pf->value }} @endif
                        @endif</li>
                    @endforeach
                </ul>
                <a class="btn {{ $plan->slug === 'starter' ? 'btn-primary' : 'btn-outline' }}" href="{{ url('/register') }}">
                    {{ $plan->slug === 'free' ? 'Get started' : 'Start free trial' }}
                </a>
            </div>
        @endforeach
    </div>
</section>

<script>
(function () {
    var grid = document.getElementById('plan-grid');
    if (!grid) return;

    function sync() {
        var interval = grid.dataset.interval;
        var currency = grid.dataset.currency;

        document.querySelectorAll('[data-price]').forEach(function (el) {
            var match = el.dataset.interval === interval && el.dataset.currency === currency;
            el.hidden = !match;
        });

        document.querySelectorAll('[data-missing]').forEach(function (el) {
            var anyVisible = document.querySelector(
                '[data-price][data-interval="' + interval + '"][data-currency="' + currency + '"]'
            );
            el.hidden = !!anyVisible;
        });
    }

    document.querySelectorAll('[data-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = btn.dataset.toggle;
            document.querySelectorAll('[data-toggle="' + group + '"]').forEach(function (b) {
                b.classList.toggle('on', b === btn);
            });
            grid.dataset[group] = btn.dataset.value;
            sync();
        });
    });

    sync();
})();
</script>
@endsection