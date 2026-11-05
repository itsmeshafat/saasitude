@extends('tyro-dashboard::layouts.admin')

@section('title', 'Gateway Settings')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Gateway Settings</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Gateway Settings</h1>
            <p class="page-description">Keys are stored encrypted and never displayed in full. Webhook URLs are per gateway.</p>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    @foreach($gateways as $key => $gateway)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ ucfirst($key) }}</h3>
            <x-ui.badge variant="{{ $gateway['mode'] === 'live' ? 'danger' : 'secondary' }}">{{ ucfirst($gateway['mode']) }}</x-ui.badge>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.gateway-settings.update') }}" x-data
                  @submit.prevent="if ($el.querySelector('[name=mode]').value === 'live') { showConfirm('Switch to LIVE mode?', 'Real money will be charged once this gateway goes live.', () => $el.submit()) } else { $el.submit() }">
                @csrf
                <input type="hidden" name="gateway" value="{{ $key }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Mode</label>
                    <select name="mode" class="form-input">
                        <option value="test" @selected($gateway['mode'] === 'test')>Test</option>
                        <option value="live" @selected($gateway['mode'] === 'live')>Live</option>
                    </select>
                </div>

                @foreach($gateway['secrets'] as $secretKey => $masked)
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" for="{{ $key }}_{{ $secretKey }}">
                            {{ Str::headline($secretKey) }}
                        </label>
                        <input type="password" id="{{ $key }}_{{ $secretKey }}" name="secrets[{{ $secretKey }}]"
                               class="form-input" placeholder="{{ $masked ?: 'Not set' }}" autocomplete="off">
                    </div>
                @endforeach

                <x-ui.button variant="primary" type="submit">Save {{ ucfirst($key) }}</x-ui.button>
            </form>

            <div class="mt-4 text-xs text-[var(--muted-foreground)]">
                Webhook URL: <code class="inline-block max-w-full" style="overflow-wrap:anywhere">{{ $gateway['webhook_url'] }}</code>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
