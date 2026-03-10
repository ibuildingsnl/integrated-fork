<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentHistoryBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentHistoryTemplateComponentsTest extends TestCase
{
    /**
     * @dataProvider indexTemplateProvider
     */
    public function testIndexTemplatesUseAdminComponents(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }

    public static function indexTemplateProvider(): iterable
    {
        yield ['content_history/index.html.twig'];
        yield ['content_history/index.iframe.html.twig'];
    }
}
