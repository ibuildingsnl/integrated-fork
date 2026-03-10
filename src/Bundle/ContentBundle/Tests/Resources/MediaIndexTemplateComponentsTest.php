<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class MediaIndexTemplateComponentsTest extends TestCase
{
    public function testMediaIndexComponentUsesAdminPageTitleComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/media/partial/index_component.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("titleHtml: mediaPageTitleHtml", $template);
        self::assertStringContainsString("actionsHtml: mediaPageTitleActionsHtml", $template);
        self::assertStringContainsString("contentHtml: mediaPageTitleContentHtml", $template);
    }
}
