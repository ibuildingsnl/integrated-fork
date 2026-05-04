<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentEditorExtensionPointTemplateTest extends TestCase
{
    public function testFullEditorExposesSidebarExtensionPoint(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% block editor_sidebar_extensions %}', $template);
        self::assertStringContainsString('integrated_content_editor_sidebar_extensions(content, type', $template);
    }

    public function testIframeEditorExposesSidebarExtensionPoint(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/partial/edit_frame.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% block editor_sidebar_extensions %}', $template);
        self::assertStringContainsString('integrated_content_editor_sidebar_extensions(content, type', $template);
    }
}
