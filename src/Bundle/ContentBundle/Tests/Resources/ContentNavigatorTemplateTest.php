<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentNavigatorTemplateTest extends TestCase
{
    public function testTemplateContainsTurboFrameAccessibilityMarkers(): void
    {
        $template = file_get_contents(__DIR__ . '/../../Resources/views/content/base.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('id="content-navigator"', $template);
        $this->assertStringContainsString('data-result-count="', $template);
        $this->assertStringContainsString('id="content-navigator-live-region"', $template);
        $this->assertStringContainsString('aria-live="polite"', $template);
        $this->assertStringContainsString('role="button"', $template);
        $this->assertStringContainsString('aria-expanded=', $template);
    }

    public function testIndexTemplateContainsLiveLockMarkers(): void
    {
        $template = file_get_contents(__DIR__ . '/../../Resources/views/content/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('data-lock-resource-type="', $template);
        $this->assertStringContainsString('data-lock-resource-id="', $template);
        $this->assertStringContainsString('data-lock-slot', $template);
        $this->assertStringContainsString("integrated_content_content_locks_status", $template);
    }

    public function testRoutingContainsLiveLockStatusEndpoint(): void
    {
        $routing = file_get_contents(__DIR__ . '/../../Resources/config/routing/content.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('id="integrated_content_content_locks_status"', $routing);
        $this->assertStringContainsString('path="/locks-status" methods="POST"', $routing);
    }
}
