<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $authGuards = $guards ? [array_shift($guards)] : [];
        $allowedDomains = $guards;

        $this->authenticate($request, $authGuards);

        if (! empty($allowedDomains)) {
            $user = $this->auth->guard($authGuards[0] ?? null)->user();

            $allowedEmails = array_filter($allowedDomains, fn ($p) => str_contains($p, '@'));
            $allowedDomains = array_filter($allowedDomains, fn ($p) => ! str_contains($p, '@'));

            $allowed = ($user && in_array($user->email, $allowedEmails))
                || ($user && in_array($user->domain, $allowedDomains));

            if (! $allowed) {
                abort(403, 'Forbidden.');
            }
        }

        return $next($request);
    }

    protected function unauthenticated($request, array $guards): void
    {
        abort(401, 'Unauthenticated.');
    }
}
