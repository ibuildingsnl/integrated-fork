<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class FlashTurboStreamTemplateTest extends TestCase
{
    public function testFlashTurboStreamAppendsMessagesToFlashContainer(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/flash.turbo_stream.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('<turbo-stream action="append" target="flash-messages">', $template);
        $this->assertStringNotContainsString('<turbo-stream action="replace" target="flash-messages">', $template);
    }
}
