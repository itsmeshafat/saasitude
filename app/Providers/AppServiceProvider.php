<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Facades\Entitlements;
use App\Gateways\StripeGateway;
use App\Services\EntitlementService;
use App\Services\GatewayCustomerResolver;
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
        $this->app->singleton(GatewayCustomerResolver::class);
        $this->app->bind(PaymentGateway::class, StripeGateway::class);

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
