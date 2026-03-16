<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentEditorTinyMceToolbarContractTest extends TestCase
{
    public function testTinyMceToolbarHeaderUsesResponsiveFixedWidths(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/sass/components/_tinymce.scss');

        self::assertIsString($source);
        self::assertStringContainsString('.hide-options .fancy_tinymce .tox:not(.tox-tinymce-inline) .tox-editor-header {', $source);
        self::assertStringContainsString('width: calc(100% - 250px);', $source);
        self::assertStringContainsString('.show-options .fancy_tinymce .tox:not(.tox-tinymce-inline) .tox-editor-header {', $source);
        self::assertStringContainsString('width: calc(100% - 600px);', $source);
        self::assertStringContainsString('.fancy_tinymce .tox:not(.tox-tinymce-inline) .tox-editor-header {', $source);
        self::assertStringContainsString('position: fixed;', $source);
        self::assertStringContainsString('width: calc(100% - 600px);', $source);
    }

    public function testTinyMceEditAreaNoLongerUsesFixedHeaderOffset(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/sass/components/_tinymce.scss');

        self::assertIsString($source);
        self::assertStringContainsString('.tox-edit-area {', $source);
        self::assertStringContainsString('margin-top: 82px;', $source);
        self::assertStringNotContainsString('top: 96px;', $source);
    }

    public function testTinyMceEditorClosesToolbarOverflowOnOutsideClick(): void
    {
        $source = file_get_contents(__DIR__.'/../../../FormTypeBundle/Resources/assets/js/editor.js');

        self::assertIsString($source);
        self::assertStringContainsString('function closeTinyMceToolbarOverflow()', $source);
        self::assertStringContainsString('function findTinyMceToolbarOverflowToggle(controlId = null)', $source);
        self::assertStringContainsString('function resetTinyMceToolbarOverflowToggle(controlId = null)', $source);
        self::assertStringContainsString('function bindTinyMceToolbarOverflowClose()', $source);
        self::assertStringContainsString("document.querySelector('.tox-tinymce-aux .tox-toolbar__overflow');", $source);
        self::assertStringContainsString("target.closest('.tox-tinymce-aux .tox-toolbar__overflow')", $source);
        self::assertStringContainsString('const toggle = findTinyMceToolbarOverflowToggle(', $source);
        self::assertStringContainsString('toggle.click();', $source);
        self::assertStringContainsString("button.setAttribute('aria-expanded', 'false');", $source);
        self::assertStringContainsString("button.removeAttribute('aria-controls');", $source);
        self::assertStringContainsString("button.classList.remove('tox-tbtn--enabled');", $source);
        self::assertStringContainsString("editor.contentDocument.addEventListener('mousedown', function()", $source);
        self::assertStringContainsString("editor.contentDocument.body.dataset.boundTinyMceToolbarOverflowClose = 'true';", $source);
        self::assertStringContainsString("editor.on('blur', function()", $source);
        self::assertStringContainsString('bindTinyMceToolbarOverflowClose();', $source);
        self::assertStringContainsString('closeTinyMceToolbarOverflow();', $source);
        self::assertStringContainsString("document.body.dataset.boundTinyMceToolbarOverflowClose = 'true';", $source);
    }

    public function testTinyMceNormalizesCmsAnchorStylesAsLinkSelectors(): void
    {
        $editorSource = file_get_contents(__DIR__.'/../../../FormTypeBundle/Resources/assets/js/editor.js');
        $collectionSource = file_get_contents(__DIR__.'/../../Resources/assets/js/collection.js');
        $templateSource = file_get_contents(__DIR__.'/../../../FormTypeBundle/Resources/views/form/form_div_layout.html.twig');

        self::assertIsString($editorSource);
        self::assertIsString($collectionSource);
        self::assertIsString($templateSource);

        self::assertStringContainsString('function normalizeTinyMceStyleFormats(styles = [])', $editorSource);
        self::assertStringContainsString("if (newStyle.inline === 'a' && !newStyle.selector) {", $editorSource);
        self::assertStringContainsString("newStyle.selector = 'a';", $editorSource);
        self::assertStringContainsString('delete newStyle.inline;', $editorSource);
        self::assertStringContainsString('style_formats = style_formats.concat(normalizeTinyMceStyleFormats(custom_styles));', $editorSource);
        self::assertStringContainsString("content_style: element.data('content_style'),", $editorSource);

        self::assertStringContainsString('function normalizeTinyMceStyleFormats(styles = [])', $collectionSource);
        self::assertStringContainsString("if (newStyle.inline === 'a' && !newStyle.selector) {", $collectionSource);
        self::assertStringContainsString("newStyle.selector = 'a';", $collectionSource);
        self::assertStringContainsString('delete newStyle.inline;', $collectionSource);
        self::assertStringContainsString('style_formats = style_formats.concat(normalizeTinyMceStyleFormats(editor.data(\'format_styles\') || []));', $collectionSource);
        self::assertStringContainsString("content_style: editor.data('content_style'),", $collectionSource);

        self::assertStringContainsString("{% set channel_brand_profile = _channel is defined and _channel ? (_channel|integrated_brand_profile) : null %}", $templateSource);
        self::assertStringContainsString("{% set content_style = ':root{--td-color-accent:' ~ channel_brand_color ~ ';--td-color-accent-secondary:' ~ channel_brand_secondary_color ~ ';--td-color-accent-dark:' ~ channel_brand_color_dark ~ ';--td-color-accent-secondary-dark:' ~ channel_brand_secondary_color_dark ~ ';}' %}", $templateSource);
        self::assertStringContainsString('data-content_style="{{ content_style }}"', $templateSource);
    }
}
