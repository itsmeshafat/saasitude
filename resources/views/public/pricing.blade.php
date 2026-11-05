@extends('layouts.public')

@section('title', 'Pricing — plans for every stage')
@section('meta_description', 'Transparent SaaS pricing in USD and BDT. Monthly or annual billing, 14-day free trial, cancel anytime.')

@section('content')
<style>
.pricing { padding: 72px 0; }
.pricing h1 { font-size: 36px; text-align: center; margin-bottom: 8px; }
.pricing > p { text-align: center; color: var(--muted-foreground); }
.controls { display: flex; justify-content: center; gap: 12px; margin: 24px 0 8px; flex-wrap: wrap; }
.pill { display: inline-flex; border: 1px solid var(--glass-border); border-radius: 999px; overflow: hidden; background: oklch(1 0 0 / 0.5); backdrop-filter: blur(12px); }
.pill button { padding: 8px 18px; background: transparent; border: 0; cursor: pointer; font-size: 14px; color: var(--muted-foreground); }
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
@media (max-width: 620px) { .plans { grid-template-columns: 1fr; } }
@media (max-width: 1020px) { .plans { gap: 12px; } .plan { padding: 18px; } .plan .price { font-size: 30px; } }
</style>

<section class="pricing" x-data="{ interval: 'month', currency: 'USD' }">
    <h1>Simple, transparent pricing</h1>
    <p>USD via Stripe/Paddle · ৳BDT via SSLCommerz · 14-day free trial</p>

    <div class="controls">
        <div class="pill">
            <button type="button" :class="interval === 'month' && 'on'" @click="interval = 'month'">Monthly</button>
            <button type="button" :class="interval === 'year' && 'on'" @click="interval = 'year'">Annual −17%</button>
        </div>
        <div class="pill">
            <button type="button" :class="currency === 'USD' && 'on'" @click="currency = 'USD'">$ USD</button>
            <button type="button" :class="currency === 'BDT' && 'on'" @click="currency = 'BDT'">৳ BDT</button>
        </div>
    </div>

    <div class="plans">
        @foreach($plans as $plan)
            <div class="plan glass {{ $plan->slug === 'starter' ? 'featured' : '' }}">
                @if($plan->slug === 'starter')<span class="pop">Most popular</span>@endif
                <h2>{{ $plan->name }}</h2>
                <template x-if="true">
                    <div class="price" x-text="
                        @foreach($plan->prices as $price)
                            @if($price->currency === 'USD')
                                (currency === 'USD' && interval === '{{ $price->interval }}') ? '${{ number_format($price->amount_minor / 100, 0) }}' :
                            @else
                                (currency === 'BDT' && interval === '{{ $price->interval }}') ? '৳{{ number_format($price->amount_minor / 100, 0) }}' :
                            @endif
                        @endforeach
                        (currency === 'USD' ? '—' : '৳ —')
                    "></div>
                </template>
                <small style="color:var(--muted-foreground)" x-text="interval === 'year' ? '/year' : '/month'"></small>
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
@endsection
