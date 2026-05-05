<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class EditorBrandStyleContractTest extends TestCase
{
    public function testEditorSupportsButtonFormatsOnLinksAndNonLinks(): void
    {
        $editorSource = file_get_contents(__DIR__.'/../../Resources/assets/js/editor.js');
        $collectionSource = file_get_contents(__DIR__.'/../../../ContentBundle/Resources/assets/js/collection.js');

        self::assertIsString($editorSource);
        self::assertIsString($collectionSource);

        self::assertStringContainsString("newStyle.selector = 'a';", $editorSource);
        self::assertStringContainsString('delete newStyle.inline;', $editorSource);
        self::assertStringNotContainsString("newStyle.inline = 'span';", $editorSource);
        self::assertStringContainsString("newStyle.selector = 'a';", $collectionSource);
        self::assertStringContainsString('delete newStyle.inline;', $collectionSource);
        self::assertStringNotContainsString("newStyle.inline = 'span';", $collectionSource);
    }

    public function testEditorDoesNotExposeSuperscriptOrSubscriptStyles(): void
    {
        $editorSource = file_get_contents(__DIR__.'/../../Resources/assets/js/editor.js');
        $collectionSource = file_get_contents(__DIR__.'/../../../ContentBundle/Resources/assets/js/collection.js');

        self::assertIsString($editorSource);
        self::assertIsString($collectionSource);

        self::assertStringNotContainsString("title: 'Superscript'", $editorSource);
        self::assertStringNotContainsString("title: 'Subscript'", $editorSource);
        self::assertStringNotContainsString(' underline subscript superscript | ', $editorSource);
        self::assertStringNotContainsString("title: 'Superscript'", $collectionSource);
        self::assertStringNotContainsString("title: 'Subscript'", $collectionSource);
    }

    public function testEditorDeduplicatesCollectedStyleFormats(): void
    {
        $editorSource = file_get_contents(__DIR__.'/../../Resources/assets/js/editor.js');

        self::assertIsString($editorSource);
        self::assertStringContainsString('function deduplicateTinyMceStyleFormats(styles = [])', $editorSource);
        self::assertStringContainsString('style_formats = deduplicateTinyMceStyleFormats(', $editorSource);
    }

    public function testEditorBindsToolbarPreviewThemeAndPrimaryChannelSync(): void
    {
        $editorSource = file_get_contents(__DIR__.'/../../Resources/assets/js/editor.js');
        $contentFormSource = file_get_contents(__DIR__.'/../../../ContentBundle/Resources/views/form/form_div_layout.html.twig');
        $primaryChannelSource = file_get_contents(__DIR__.'/../../../ContentBundle/Resources/public/js/primary_channel.js');
        $formThemeSource = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        self::assertIsString($editorSource);
        self::assertIsString($contentFormSource);
        self::assertIsString($primaryChannelSource);
        self::assertIsString($formThemeSource);

        self::assertStringContainsString("const TINYMCE_BRAND_THEME_EVENT = 'integrated:editor-brand-theme';", $editorSource);
        self::assertStringContainsString('ensureTinyMceToolbarPreviewStyles()', $editorSource);
        self::assertStringContainsString('document.addEventListener(TINYMCE_BRAND_THEME_EVENT', $editorSource);
        self::assertStringContainsString('applyTinyMceBrandThemeToEditor(editor, currentContentStyle);', $editorSource);
        self::assertStringContainsString('.tox-tinymce-aux .tox-collection__item .tox-collection__item-label h1', $editorSource);
        self::assertStringContainsString('border-radius: 999px !important;', $editorSource);

        self::assertStringContainsString('data-channel-brand-color="{{ option_brand_color }}"', $contentFormSource);
        self::assertStringContainsString('data-channel-brand-secondary-color="{{ option_brand_secondary_color }}"', $contentFormSource);
        self::assertStringContainsString('document.dispatchEvent(new CustomEvent(TINYMCE_BRAND_THEME_EVENT', $primaryChannelSource);
        self::assertStringContainsString('normalizePrimaryChannelSelection();', $primaryChannelSource);
        self::assertStringContainsString('$primaryChannel.val($selectedInput.val());', $primaryChannelSource);

        self::assertStringContainsString('form.parent.primaryChannel is defined', $formThemeSource);
        self::assertStringContainsString('editor_primary_channel_value|integrated_channel', $formThemeSource);
        self::assertStringContainsString('editor_primary_channel_value.id is defined', $formThemeSource);
        self::assertStringContainsString('option_channel_value.id is defined', $contentFormSource);
    }
}
