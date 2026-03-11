<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentModalTemplateComponentsTest extends TestCase
{
    public function testEditModalPartialUsesAdminIframeModal(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/partial/edit_modal.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:iframe_modal'", $template);
        self::assertStringContainsString("modalId: 'navigator-edit-modal'", $template);
        self::assertStringContainsString("iframeId: 'editmodaliframe'", $template);
    }

    public function testContentEditUsesAdminConfirmModalForUnsavedChanges(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:confirm_modal'", $template);
        self::assertStringContainsString("modalId: 'content-edit-modal'", $template);
    }

    public function testContentEditUsesAdminIframeModalForRelationAdd(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:iframe_modal'", $template);
        self::assertStringContainsString("modalId: 'relation-add-modal'", $template);
        self::assertStringContainsString("iframeId: 'relation-add-iframe'", $template);
    }

    public function testMediaEditImageUsesAdminSelectionModal(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/media/edit_image.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:selection_modal'", $template);
        self::assertStringContainsString("modalId: 'image-edit-modal'", $template);
    }

    public function testMediaEditFrameUsesAdminAlertBoxForFormErrors(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/partial/edit_frame.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:alert_box'", $template);
        self::assertStringContainsString("variant: 'danger'", $template);
    }
}
