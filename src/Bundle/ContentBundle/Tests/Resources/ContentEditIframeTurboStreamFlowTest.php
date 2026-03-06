<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentEditIframeTurboStreamFlowTest extends TestCase
{
    public function testIframeTurboStreamTemplateIncludesFlashMessages(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.iframe.turbo_stream.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('@IntegratedContent/content/flash.turbo_stream.html.twig', $template);
        $this->assertStringContainsString('target="media-edit-panel"', $template);
    }
}
