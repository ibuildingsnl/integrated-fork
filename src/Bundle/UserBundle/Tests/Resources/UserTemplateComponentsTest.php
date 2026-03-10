<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class UserTemplateComponentsTest extends TestCase
{
    /**
     * @dataProvider indexTemplateProvider
     */
    public function testIndexTemplateUsesAdminComponents(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }

    public static function indexTemplateProvider(): iterable
    {
        yield ['group/index.html.twig'];
        yield ['scope/index.html.twig'];
        yield ['ip_list/index.html.twig'];
    }
}
