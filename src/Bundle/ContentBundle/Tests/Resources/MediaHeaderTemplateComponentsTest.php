<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class MediaHeaderTemplateComponentsTest extends TestCase
{
    public function testMediaHeaderUsesAdminOptionsToolbarComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/media/partial/header_component.html.twig');

        self::assertIsString($template);
        self::assertSame(2, substr_count($template, "component('integrated_admin:options_toolbar'"));
        self::assertStringContainsString("extraClass: 'flex justify-between sm:mr-2'", $template);
        self::assertStringContainsString("extraClass: 'flex flex-1'", $template);
    }
}
