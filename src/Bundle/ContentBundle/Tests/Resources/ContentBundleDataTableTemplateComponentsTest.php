<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentBundleDataTableTemplateComponentsTest extends TestCase
{
    #[DataProvider('dataTableProvider')]
    public function testTemplateUsesAdminDataTableComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }

    public function testContentTypeListUsesAdminSectionCard(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content_type/partial/list.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
    }

    public static function dataTableProvider(): iterable
    {
        yield ['channel/index.html.twig'];
        yield ['relation/index.html.twig'];
        yield ['content_type/partial/list.html.twig'];
        yield ['content/index.html.twig'];
    }
}
