<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentNewFlowTest extends TestCase
{
    public function testNewTemplateDisablesTurboForFormSubmissions(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/new.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% block form_start %}', $template);
        $this->assertStringContainsString("'data-turbo': 'false'", $template);
    }
}
