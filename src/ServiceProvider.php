<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/iap.php', 'iap');

        $this->app->singleton('cloud_iap', function (Application $app) {
            return new IapGuard(
                request: $app->make('request'),
                config: $app['config']['iap'] ?? [],
            );
        });
    }

    public function boot(): void
    {
        Auth::extend('iap', function (Application $app, string $name, array $config) {
            return $app->make('cloud_iap');
        });

        $this->app['router']->aliasMiddleware('iap', Authenticate::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/iap.php' => config_path('iap.php'),
            ], 'iap-config');
        }
    }
}
