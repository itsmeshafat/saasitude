<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
    // Theme before first paint: saved preference, else dark (design-system default).
    (function () {
        var saved = localStorage.getItem('theme');
        var dark = saved ? saved === 'dark' : true;
        document.documentElement.classList.toggle('dark', dark);
    })();
</script>
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
            <button type="button" class="theme-btn" id="theme-toggle" aria-label="Toggle theme">◐</button>
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
        <div class="pub-footer-credits">
            Built on
            <a href="https://laravel.com" target="_blank" rel="noopener">Laravel</a> ·
            <a href="https://tyrolabs.dev" target="_blank" rel="noopener">Tyro</a>
            by <a href="https://github.com/hasinhayder" target="_blank" rel="noopener">Hasin Hayder</a>
        </div>
    </footer>
</div>
<script>
    document.getElementById('theme-toggle').addEventListener('click', function () {
        var dark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    });
</script>
</body>
</html>
