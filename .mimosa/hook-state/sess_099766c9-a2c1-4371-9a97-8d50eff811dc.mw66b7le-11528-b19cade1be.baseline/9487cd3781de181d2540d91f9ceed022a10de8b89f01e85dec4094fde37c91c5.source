@extends('layouts.public')

@section('title', 'Changelog')
@section('meta_description', 'What changed in '.config('app.name').' — new features, fixes and improvements.')

@section('content')

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
