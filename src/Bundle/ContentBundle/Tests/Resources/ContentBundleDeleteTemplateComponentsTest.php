<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentBundleDeleteTemplateComponentsTest extends TestCase
{
    #[DataProvider('pageTitleProvider')]
    public function testDeleteTemplateUsesAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public static function pageTitleProvider(): iterable
    {
        yield ['channel/delete.html.twig'];
        yield ['relation/delete.html.twig'];
        yield ['content_type/delete.html.twig'];
    }
}
