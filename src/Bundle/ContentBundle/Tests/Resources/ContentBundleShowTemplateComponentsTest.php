<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentBundleShowTemplateComponentsTest extends TestCase
{
    #[DataProvider('pageTitleProvider')]
    public function testShowTemplateUsesAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    #[DataProvider('sectionCardProvider')]
    public function testShowTemplateUsesAdminSectionCardComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
    }

    public static function pageTitleProvider(): iterable
    {
        yield ['channel/show.html.twig'];
        yield ['relation/show.html.twig'];
        yield ['content_type/show.html.twig'];
    }

    public static function sectionCardProvider(): iterable
    {
        yield ['channel/show.html.twig'];
        yield ['content_type/show.html.twig'];
    }
}
