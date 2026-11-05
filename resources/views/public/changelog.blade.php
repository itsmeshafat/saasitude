@extends('layouts.public')

@section('title', 'Changelog')
@section('meta_description', 'What changed in '.config('app.name').' — new features, fixes and improvements.')

@section('content')
<style>
.changelog { max-width: 760px; margin: 0 auto; padding: 72px 0; }
.changelog h1 { font-size: 32px; margin-bottom: 32px; }
.entry { border-radius: 18px; padding: 28px; margin-bottom: 20px; }
.entry h2 { font-size: 20px; margin-bottom: 12px; }
.entry ul { padding-left: 20px; }
.entry li { font-size: 14px; color: var(--muted-foreground); margin-bottom: 6px; }
</style>

<section class="changelog">
    <h1>Changelog</h1>
    @forelse($entries as $version => $html)
        <article class="entry glass">
            <h2>{{ ucfirst($version) }}</h2>
            {!! $html !!}
        </article>
    @empty
        <p style="color:var(--muted-foreground)">No changelog entries yet.</p>
    @endforelse
</section>
@endsection
