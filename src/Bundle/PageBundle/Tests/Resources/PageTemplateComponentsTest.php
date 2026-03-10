<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageTemplateComponentsTest extends TestCase
{
    public function testPageIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }

    /**
     * @dataProvider pageCrudTemplateProvider
     */
    public function testPageCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public static function pageCrudTemplateProvider(): iterable
    {
        yield ['page/new.html.twig'];
        yield ['page/edit.html.twig'];
        yield ['page/delete.html.twig'];
        yield ['page/copy.html.twig'];
    }
}
