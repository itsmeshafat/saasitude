@extends('install.layout')

@section('title', 'Database')

@section('content')
<h1>Database</h1>
<p class="install-lead">Credentials are written to <code>.env</code> (never logged) and migrations run immediately after the connection is verified.</p>

<form method="POST" action="{{ route('install.database') }}">
    @csrf

    @if($errors->has('database'))
        <div class="install-error">{{ $errors->first('database') }}</div>
    @endif

    <div class="install-field">
        <label for="connection">Connection</label>
        <select name="connection" id="connection" class="form-select" data-toggle="mysql-fields">
            <option value="mysql" selected>MySQL</option>
            <option value="sqlite">SQLite (file path)</option>
        </select>
    </div>

    <div data-mysql-only>
        <div class="install-field">
            <label for="host">Host</label>
            <input id="host" name="host" type="text" value="{{ old('host', '127.0.0.1') }}" class="form-input">
            @error('host')<span class="install-error">{{ $message }}</span>@enderror
        </div>
        <div class="install-field">
            <label for="port">Port</label>
            <input id="port" name="port" type="number" value="{{ old('port', '3306') }}" class="form-input">
        </div>
        <div class="install-field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" class="form-input">
        </div>
        <div class="install-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" value="{{ old('password') }}" class="form-input" autocomplete="new-password">
        </div>
    </div>

    <div class="install-field" data-sqlite-only hidden>
        <label for="database-sqlite">SQLite file path</label>
        <input id="database-sqlite" name="database" type="text" placeholder="/absolute/path/database.sqlite" value="{{ old('database') }}" class="form-input">
        <small class="install-hint">Absolute path. The file must already exist (e.g. <code>touch database.sqlite</code>).</small>
    </div>

    <div data-mysql-only>
        <div class="install-field">
            <label for="database-mysql">Database name</label>
            <input id="database-mysql" name="database" type="text" value="{{ old('database') }}" class="form-input">
            @error('database')<span class="install-error">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="install-field">
        <label for="app_url">App URL <span class="install-hint-inline">(optional)</span></label>
        <input id="app_url" name="app_url" type="url" value="{{ old('app_url', url('/')) }}" class="form-input">
    </div>

    <button type="submit" class="btn btn-primary install-next">Test connection &amp; migrate →</button>
</form>

<script>
    var select = document.getElementById('connection');
    // Toggle visibility AND disable/require inputs per connection so hidden
    // fields never submit, block native validation, or collide by name.
    var toggle = function () {
        var mysql = select.value === 'mysql';
        document.querySelectorAll('[data-mysql-only]').forEach(function (group) {
            group.hidden = !mysql;
            group.querySelectorAll('input').forEach(function (input) {
                input.disabled = !mysql;
                input.required = mysql;
            });
        });
        document.querySelectorAll('[data-sqlite-only]').forEach(function (group) {
            group.hidden = mysql;
            group.querySelectorAll('input').forEach(function (input) {
                input.disabled = mysql;
                input.required = !mysql;
            });
        });
    };
    select.addEventListener('change', toggle);
    toggle();
</script>
@endsection
