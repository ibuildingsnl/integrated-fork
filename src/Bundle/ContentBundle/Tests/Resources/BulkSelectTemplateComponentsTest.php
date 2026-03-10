<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BulkSelectTemplateComponentsTest extends TestCase
{
    public function testBulkSelectUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/bulk/select.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("component('integrated_admin:options_toolbar'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }
}
