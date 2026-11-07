<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'trial_length_days', 'value' => '14', 'group' => 'billing', 'is_secret' => false],
            ['key' => 'grace_period_days', 'value' => '5', 'group' => 'billing', 'is_secret' => false],
            ['key' => 'invoice_prefix', 'value' => 'INV', 'group' => 'billing', 'is_secret' => false],
            ['key' => 'invoice_counter', 'value' => '0', 'group' => 'billing', 'is_secret' => false],
        ];

        foreach ($defaults as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
