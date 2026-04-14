<sub>Companion packages: <a href="https://github.com/marickvantuil/laravel-google-cloud-logging">Cloud Logging</a>, <a href="https://github.com/stackkit/laravel-google-cloud-scheduler">Cloud Scheduler</a>, <a href="https://github.com/stackkit/laravel-google-cloud-tasks-queue">Cloud Tasks</a></sub>

# Introduction

This package integrates [Google Cloud Identity-Aware Proxy (IAP)](https://cloud.google.com/iap) with Laravel's authentication system.

When IAP is enabled on a Cloud Run service or App Engine application, Google intercepts every request and requires the user to authenticate with their Google account. Authenticated requests are forwarded to your app with a signed JWT header (`X-Goog-IAP-JWT-Assertion`) containing the user's identity.

This package verifies that JWT and exposes the user via `Auth::user()`, so you can access the logged-in user anywhere in your Laravel application without building your own login system.

This package requires Laravel 12 or 13.

> **Note:** If your app uses database sessions, the `user_id` column in the `sessions` table must be a `varchar` instead of the default `bigint`, as IAP user identifiers are strings. Run a migration to change the column type before using this package.

# Installation

Install the package with Composer:

```console
composer require marick/laravel-google-cloud-iap
```

Register the `iap` guard in `config/auth.php`:

```php
'guards' => [
    'iap' => [
        'driver' => 'iap',
    ],
],
```

Set it as your default guard, or use it explicitly per route:

```php
// Set as default
'defaults' => [
    'guard' => 'iap',
],
```

# How to

## Access the authenticated user

```php
use Illuminate\Support\Facades\Auth;

Auth::user()->email;   // john@company.com
Auth::user()->sub;     // accounts.google.com:123456789
Auth::user()->domain;  // company.com

Auth::check();  // true when the IAP header is present and valid
Auth::guest();  // true when unauthenticated
Auth::id();     // returns the sub claim
```

The user is an `IapUser` value object — it has no database backing. Google manages your users; this package just reads what IAP tells you.

## Protect routes

Use the `iap` middleware alias instead of `auth`. This is necessary because IAP has no login page to redirect to — unauthenticated requests return a `401` response instead.

```php
Route::middleware('iap:iap')->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

The `iap` alias is registered automatically by the package's service provider.

## Validate the audience claim

IAP signs JWTs with an audience claim (`aud`) specific to your backend service. Validating it prevents tokens issued for one service from being accepted by another. Set it via the environment:

```dotenv
IAP_AUDIENCE=/projects/123456789/global/backendServices/456789123
```

For App Engine the format is `/projects/PROJECT_NUMBER/apps/PROJECT_ID`.

Leave it unset to skip audience validation (fine for single-service setups).

# Testing

## Act as a user

```php
use Marick\LaravelGoogleCloudIap\CloudIAP;

CloudIAP::actingAs('john@company.com');

$this->assertTrue(Auth::check());
$this->assertSame('john@company.com', Auth::user()->email);
$this->assertSame('company.com', Auth::user()->domain);
```

Provide a custom `sub` if your tests need a specific identifier:

```php
CloudIAP::actingAs('john@company.com', 'accounts.google.com:12345');
```

## Test unauthenticated behaviour

```php
CloudIAP::fake();

$this->assertNull(Auth::user());
$this->assertTrue(Auth::guest());
```

`fake()` also ensures that any `X-Goog-IAP-JWT-Assertion` header present during a test is ignored — no HTTP calls are made to Google's certificate endpoint.
