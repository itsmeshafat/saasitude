<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GatewaySettingsController extends Controller
{
    /** Keys stored (encrypted-at-rest by the settings table casts) per gateway. */
    private const SECRET_KEYS = [
        'stripe' => ['secret_key', 'webhook_secret'],
        'paddle' => ['api_key', 'webhook_secret'],
        'sslcommerz' => ['store_password'],
    ];

    public function show(): View
    {
        $settings = app(SettingsService::class);

        $gateways = [];
        foreach (['stripe', 'paddle', 'sslcommerz'] as $key) {
            $gateways[$key] = [
                'mode' => $settings->get("gateway.{$key}.mode", 'test'),
                'secrets' => collect(self::SECRET_KEYS[$key])
                    ->mapWithKeys(fn ($k) => [$k => $this->mask($settings->get("gateway.{$key}.{$k}"))])
                    ->all(),
                'webhook_url' => url("webhooks/{$key}"),
            ];
        }

        return view('dashboard.gateway-settings', ['gateways' => $gateways]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('settings.manage'), 403);
        abort_if(session('impersonator_id'), 403, 'Settings changes are blocked while impersonating.');

        $validated = $request->validate([
            'gateway' => 'required|in:stripe,paddle,sslcommerz',
            'mode' => 'required|in:test,live',
            'secrets' => 'nullable|array',
            'secrets.*' => 'nullable|string|max:500',
        ]);

        $settings = app(SettingsService::class);
        $gateway = $validated['gateway'];

        $dirty = [];
        foreach (self::SECRET_KEYS[$gateway] as $secretKey) {
            $value = $validated['secrets'][$secretKey] ?? null;
            // Blank inputs keep the existing secret — secrets are never echoed back.
            if ($value !== null && $value !== '') {
                $settings->set("gateway.{$gateway}.{$secretKey}", $value, secret: true);
                $dirty[$secretKey] = '***updated***';
            }
        }

        $oldMode = $settings->get("gateway.{$gateway}.mode", 'test');
        $settings->set("gateway.{$gateway}.mode", $validated['mode']);

        TyroAudit::log('gateway.settings_updated', null, ['gateway' => $gateway, 'mode' => $oldMode], [
            'gateway' => $gateway,
            'mode' => $validated['mode'],
            'secrets' => $dirty,
            'admin_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.gateway-settings')
            ->with('success', ucfirst($gateway).' settings saved.');
    }

    private function mask(?string $value): string
    {
        return $value ? str_repeat('•', 8).substr($value, -4) : '';
    }
}
