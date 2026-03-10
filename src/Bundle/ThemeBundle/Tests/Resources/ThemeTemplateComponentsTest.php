<?php

declare(strict_types=1);

namespace Integrated\Bundle\ThemeBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ThemeTemplateComponentsTest extends TestCase
{
    public function testScraperIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/scraper/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }
}
