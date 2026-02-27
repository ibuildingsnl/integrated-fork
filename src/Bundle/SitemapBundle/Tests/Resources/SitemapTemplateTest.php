<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class SitemapTemplateTest extends TestCase
{
    public function testIndexTemplateContainsPagesSection(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/default/index.xml.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% if pagesCount > 0 %}', $template);
        self::assertStringContainsString('{% for page in 1..pagesCount %}', $template);
        self::assertStringContainsString("path('integrated_sitemap_list_pages'", $template);
    }

    public function testListTemplateSupportsPagePathFallback(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/default/list.xml.twig');

        self::assertIsString($template);
        self::assertStringContainsString('document.path is defined', $template);
        self::assertStringContainsString("document.path starts with('/')", $template);
        self::assertStringContainsString('integrated_url(document)', $template);
    }
}
