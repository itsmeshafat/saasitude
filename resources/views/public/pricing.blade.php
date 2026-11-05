@extends('layouts.public')

@section('title', 'Pricing — plans for every stage')
@section('meta_description', 'Transparent SaaS pricing in USD and BDT. Monthly or annual billing, 14-day free trial, cancel anytime.')

@section('content')
<style>
.pricing { padding: 72px 0; }
.pricing h1 { font-size: 36px; text-align: center; margin-bottom: 8px; }
.pricing > p { text-align: center; color: var(--muted-foreground); }
.controls { display: flex; justify-content: center; gap: 12px; margin: 24px 0 8px; flex-wrap: wrap; }
.pill { display: inline-flex; border: 1px solid var(--glass-border); border-radius: 999px; overflow: hidden;
        background: var(--glass-strong); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); }
.pill button { padding: 8px 18px; background: transparent; border: 0; cursor: pointer; font-size: 14px;
               color: var(--muted-foreground); transition: color .15s, background .15s; }
.pill button.on { background: var(--primary); color: var(--primary-foreground); }
.plans { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 40px; align-items: stretch; }
.plan { border-radius: 18px; padding: 28px; position: relative; display: flex; flex-direction: column; }
.plan.featured { background: var(--glass-strong); border: 2px solid oklch(0.546 0.237 258 / 0.65); box-shadow: 0 24px 48px -20px oklch(0.546 0.237 258 / 0.5); }
.plan .pop { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--primary); color: var(--primary-foreground); font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 999px; }
.plan h2 { font-size: 15px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted-foreground); }
.plan .price { font-size: 40px; font-weight: 800; margin: 8px 0; }
.plan .price small { font-size: 14px; font-weight: 400; color: var(--muted-foreground); }
.plan ul { list-style: none; margin: 16px 0 24px; font-size: 14px; color: var(--muted-foreground); }
.plan li { padding: 6px 0; }
.plan li::before { content: "✓ "; color: var(--success); font-weight: 700; }
.plan .btn { margin-top: auto; text-align: center; }
.missing { color: var(--muted-foreground); font-size: 15px; }
@media (max-width: 620px) { .plans { grid-template-columns: 1fr; } }
@media (max-width: 1020px) { .plans { gap: 12px; } .plan { padding: 18px; } .plan .price { font-size: 30px; } }
</style>

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