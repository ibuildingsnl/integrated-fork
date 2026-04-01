<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class EditorAssetHookTemplateTest extends TestCase
{
    public function testContentEditTemplateIncludesOptionalProjectEditorStylesheetHook(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("include('admin/_integrated_editor_stylesheets.html.twig', ignore_missing = true)", $template);
    }

    public function testBlockTemplatesIncludeOptionalProjectEditorStylesheetHook(): void
    {
        $editTemplate = file_get_contents(__DIR__.'/../../../BlockBundle/Resources/views/block/edit.html.twig');
        $newTemplate = file_get_contents(__DIR__.'/../../../BlockBundle/Resources/views/block/new.html.twig');

        self::assertIsString($editTemplate);
        self::assertIsString($newTemplate);
        self::assertStringContainsString("include('admin/_integrated_editor_stylesheets.html.twig', ignore_missing = true)", $editTemplate);
        self::assertStringContainsString("include('admin/_integrated_editor_stylesheets.html.twig', ignore_missing = true)", $newTemplate);
    }
}
