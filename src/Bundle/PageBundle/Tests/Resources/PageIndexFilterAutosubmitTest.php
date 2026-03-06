<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class PageIndexFilterAutosubmitTest extends TestCase
{
    public function testPageFilterAutosubmitIgnoresSyntheticChangeEvents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("form.addEventListener('change', function (event) {", $template);
        $this->assertStringContainsString('if (!event.isTrusted) {', $template);
        $this->assertStringContainsString('return;', $template);
        $this->assertStringContainsString('submitForm();', $template);
    }
}
