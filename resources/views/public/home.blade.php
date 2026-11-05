@extends('layouts.public')

@section('title', 'Launch your SaaS with billing that just works')
@section('meta_description', 'Workspaces, subscriptions, invoices and entitlements — powered by Stripe, Paddle and SSLCommerz. Production-ready Laravel SaaS boilerplate.')

@section('content')
<style>
.hero { text-align: center; padding: 96px 0 72px; }
.hero h1 { font-size: clamp(36px, 6vw, 56px); line-height: 1.1; letter-spacing: -0.02em; margin: 20px 0; }
.hero p { font-size: 19px; color: var(--muted-foreground); max-width: 640px; margin: 0 auto 32px; }
.hero .cta { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.mockup { margin: 56px auto 0; max-width: 880px; border-radius: 20px; padding: 18px; }
.mockup-bar { display: flex; gap: 6px; margin-bottom: 14px; }
.mockup-bar i { width: 10px; height: 10px; border-radius: 50%; background: var(--border); }
.mockup-body { background: var(--glass); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur);
               border: 1px solid var(--glass-border); border-radius: 14px; padding: 32px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.stat { border-radius: 12px; padding: 18px; background: var(--muted); border: 1px solid var(--glass-border); }
.stat b { font-size: 24px; display: block; }
.stat small { color: var(--muted-foreground); }
.features { padding: 72px 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.feature { border-radius: 18px; padding: 28px; }
.feature h3 { margin: 12px 0 8px; font-size: 17px; }
.feature p { font-size: 14px; color: var(--muted-foreground); line-height: 1.6; }
.feature .icon { width: 40px; height: 40px; border-radius: 10px; background: oklch(0.546 0.237 258 / 0.12); color: var(--primary); border: 1px solid oklch(0.546 0.237 258 / 0.2); display: flex; align-items: center; justify-content: center; font-size: 20px; }
.bottom-cta { padding: 0 0 72px; text-align: center; border-radius: 18px; padding: 48px; }
@media (max-width: 860px) { .features, .mockup-body { grid-template-columns: 1fr; } }
</style>

<section class="hero">
    <span class="eyebrow">Multi-gateway billing boilerplate</span>
    <h1>Launch your SaaS<br>with billing that just works</h1>
    <p>Workspaces, subscriptions, invoices and entitlements — powered by Stripe, Paddle and SSLCommerz. Production-ready from day one.</p>
    <div class="cta">
        <a class="btn btn-primary" href="{{ url('/register') }}">Start free trial</a>
        <a class="btn btn-outline" href="{{ route('public.pricing') }}">View pricing</a>
    </div>
    <div class="mockup glass">
        <div class="mockup-bar"><i></i><i></i><i></i></div>
        <div class="mockup-body">
            <div class="stat"><b>3</b><small>Payment gateways</small></div>
            <div class="stat"><b>1,204</b><small>Active workspaces</small></div>
            <div class="stat"><b>98.2%</b><small>Trial conversion</small></div>
        </div>
    </div>
</section>

<section class="features">
    <div class="feature glass">
        <div class="icon">◆</div>
        <h3>Multi-gateway billing</h3>
        <p>Stripe, Paddle and SSLCommerz adapters behind one interface — verified webhooks, proration and refunds included.</p>
    </div>
    <div class="feature glass">
        <div class="icon">◇</div>
        <h3>Workspaces &amp; teams</h3>
        <p>Every user gets a workspace. Invite teammates with owner, admin, member and billing roles.</p>
    </div>
    <div class="feature glass">
        <div class="icon">○</div>
        <h3>Entitlements engine</h3>
        <p>Plans are data, not code. Feature flags, seat limits and metered usage enforced by one service.</p>
    </div>
</section>

<section class="bottom-cta glass">
    <span class="eyebrow">Get started</span>
    <h2 style="font-size:32px;margin:12px 0 24px">Simple pricing, no surprises</h2>
    <p style="color:var(--muted-foreground);margin-bottom:28px">
        @foreach($plans as $plan)
            @php($price = $plan->prices->firstWhere('interval', 'month'))
            @if($price)
                <strong>{{ $plan->name }}</strong> from ${{ number_format($price->amount_minor / 100, 0) }}/mo{{ $loop->last ? '' : ' · ' }}
            @endif
        @endforeach
    </p>
    <a class="btn btn-primary" href="{{ route('public.pricing') }}">See full pricing</a>
</section>
@endsection
