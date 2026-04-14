<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin IapGuard
 */
class CloudIAP extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cloud_iap';
    }

    public static function actingAs(string $email, ?string $sub = null): void
    {
        $guard = static::getFacadeRoot();
        $guard->fake();
        $guard->setUser(IapUser::fake($email, $sub));
    }

    public static function fake(): void
    {
        static::getFacadeRoot()->fake();
    }
}
