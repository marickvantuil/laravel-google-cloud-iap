<?php

declare(strict_types=1);

namespace Marick\LaravelGoogleCloudIap;

use Illuminate\Contracts\Auth\Authenticatable;

class IapUser implements Authenticatable
{
    public function __construct(
        public readonly string $sub,
        public readonly string $email,
        public readonly string $domain,
    ) {}

    public static function fromPayload(array $payload): self
    {
        $email = (string) ($payload['email'] ?? '');

        return new self(
            sub: (string) ($payload['sub'] ?? ''),
            email: $email,
            domain: str_contains($email, '@') ? substr($email, strpos($email, '@') + 1) : '',
        );
    }

    public static function fake(string $email, ?string $sub = null): self
    {
        return new self(
            sub: $sub ?? 'fake-'.md5($email),
            email: $email,
            domain: str_contains($email, '@') ? substr($email, strpos($email, '@') + 1) : '',
        );
    }

    public function getAuthIdentifierName(): string
    {
        return 'sub';
    }

    public function getAuthIdentifier(): string
    {
        return $this->sub;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        //
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
