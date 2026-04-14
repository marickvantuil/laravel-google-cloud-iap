<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Marick\LaravelGoogleCloudIap\Authenticate;
use Marick\LaravelGoogleCloudIap\CloudIAP;
use Marick\LaravelGoogleCloudIap\IapUser;
use PHPUnit\Framework\Attributes\Test;

class IapTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_no_header_is_present(): void
    {
        $this->assertNull(Auth::user());
    }

    #[Test]
    public function auth_check_is_false_when_no_header_is_present(): void
    {
        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function auth_guest_is_true_when_no_header_is_present(): void
    {
        $this->assertTrue(Auth::guest());
    }

    #[Test]
    public function acting_as_returns_an_iap_user(): void
    {
        CloudIAP::actingAs('john@example.com');

        $user = Auth::user();

        $this->assertInstanceOf(IapUser::class, $user);
    }

    #[Test]
    public function acting_as_sets_the_email(): void
    {
        CloudIAP::actingAs('john@example.com');

        $this->assertSame('john@example.com', Auth::user()->email);
    }

    #[Test]
    public function acting_as_derives_the_domain_from_email(): void
    {
        CloudIAP::actingAs('john@example.com');

        $this->assertSame('example.com', Auth::user()->domain);
    }

    #[Test]
    public function acting_as_generates_a_sub_when_not_provided(): void
    {
        CloudIAP::actingAs('john@example.com');

        $this->assertNotEmpty(Auth::user()->sub);
    }

    #[Test]
    public function acting_as_uses_the_provided_sub(): void
    {
        CloudIAP::actingAs('john@example.com', 'accounts.google.com:12345');

        $this->assertSame('accounts.google.com:12345', Auth::user()->sub);
    }

    #[Test]
    public function auth_check_is_true_when_acting_as(): void
    {
        CloudIAP::actingAs('john@example.com');

        $this->assertTrue(Auth::check());
    }

    #[Test]
    public function auth_id_returns_the_sub(): void
    {
        CloudIAP::actingAs('john@example.com', 'my-sub');

        $this->assertSame('my-sub', Auth::id());
    }

    #[Test]
    public function fake_returns_null_user(): void
    {
        CloudIAP::fake();

        $this->assertNull(Auth::user());
    }

    #[Test]
    public function fake_prevents_jwt_verification(): void
    {
        request()->headers->set('X-Goog-IAP-JWT-Assertion', 'some.invalid.jwt');

        CloudIAP::fake();

        $this->assertNull(Auth::user());
    }

    #[Test]
    public function facade_and_auth_guard_resolve_the_same_instance(): void
    {
        CloudIAP::actingAs('john@example.com');

        $this->assertSame(
            Auth::guard('iap')->user(),
            CloudIAP::user(),
        );
    }

    #[Test]
    public function authenticate_middleware_returns_401_when_unauthenticated(): void
    {
        Route::middleware(Authenticate::class.':iap')->get('/protected', fn () => 'ok');

        $this->get('/protected')->assertStatus(401);
    }

    #[Test]
    public function authenticate_middleware_passes_when_acting_as(): void
    {
        Route::middleware(Authenticate::class.':iap')->get('/protected', fn () => 'ok');

        CloudIAP::actingAs('john@example.com');

        $this->get('/protected')->assertStatus(200);
    }

    #[Test]
    public function iap_user_from_payload_extracts_claims(): void
    {
        $user = IapUser::fromPayload([
            'sub' => 'accounts.google.com:12345',
            'email' => 'john@example.com',
        ]);

        $this->assertSame('accounts.google.com:12345', $user->sub);
        $this->assertSame('john@example.com', $user->email);
        $this->assertSame('example.com', $user->domain);
    }
}
