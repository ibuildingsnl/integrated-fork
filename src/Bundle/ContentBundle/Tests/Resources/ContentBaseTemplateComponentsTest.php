<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentBaseTemplateComponentsTest extends TestCase
{
    public function testContentBaseUsesAdminPageTitleAndOptionsToolbarComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/base.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("component('integrated_admin:options_toolbar'", $template);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $template);
    }
}
