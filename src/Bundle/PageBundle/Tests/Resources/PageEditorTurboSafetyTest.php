<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class PageEditorTurboSafetyTest extends TestCase
{
    public function testNewPageTemplateDisablesTurboForFormSubmissions(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/new.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{ attr: { \'data-turbo\': \'false\' } }', $template);
    }

    public function testEditPageTemplateDisablesTurboForFormSubmissions(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{ attr: { \'data-turbo\': \'false\' } }', $template);
    }
}
