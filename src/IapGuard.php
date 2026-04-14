<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Google\Auth\AccessToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class IapGuard implements Guard
{
    private bool $faking = false;

    private ?Authenticatable $user = null;

    private bool $userResolved = false;

    public function __construct(
        private readonly Request $request,
        private readonly array $config = [],
    ) {}

    public function fake(): void
    {
        $this->faking = true;
        $this->user = null;
        $this->userResolved = false;
    }

    public function user(): ?Authenticatable
    {
        if ($this->faking) {
            return $this->user;
        }

        if ($this->userResolved) {
            return $this->user;
        }

        $this->userResolved = true;
        $this->user = $this->resolveUser();

        return $this->user;
    }

    private function resolveUser(): ?IapUser
    {
        $jwt = $this->request->header('X-Goog-IAP-JWT-Assertion');

        if (! $jwt) {
            return null;
        }

        $payload = $this->verifyJwt($jwt);

        if (! $payload) {
            return null;
        }

        return IapUser::fromPayload($payload);
    }

    private function verifyJwt(string $jwt): array|false
    {
        try {
            $accessToken = new AccessToken;
            $payload = $accessToken->verify($jwt, [
                'certsLocation' => AccessToken::IAP_CERT_URL,
            ]);

            if (! is_array($payload)) {
                return false;
            }

            if ($audience = $this->config['audience'] ?? null) {
                if (($payload['aud'] ?? null) !== $audience) {
                    return false;
                }
            }

            return $payload;
        } catch (\Exception) {
            return false;
        }
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->faking = true;
        $this->user = $user;

        return $this;
    }
}
