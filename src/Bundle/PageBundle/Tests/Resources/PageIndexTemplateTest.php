<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageIndexTemplateTest extends TestCase
{
    public function testIndexTemplateRebindsFilterAutosubmitAfterTurboNavigation(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('bindPageFilterAutoSubmit', $template);
        $this->assertStringContainsString("document.addEventListener('turbo:load', bindPageFilterAutoSubmit);", $template);
        $this->assertStringContainsString("form.requestSubmit ? form.requestSubmit() : form.submit();", $template);
        $this->assertStringNotContainsString('event.isTrusted', $template);
    }
}
