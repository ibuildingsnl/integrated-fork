<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockTemplateComponentsTest extends TestCase
{
    /**
     * @dataProvider blockCrudTemplateProvider
     */
    public function testBlockCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public static function blockCrudTemplateProvider(): iterable
    {
        yield ['block/new.html.twig'];
        yield ['block/edit.html.twig'];
        yield ['block/delete.html.twig'];
    }
}
