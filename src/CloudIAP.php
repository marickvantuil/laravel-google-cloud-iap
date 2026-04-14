<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Illuminate\Http\RedirectResponse;
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

    public static function logout(string $redirectTo = '/'): RedirectResponse
    {
        $response = redirect($redirectTo);

        foreach (request()->cookies->all() as $name => $value) {
            if (
                str_starts_with($name, '__Host-GCP_IAP_AUTH_TOKEN_') ||
                str_starts_with($name, 'GCP_IAP_UID') ||
                str_starts_with($name, 'GCP_IAP_XSRF_NONCE_')
            ) {
                // __Host- prefixed cookies require Secure, Path=/, and no Domain
                if (str_starts_with($name, '__Host-')) {
                    header("Set-Cookie: {$name}=; Path=/; Secure; SameSite=Lax; Max-Age=0", false);
                } else {
                    cookie()->queue(cookie($name, '', -1, '/'));
                }
            }
        }

        return $response;
    }
}
