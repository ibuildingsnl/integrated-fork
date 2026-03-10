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
}
