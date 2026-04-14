<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, string ...$guards): mixed
    {
        $authGuards = $guards ? [array_shift($guards)] : [];
        $allowedDomains = $guards;

        $this->authenticate($request, $authGuards);

        if (! empty($allowedDomains)) {
            $user = $this->auth->guard($authGuards[0] ?? null)->user();

            if (! $user || ! in_array($user->domain, $allowedDomains)) {
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
