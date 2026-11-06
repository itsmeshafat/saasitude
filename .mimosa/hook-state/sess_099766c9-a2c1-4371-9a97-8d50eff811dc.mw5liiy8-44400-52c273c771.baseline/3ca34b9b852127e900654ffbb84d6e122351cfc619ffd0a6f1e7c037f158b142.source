@extends('layouts.public')

@section('title', 'Launch your SaaS with billing that just works')
@section('meta_description', 'Workspaces, subscriptions, invoices and entitlements — powered by Stripe, Paddle and SSLCommerz. Production-ready Laravel SaaS boilerplate.')

@section('content')

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
