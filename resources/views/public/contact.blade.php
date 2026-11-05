@extends('layouts.public')

@section('title', 'Contact us')
@section('meta_description', 'Questions about billing, plans or the API? Send us a message — we reply fast.')

@section('content')

<section class="contact">
    <div>
        <h1>Contact us</h1>
        <p style="color:var(--muted-foreground)">
            Questions about billing, plans or the API? Every message becomes a tracked
            support ticket — we usually reply within one business day.
        </p>
    </div>

    <div>
        @if(session('success'))
            <div class="alert alert-ok">{{ session('success') }}</div>
        @endif

        <form class="glass" method="POST" action="{{ route('public.contact.submit') }}">
            @csrf
            <div class="field">
                <label for="email">Your email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required>
                @error('subject')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    @foreach(['general' => 'General', 'billing' => 'Billing', 'technical' => 'Technical', 'feature' => 'Feature request'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="body">Message</label>
                <textarea id="body" name="body" rows="5" required>{{ old('body') }}</textarea>
                @error('body')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div><button class="btn btn-primary" type="submit">Send message</button></div>
        </form>
    </div>
</section>
@endsection
