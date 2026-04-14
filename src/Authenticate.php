<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards): void
    {
        abort(401, 'Unauthenticated.');
    }
}
