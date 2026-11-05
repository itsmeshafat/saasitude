@extends('layouts.public')

@section('title', $heading)
@section('meta_description', $heading.' for '.config('app.name').'.')

@section('content')
{{-- LAWYER REVIEW REQUIRED: all legal copy below is placeholder boilerplate and must be reviewed before launch. --}}

<section class="legal">
    <h1>{{ $heading }}</h1>
    <span class="flag">⚠ Placeholder content — lawyer review required before launch</span>

    <div class="legal-body glass">
        @if($page === 'terms')
            <h2>1. Acceptance</h2>
            <p>By accessing {{ config('app.name') }} you agree to these terms. [LAWYER REVIEW REQUIRED]</p>
            <h2>2. Subscriptions and billing</h2>
            <p>Plans are billed monthly or annually through our payment providers (Stripe, Paddle, SSLCommerz). Prices exclude applicable taxes. [LAWYER REVIEW REQUIRED]</p>
            <h2>3. Acceptable use</h2>
            <ul><li>No unlawful or abusive use of the service.</li><li>You are responsible for your workspace members' actions.</li></ul>
        @elseif($page === 'privacy')
            <h2>Data we collect</h2>
            <p>Account data (name, email), workspace data, billing records and usage metrics. [LAWYER REVIEW REQUIRED — GDPR/PBDT compliance]</p>
            <h2>Your rights</h2>
            <p>You can export or delete your data from your dashboard at any time. [LAWYER REVIEW REQUIRED]</p>
        @else
            <h2>Refund window</h2>
            <p>[LAWYER REVIEW REQUIRED — define refund window, gateway-specific rules, and BDT/USD differences]</p>
            <h2>How to request</h2>
            <p>Contact support from your dashboard; approved refunds return to the original payment method within 5–10 business days.</p>
        @endif
    </div>
</section>
@endsection
