<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BrandTemplateComponentsTest extends TestCase
{
    public function testBrandIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/brand/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }
}
