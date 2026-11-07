<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Installer') — {{ config('app.name') }}</title>
<script>
    // Theme before first paint: saved preference, else dark (matches public site).
    (function () {
        var saved = localStorage.getItem('theme');
        document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : true);
    })();
</script>
@vite('resources/css/app.css')
</head>
<body>
@php
    $steps = ['requirements' => 'Requirements', 'database' => 'Database', 'admin' => 'Admin', 'gateway' => 'Gateways', 'demo' => 'Finish'];
    $current = explode('.', Route::currentRouteName() ?? '')[1] ?? 'requirements';
    $current = str($current)->replaceEnd('-form', '')->toString();
    $currentIndex = array_search($current, array_keys($steps), true) ?: 0;
@endphp
<div class="wrap install-wrap">
    <header class="install-header">
        <div class="install-brand">{{ config('app.name') }} <span>Installer</span></div>
        <button type="button" class="theme-btn" id="theme-toggle" aria-label="Toggle theme">◐</button>
    </header>

    <div class="install-steps" aria-label="Installation progress">
        @foreach($steps as $key => $label)
            <div class="install-step {{ $key === $current ? 'is-current' : ($loop->index < $currentIndex ? 'is-done' : '') }}">
                <span class="install-step-dot">{{ $loop->index < $currentIndex ? '✓' : $loop->index + 1 }}</span>
                <span class="install-step-label">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    @if(session('install.message'))
        <div class="install-flash" role="status">{{ session('install.message') }}</div>
    @endif

    <main class="install-card glass">
        @yield('content')
    </main>

    <footer class="install-footer">
        Built on <a href="https://laravel.com" target="_blank" rel="noopener">Laravel</a> ·
        <a href="https://tyrolabs.dev" target="_blank" rel="noopener">Tyro</a> by
        <a href="https://github.com/hasinhayder" target="_blank" rel="noopener">Hasin Hayder</a>
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
