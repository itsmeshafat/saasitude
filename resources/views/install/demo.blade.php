@extends('install.layout')

@section('title', 'Finish')

@section('content')
<h1>One last thing — demo data</h1>
<p class="install-lead">
    Seed a rich demo dataset so every screen (billing, subscriptions, invoices, coupons, support) has something to show.
    Plans and settings are always seeded either way.
</p>

<div class="install-demo-box">
    <p><strong>If you say yes</strong>, you get:</p>
    <ul>
        <li><code>demo@demo.com</code> / <code>password</code> — owner of <strong>Acme Inc</strong> with an active Pro subscription, 3 payments, 2 invoices</li>
        <li>Workspaces in every subscription state: trialing (Paddle), past-due (SSLCommerz), canceled</li>
        <li>2 coupons (one expired), 2 support tickets with replies, an extra team member</li>
    </ul>
    <p class="install-warn">⚠️ Demo data is for evaluation only. Remove it with <code>php artisan saas:reset-demo</code> before going live.</p>
</div>

<form method="POST" action="{{ route('install.demo') }}">
    @csrf
    <div class="install-actions">
        <button type="submit" name="demo" value="0" class="btn btn-outline">Install clean (no demo)</button>
        <button type="submit" name="demo" value="1" class="btn btn-primary">Install with demo data →</button>
    </div>
</form>
@endsection
