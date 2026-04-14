<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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

        Blade::directive('iapauth', function (string $expression) {
            if (empty(trim($expression))) {
                return "<?php if (auth()->guard('iap')->check()): ?>";
            }

            return "<?php if (auth()->guard('iap')->check() && auth()->guard('iap')->user()->allows({$expression})): ?>";
        });

        Blade::directive('endiapauth', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('iapguest', function () {
            return "<?php if (auth()->guard('iap')->guest()): ?>";
        });

        Blade::directive('endiapguest', function () {
            return '<?php endif; ?>';
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/iap.php' => config_path('iap.php'),
            ], 'iap-config');
        }
    }
}
