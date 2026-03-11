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

    public function testContentTypeShowUsesAdminDataTableComponentForFields(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content_type/show.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }

    public function testRelationShowUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/relation/show.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    /**
     * @dataProvider detailListProvider
     */
    public function testShowTemplateUsesAdminDetailListComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:detail_list'", $template);
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

    public static function detailListProvider(): iterable
    {
        yield ['channel/show.html.twig'];
        yield ['relation/show.html.twig'];
        yield ['content_type/show.html.twig'];
    }
}
