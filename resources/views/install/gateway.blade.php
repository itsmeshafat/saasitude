@extends('install.layout')

@section('title', 'Payment Gateways')

@section('content')
<h1>Payment gateways <span class="install-optional">optional</span></h1>
<p class="install-lead">Add gateway keys now or later under <strong>Admin → Gateway Settings</strong>. Secrets are stored encrypted in the database, never in <code>.env</code>.</p>

<form method="POST" action="{{ route('install.gateway') }}">
    @csrf

    <details class="install-gateway" open>
        <summary>Stripe</summary>
        <div class="install-field">
            <label for="stripe_mode">Mode</label>
            <select name="stripe_mode" id="stripe_mode" class="form-select">
                <option value="test" selected>Test</option>
                <option value="live">Live</option>
            </select>
        </div>
        <div class="install-field">
            <label for="stripe_secret">Secret key</label>
            <input id="stripe_secret" name="stripe_secret" type="password" placeholder="sk_test_…" class="form-input" autocomplete="off">
        </div>
        <div class="install-field">
            <label for="stripe_webhook_secret">Webhook signing secret</label>
            <input id="stripe_webhook_secret" name="stripe_webhook_secret" type="password" placeholder="whsec_…" class="form-input" autocomplete="off">
        </div>
    </details>

    <details class="install-gateway">
        <summary>Paddle</summary>
        <div class="install-field">
            <label for="paddle_mode">Mode</label>
            <select name="paddle_mode" id="paddle_mode" class="form-select">
                <option value="test" selected>Sandbox</option>
                <option value="live">Live</option>
            </select>
        </div>
        <div class="install-field">
            <label for="paddle_api_key">API key</label>
            <input id="paddle_api_key" name="paddle_api_key" type="password" class="form-input" autocomplete="off">
        </div>
        <div class="install-field">
            <label for="paddle_webhook_secret">Webhook secret</label>
            <input id="paddle_webhook_secret" name="paddle_webhook_secret" type="password" class="form-input" autocomplete="off">
        </div>
    </details>

    <details class="install-gateway">
        <summary>SSLCommerz</summary>
        <div class="install-field">
            <label for="sslcommerz_mode">Mode</label>
            <select name="sslcommerz_mode" id="sslcommerz_mode" class="form-select">
                <option value="test" selected>Sandbox</option>
                <option value="live">Live</option>
            </select>
        </div>
        <div class="install-field">
            <label for="sslcommerz_store_id">Store ID</label>
            <input id="sslcommerz_store_id" name="sslcommerz_store_id" type="text" class="form-input" autocomplete="off">
        </div>
        <div class="install-field">
            <label for="sslcommerz_store_password">Store password</label>
            <input id="sslcommerz_store_password" name="sslcommerz_store_password" type="password" class="form-input" autocomplete="off">
        </div>
    </details>

    <div class="install-actions">
        <button type="submit" name="action" value="skip" class="btn btn-outline">Skip for now</button>
        <button type="submit" name="action" value="save" class="btn btn-primary">Save &amp; continue →</button>
    </div>
</form>
@endsection
