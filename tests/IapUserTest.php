<?php

declare(strict_types=1);

namespace Tests;

use Marick\LaravelGoogleCloudIap\IapUser;
use PHPUnit\Framework\Attributes\Test;

class IapUserTest extends TestCase
{
    #[Test]
    public function allows_returns_true_for_matching_domain(): void
    {
        $user = IapUser::fake('john@example.com');

        $this->assertTrue($user->allows('example.com'));
    }

    #[Test]
    public function allows_returns_false_for_non_matching_domain(): void
    {
        $user = IapUser::fake('john@example.com');

        $this->assertFalse($user->allows('other.com'));
    }

    #[Test]
    public function allows_returns_true_for_matching_email(): void
    {
        $user = IapUser::fake('john@example.com');

        $this->assertTrue($user->allows('john@example.com'));
    }

    #[Test]
    public function allows_returns_false_for_non_matching_email(): void
    {
        $user = IapUser::fake('john@example.com');

        $this->assertFalse($user->allows('jane@example.com'));
    }

    #[Test]
    public function allows_returns_true_when_domain_matches_in_mixed_params(): void
    {
        $user = IapUser::fake('john@example.com');

        $this->assertTrue($user->allows('jane@other.com', 'example.com'));
    }

    #[Test]
    public function allows_returns_true_when_email_matches_in_mixed_params(): void
    {
        $user = IapUser::fake('john@example.com');

        $this->assertTrue($user->allows('john@example.com', 'other.com'));
    }

    #[Test]
    public function allows_returns_false_when_nothing_matches(): void
    {
        $user = IapUser::fake('john@example.com');

        $this->assertFalse($user->allows('jane@example.com', 'other.com'));
    }
}
