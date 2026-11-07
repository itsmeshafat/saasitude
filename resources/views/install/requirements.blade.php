@extends('install.layout')

@section('title', 'Server Requirements')

@section('content')
<h1>Server requirements</h1>
<p class="install-lead">Checking your server before we configure anything. Items marked <strong>required</strong> must pass.</p>

<ul class="install-checks">
    @foreach($checks as $check)
        <li class="{{ $check['ok'] ? 'ok' : ($check['blocking'] ? 'fail' : 'warn') }}">
            <span class="install-check-icon">{{ $check['ok'] ? '✓' : '✕' }}</span>
            <span>{{ $check['label'] }}</span>
            @unless($check['ok'])
                <span class="install-check-tag">{{ $check['blocking'] ? 'required' : 'recommended' }}</span>
            @endunless
        </li>
    @endforeach
</ul>

@if($passes)
    <form method="GET" action="{{ route('install.database.form') }}">
        <button type="submit" class="btn btn-primary install-next">Continue → Database</button>
    </form>
@else
    <div class="install-error">Fix the failed required items (or adjust permissions) and reload this page to re-check.</div>
    <form method="GET" action="{{ route('install.requirements') }}">
        <button type="submit" class="btn btn-outline install-next">Re-check</button>
    </form>
@endif
@endsection
