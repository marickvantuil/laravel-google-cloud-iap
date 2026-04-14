<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Blade;
use Marick\LaravelGoogleCloudIap\CloudIAP;
use PHPUnit\Framework\Attributes\Test;

class BladeTest extends TestCase
{
    #[Test]
    public function iapauth_renders_content_when_authenticated(): void
    {
        CloudIAP::actingAs('john@example.com');

        $output = Blade::render('@iapauth hello @endiapauth');

        $this->assertStringContainsString('hello', $output);
    }

    #[Test]
    public function iapauth_does_not_render_content_when_unauthenticated(): void
    {
        CloudIAP::fake();

        $output = Blade::render('@iapauth hello @endiapauth');

        $this->assertStringNotContainsString('hello', $output);
    }

    #[Test]
    public function iapauth_with_domain_renders_content_for_matching_domain(): void
    {
        CloudIAP::actingAs('john@example.com');

        $output = Blade::render("@iapauth('example.com') hello @endiapauth");

        $this->assertStringContainsString('hello', $output);
    }

    #[Test]
    public function iapauth_with_domain_does_not_render_content_for_non_matching_domain(): void
    {
        CloudIAP::actingAs('john@example.com');

        $output = Blade::render("@iapauth('other.com') hello @endiapauth");

        $this->assertStringNotContainsString('hello', $output);
    }

    #[Test]
    public function iapauth_with_email_renders_content_for_matching_email(): void
    {
        CloudIAP::actingAs('john@example.com');

        $output = Blade::render("@iapauth('john@example.com') hello @endiapauth");

        $this->assertStringContainsString('hello', $output);
    }

    #[Test]
    public function iapauth_with_email_does_not_render_content_for_non_matching_email(): void
    {
        CloudIAP::actingAs('john@example.com');

        $output = Blade::render("@iapauth('jane@example.com') hello @endiapauth");

        $this->assertStringNotContainsString('hello', $output);
    }

    #[Test]
    public function iapguest_renders_content_when_unauthenticated(): void
    {
        CloudIAP::fake();

        $output = Blade::render('@iapguest hello @endiapguest');

        $this->assertStringContainsString('hello', $output);
    }

    #[Test]
    public function iapguest_does_not_render_content_when_authenticated(): void
    {
        CloudIAP::actingAs('john@example.com');

        $output = Blade::render('@iapguest hello @endiapguest');

        $this->assertStringNotContainsString('hello', $output);
    }
}
