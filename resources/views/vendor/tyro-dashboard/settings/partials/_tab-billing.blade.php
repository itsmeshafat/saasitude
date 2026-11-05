{{-- Billing & gateways — OUR tab inside Tyro's System Settings hub --}}
@php
    $settingsService = app(\App\Services\SettingsService::class);
    $gatewayKeys = ['stripe', 'paddle', 'sslcommerz'];
    $gatewaySecretKeys = [
        'stripe' => ['secret_key' => 'Secret key', 'webhook_secret' => 'Webhook secret'],
        'paddle' => ['api_key' => 'API key', 'webhook_secret' => 'Webhook secret'],
        'sslcommerz' => ['store_password' => 'Store password'],
    ];
@endphp

<div class="vtabs-panel" id="vtab-billing">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Billing &amp; gateways</h3>
        </div>
        <div class="card-body">
            <p class="sys-settings-section-description" style="margin-bottom:1.25rem;">
                Payment gateway credentials and modes. Secrets are stored encrypted and never displayed in full —
                leave a field blank to keep its current value. Requires the <code>settings.manage</code> privilege.
            </p>

            @foreach($gatewayKeys as $gwKey)
                @php
                    $gwMode = $settingsService->get("gateway.{$gwKey}.mode", 'test');
                @endphp
                <div class="sys-settings-surface mb-6" data-gateway-panel="{{ $gwKey }}">
                    <h4 class="sys-settings-surface-title">
                        {{ ucfirst($gwKey) }}
                        <span class="badge {{ $gwMode === 'live' ? 'badge-danger' : 'badge-secondary' }}" style="margin-left:.5rem;">{{ ucfirst($gwMode) }}</span>
                    </h4>
                    <p class="sys-settings-surface-description">Webhook URL: <code style="word-break:break-all;">{{ url("webhooks/{$gwKey}") }}</code></p>

                    <div class="form-group">
                        <label class="form-label" for="{{ $gwKey }}_mode">Mode</label>
                        <select name="mode" id="{{ $gwKey }}_mode" class="form-select" data-gateway-field disabled>
                            <option value="test" @selected($gwMode === 'test')>Test</option>
                            <option value="live" @selected($gwMode === 'live')>Live</option>
                        </select>
                        <p class="form-hint">Switching to live requires confirmation — real money will be charged.</p>
                    </div>

                    @foreach($gatewaySecretKeys[$gwKey] as $secretKey => $secretLabel)
                        @php($masked = $settingsService->get("gateway.{$gwKey}.{$secretKey}"))
                        <div class="form-group">
                            <label class="form-label" for="{{ $gwKey }}_{{ $secretKey }}">{{ $secretLabel }} <span class="env-key">{{ $gwKey }}.{{ $secretKey }}</span></label>
                            <input type="password" name="secrets[{{ $secretKey }}]" id="{{ $gwKey }}_{{ $secretKey }}"
                                   class="form-input" autocomplete="off" data-gateway-field disabled
                                   placeholder="{{ $masked ? str_repeat('•', 8).substr($masked, -4) : 'Not set' }}">
                            <p class="form-hint">Leave blank to keep the current value.</p>
                        </div>
                    @endforeach

                    <button type="button" class="btn btn-primary btn-sm"
                            data-gateway-save="{{ $gwKey }}"
                            @unless(auth()->user()?->hasPrivilege('settings.manage')) disabled @endunless>
                        Save {{ ucfirst($gwKey) }}
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    (function () {
        // Fields start disabled so they NEVER submit with the hub's System Settings form.
        // The per-gateway Save button enables → collects via fetch to our endpoint → re-disables.
        document.querySelectorAll('[data-gateway-save]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var gw = btn.getAttribute('data-gateway-save');
                var panel = document.querySelector('[data-gateway-panel="' + gw + '"]');
                var fields = panel.querySelectorAll('[data-gateway-field]');
                var token = document.querySelector('#systemSettingsForm input[name="_token"]')?.value
                    || document.querySelector('meta[name="csrf-token"]')?.content;

                var data = new FormData();
                data.append('gateway', gw);
                data.append('mode', panel.querySelector('[name="mode"]').value);
                fields.forEach(function (f) {
                    if (f.name && f.name.startsWith('secrets[') && f.value !== '') {
                        data.append(f.name, f.value);
                    }
                });

                btn.disabled = true;
                btn.textContent = 'Saving…';

                fetch('{{ route('admin.gateway-settings.update') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token || '', 'X-Requested-With': 'XMLHttpRequest' },
                    body: data,
                }).then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    // Re-fetch the page section: simplest reliable feedback is a reload with flash.
                    window.location.reload();
                }).catch(function () {
                    btn.disabled = false;
                    btn.textContent = 'Save ' + gw.charAt(0).toUpperCase() + gw.slice(1) + ' (failed — retry)';
                });
            });
        });
    })();
</script>
