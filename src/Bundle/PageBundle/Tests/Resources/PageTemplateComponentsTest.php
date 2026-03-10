<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageTemplateComponentsTest extends TestCase
{
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
