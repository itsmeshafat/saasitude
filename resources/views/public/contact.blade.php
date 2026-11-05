@extends('layouts.public')

@section('title', 'Contact us')
@section('meta_description', 'Questions about billing, plans or the API? Send us a message — we reply fast.')

@section('content')
<style>
.contact { display: grid; grid-template-columns: 1fr 1.2fr; gap: 32px; padding: 72px 0; align-items: start; }
.contact h1 { font-size: 36px; margin-bottom: 12px; }
.contact form { border-radius: 18px; padding: 32px; display: flex; flex-direction: column; gap: 16px; }
.field label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
.field input, .field select, .field textarea {
    width: 100%; padding: 11px 14px; font: inherit; border-radius: var(--radius);
    border: 1px solid var(--input); background: var(--glass); color: var(--foreground);
}
.field .err { color: var(--destructive); font-size: 13px; margin-top: 4px; }
.alert { border-radius: var(--radius); padding: 14px 18px; margin-bottom: 20px; font-size: 14px; }
.alert-ok { background: oklch(0.627 0.194 149 / 0.15); border: 1px solid oklch(0.627 0.194 149 / 0.4); }
@media (max-width: 860px) { .contact { grid-template-columns: 1fr; } }
</style>

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
