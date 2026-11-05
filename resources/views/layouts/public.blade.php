<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@guest
    <title>@yield('title', config('app.name')) — SaaS billing boilerplate</title>
    <meta name="description" content="@yield('meta_description', 'Launch your SaaS with multi-gateway billing, workspaces, entitlements and a mobile API. Powered by Stripe, Paddle and SSLCommerz.')">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', 'Multi-gateway SaaS billing boilerplate.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ url('/images/og.png') }}">
    <link rel="canonical" href="{{ url()->current() }}">
@endguest
@if(!file_exists(public_path('build/manifest.json')) && !file_exists(public_path('hot')))
    {{-- Public site styles are inlined per-page when Vite assets are absent --}}
@endif
<style>
    :root { color-scheme: light dark; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: var(--background);
        background-image: var(--mesh);
        background-attachment: fixed;
        color: var(--foreground);
        -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.6;
    }
    .glass { background: var(--glass); backdrop-filter: var(--glass-blur);
             -webkit-backdrop-filter: var(--glass-blur); border: 1px solid var(--glass-border);
             box-shadow: 0 8px 32px -12px oklch(0.4 0.1 255 / 0.25), var(--glass-highlight); }
    .wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
    .btn { display: inline-block; padding: 12px 24px; border-radius: var(--radius);
           font-weight: 600; text-decoration: none; cursor: pointer; border: 0; font-size: 15px; }
    .btn-primary { background: var(--primary); color: var(--primary-foreground); }
    .btn-outline { border: 1px solid var(--border); color: var(--foreground); }
    .eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--primary); }
    .pub-nav { position: sticky; top: 16px; z-index: 50; margin-top: 20px; border-radius: 999px;
               display: flex; align-items: center; justify-content: space-between; padding: 12px 24px;
               background: var(--glass-strong); }
    .pub-logo { font-size: 19px; font-weight: 800; text-decoration: none; color: var(--foreground); }
    .pub-logo span { color: var(--primary); }
    .pub-links { display: flex; gap: 22px; font-size: 15px; }
    .pub-links a { text-decoration: none; color: var(--muted-foreground); }
    .pub-links a:hover { color: var(--foreground); }
    .pub-footer { margin: 96px 0 40px; padding: 32px; border-radius: var(--radius);
                  display: flex; flex-wrap: wrap; gap: 24px; justify-content: space-between; align-items: center;
                  font-size: 14px; color: var(--muted-foreground); }
    .pub-footer a { color: var(--muted-foreground); text-decoration: none; margin-left: 18px; }
    .pub-footer a:hover { color: var(--foreground); }
    @media (max-width: 720px) { .pub-links { display: none; } }
    @media (prefers-color-scheme: dark) {
        html:not(.light) body { --background: oklch(0.13 0.02 254); }
    }
</style>
@vite('resources/css/app.css')
</head>
<body>
<div class="wrap">
    <nav class="pub-nav">
        <a class="pub-logo" href="{{ route('public.home') }}">{{ config('app.name') }}<span>.</span></a>
        <div class="pub-links">
            <a href="{{ route('public.pricing') }}">Pricing</a>
            <a href="{{ route('public.changelog') }}">Changelog</a>
            <a href="{{ route('public.contact') }}">Contact</a>
        </div>
        @guest
            <a class="btn btn-primary" style="padding:9px 18px;font-size:14px" href="{{ url('/register') }}">Sign up free</a>
        @else
            <a class="btn btn-primary" style="padding:9px 18px;font-size:14px" href="{{ url('/dashboard') }}">Dashboard</a>
        @endguest
    </nav>

    @yield('content')

    <footer class="pub-footer glass">
        <div>© {{ now()->year }} {{ config('app.name') }}. All rights reserved.</div>
        <div>
            <a href="{{ route('public.legal', 'terms') }}">Terms</a>
            <a href="{{ route('public.legal', 'privacy') }}">Privacy</a>
            <a href="{{ route('public.legal', 'refund-policy') }}">Refunds</a>
        </div>
    </footer>
</div>
</body>
</html>
