<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockIframeLifecycleTemplateTest extends TestCase
{
    public function testSavedTemplateDispatchesBlockAddedEvent(): void
    {
        $template = (string) file_get_contents(__DIR__.'/../../Resources/views/block/saved.iframe.html.twig');

        self::assertStringContainsString("CustomEvent('block-added'", $template);
    }

    public function testDeletedTemplateDispatchesBlockDeletedEvent(): void
    {
        $template = (string) file_get_contents(__DIR__.'/../../Resources/views/block/deleted.iframe.html.twig');

        self::assertStringContainsString("CustomEvent('block-deleted'", $template);
    }

    public function testEditTemplateUsesIframeDeleteParameterWhenEditingInIframe(): void
    {
        $template = (string) file_get_contents(__DIR__.'/../../Resources/views/block/edit.html.twig');

        self::assertStringContainsString("app.request.requestFormat == 'iframe.html'", $template);
        self::assertStringContainsString("'_format': 'iframe.html'", $template);
    }
}

