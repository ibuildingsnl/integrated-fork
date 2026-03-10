<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentBundleFormTemplateComponentsTest extends TestCase
{
    #[DataProvider('pageTitleProvider')]
    public function testFormTemplateUsesAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public static function pageTitleProvider(): iterable
    {
        yield ['channel/new.html.twig'];
        yield ['channel/edit.html.twig'];
        yield ['relation/new.html.twig'];
        yield ['relation/edit.html.twig'];
        yield ['content_type/new.html.twig'];
        yield ['content_type/edit.html.twig'];
    }
}
