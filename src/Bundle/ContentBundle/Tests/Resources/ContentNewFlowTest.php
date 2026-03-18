<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentNewFlowTest extends TestCase
{
    public function testNewTemplateDoesNotForceTurboOffForFormSubmissions(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/new.html.twig');

        $this->assertIsString($template);
        $this->assertStringNotContainsString("'data-turbo': 'false'", $template);
    }
}
