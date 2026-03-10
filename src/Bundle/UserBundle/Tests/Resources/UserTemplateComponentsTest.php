<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class UserTemplateComponentsTest extends TestCase
{
    public function testGroupIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/group/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }
}
