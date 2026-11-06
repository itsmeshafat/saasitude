<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SettingsService::class);
    }

    public function test_get_returns_default_when_key_missing(): void
    {
        $value = $this->service->get('nonexistent', 'fallback');

        $this->assertSame('fallback', $value);
    }

    public function test_get_returns_plain_value(): void
    {
        Setting::create(['key' => 'trial_length_days', 'value' => '14', 'group' => 'billing', 'is_secret' => false]);

        $this->assertSame('14', $this->service->get('trial_length_days'));
    }

    public function test_get_decrypts_secret_value(): void
    {
        $setting = Setting::create(['key' => 'gateway_secret', 'value' => null, 'group' => 'secret', 'is_secret' => true]);
        $setting->update(['value' => Crypt::encryptString('sk_test_abc')]);

        $this->assertSame('sk_test_abc', $this->service->get('gateway_secret'));
    }

    public function test_set_updates_plain_value(): void
    {
        Setting::create(['key' => 'invoice_prefix', 'value' => 'INV', 'group' => 'billing', 'is_secret' => false]);

        $this->service->set('invoice_prefix', 'BILL');

        $this->assertDatabaseHas('settings', ['key' => 'invoice_prefix', 'value' => 'BILL']);
    }

    public function test_set_encrypts_secret_value(): void
    {
        Setting::create(['key' => 'gateway_key', 'value' => null, 'group' => 'secret', 'is_secret' => true]);

        $this->service->set('gateway_key', 'sk_live_xyz');

        $raw = Setting::where('key', 'gateway_key')->value('value');
        $this->assertNotSame('sk_live_xyz', $raw);
        $this->assertSame('sk_live_xyz', Crypt::decryptString($raw));
    }
}
