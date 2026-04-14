<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Route;
use Marick\LaravelGoogleCloudIap\Authenticate;
use Marick\LaravelGoogleCloudIap\CloudIAP;
use PHPUnit\Framework\Attributes\Test;

class MiddlewareTest extends TestCase
{
    #[Test]
    public function it_returns_401_when_unauthenticated(): void
    {
        Route::middleware(Authenticate::class.':iap')->get('/protected', fn () => 'ok');

        $this->get('/protected')->assertStatus(401);
    }

    #[Test]
    public function it_passes_when_authenticated(): void
    {
        Route::middleware(Authenticate::class.':iap')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@example.com');

        $this->get('/protected')->assertStatus(200);
    }

    #[Test]
    public function it_allows_user_from_allowed_domain(): void
    {
        Route::middleware(Authenticate::class.':iap,example.com')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@example.com');

        $this->get('/protected')->assertStatus(200);
    }

    #[Test]
    public function it_returns_403_when_domain_not_allowed(): void
    {
        Route::middleware(Authenticate::class.':iap,other.com')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@example.com');

        $this->get('/protected')->assertStatus(403);
    }

    #[Test]
    public function it_allows_user_with_specific_email(): void
    {
        Route::middleware(Authenticate::class.':iap,john@example.com')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@example.com');

        $this->get('/protected')->assertStatus(200);
    }

    #[Test]
    public function it_returns_403_when_email_not_allowed(): void
    {
        Route::middleware(Authenticate::class.':iap,jane@example.com')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@example.com');

        $this->get('/protected')->assertStatus(403);
    }

    #[Test]
    public function it_allows_multiple_domains(): void
    {
        Route::middleware(Authenticate::class.':iap,example.com,other.com')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@other.com');

        $this->get('/protected')->assertStatus(200);
    }

    #[Test]
    public function it_allows_mixed_email_and_domain(): void
    {
        Route::middleware(Authenticate::class.':iap,example.com,jane@partner.com')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('jane@partner.com');

        $this->get('/protected')->assertStatus(200);
    }

    #[Test]
    public function it_returns_403_for_mixed_when_neither_matches(): void
    {
        Route::middleware(Authenticate::class.':iap,example.com,jane@partner.com')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@other.com');

        $this->get('/protected')->assertStatus(403);
    }
}
