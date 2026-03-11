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
        self::assertStringContainsString("component('integrated_admin:confirm_modal'", $template);
        self::assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("titleHtml: mediaPageTitleHtml", $template);
        self::assertStringContainsString("actionsHtml: mediaPageTitleActionsHtml", $template);
        self::assertStringContainsString("contentHtml: mediaPageTitleContentHtml", $template);
    }

    public function testMediaMenuComponentUsesFolderMenuPanel(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/media/partial/menu_component.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:folder_menu_panel'", $template);
        self::assertStringContainsString('contentHtml: mediaFolderMenuContentHtml', $template);
        self::assertStringContainsString('rootLinkHtml: mediaFolderMenuRootLinkHtml', $template);
        self::assertStringContainsString('searchHtml: mediaFolderMenuSearchHtml', $template);
    }

    public function testMediaEditPanelUsesEditDrawerPanel(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/media/partial/edit_panel.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_drawer_panel'", $template);
        self::assertStringContainsString("editImagePath: path('integrated_content_media_edit_image', {'id': 'REPLACE'})", $template);
        self::assertStringContainsString("editImageIframePath: path('integrated_content_media_edit_image_iframe', {'id': 'REPLACE'})", $template);
    }
}
