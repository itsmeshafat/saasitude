<?php

namespace App\Providers;

use App\Facades\Entitlements;
use App\Services\EntitlementService;
use App\Services\SettingsService;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(EntitlementService::class);

        AliasLoader::getInstance()->alias('Entitlements', Entitlements::class);
    }

    public function boot(): void
    {
        Blade::if('entitlement', function (string $key): bool {
            $workspace = auth()->user()?->currentWorkspace();

            if (! $workspace) {
                return false;
            }

            return app(EntitlementService::class)->for($workspace)->can($key);
        });
    }
}
