<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        if ($setting->is_secret && $setting->value !== null) {
            return Crypt::decryptString($setting->value);
        }

        return $setting->value;
    }

    public function set(string $key, mixed $value, ?bool $secret = null): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        if ($secret !== null) {
            $setting->is_secret = $secret;
        }

        $stored = $setting->is_secret && $value !== null
            ? Crypt::encryptString((string) $value)
            : $value;

        $setting->fill(['value' => $stored])->save();
    }
}
