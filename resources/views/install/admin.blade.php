@extends('install.layout')

@section('title', 'Administrator Account')

@section('content')
<h1>Administrator account</h1>
<p class="install-lead">The first account becomes a <strong>super-admin</strong> with a personal workspace — exactly like a normal registration.</p>

<form method="POST" action="{{ route('install.admin') }}">
    @csrf

    <div class="install-field">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-input" required autofocus>
        @error('name')<span class="install-error">{{ $message }}</span>@enderror
    </div>

    <div class="install-field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-input" required>
        @error('email')<span class="install-error">{{ $message }}</span>@enderror
    </div>

    <div class="install-field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" class="form-input" required autocomplete="new-password">
        @error('password')<span class="install-error">{{ $message }}</span>@enderror
        <small class="install-hint">At least 8 characters.</small>
    </div>

    <div class="install-field">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary install-next">Create account →</button>
</form>
@endsection
