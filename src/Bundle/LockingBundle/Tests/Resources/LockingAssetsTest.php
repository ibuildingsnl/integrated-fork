<?php

declare(strict_types=1);

namespace Integrated\Bundle\LockingBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class LockingAssetsTest extends TestCase
{
    public function testRoutingUsesPostAndExposesReleaseEndpoint(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing.xml');

        self::assertIsString($routing);
        self::assertStringContainsString('id="integrated_locking_api_refresh"', $routing);
        self::assertStringContainsString('path="/_locking/api/refresh" methods="POST"', $routing);
        self::assertStringContainsString('id="integrated_locking_api_release"', $routing);
        self::assertStringContainsString('path="/_locking/api/release" methods="POST"', $routing);
    }

    public function testRefreshTemplatePostsAndReleasesOnLeave(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/locking.refresh.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("method: 'POST'", $template);
        self::assertStringContainsString("path('integrated_locking_api_release')", $template);
        self::assertStringContainsString("document.addEventListener('turbo:before-cache'", $template);
        self::assertStringContainsString("window.addEventListener('pagehide'", $template);
    }
}

