<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageCopyTemplateTest extends TestCase
{
    public function testFormThemeRendersExpandablePagePanels(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('data-page-copy-panel-list', $template);
        $this->assertStringContainsString('data-page-copy-panel', $template);
        $this->assertStringContainsString('data-page-copy-panel-header', $template);
        $this->assertStringContainsString('data-page-copy-panel-body', $template);
        $this->assertStringContainsString('data-page-copy-toggle', $template);
        $this->assertStringNotContainsString('id="copy-table"', $template);
    }

    public function testCopyTemplateRendersSummaryAndActionStateMarkup(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');
        $formTheme = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertIsString($formTheme);
        $this->assertStringContainsString('data-page-copy-summary', $template);
        $this->assertStringContainsString('overwriteCount', $template);
        $this->assertStringContainsString('data-page-copy-action', $formTheme);
        $this->assertStringContainsString('{% if copyAction == \'overwrite\' %}', $formTheme);
        $this->assertStringContainsString('{% trans %}Overwrite{% endtrans %}', $formTheme);
        $this->assertStringContainsString('{% trans %}Create{% endtrans %}', $formTheme);
    }

    public function testFormThemeAddsCloneVisibilityHooksForBlockRows(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('data-page-copy-block-row', $template);
        $this->assertStringContainsString('data-page-copy-block-operation', $template);
        $this->assertStringContainsString('data-page-copy-new-block-id-container', $template);
        $this->assertStringContainsString("{% set isCloneOperation = form.operation.vars.data|default('') == 'clone' %}", $template);
    }

    public function testTemplatesExposeExpandedStateAndInitializePanelInteractions(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');
        $pageTemplate = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($template);
        $this->assertIsString($pageTemplate);
        $this->assertStringContainsString('{% set isExpanded = false %}', $template);
        $this->assertStringContainsString('data-page-copy-expanded="{{ isExpanded ? \'true\' : \'false\' }}"', $template);
        $this->assertStringContainsString('initializePageCopyPanels', $pageTemplate);
        $this->assertStringContainsString("document.querySelectorAll('[data-page-copy-panel]')", $pageTemplate);
        $this->assertStringContainsString("document.addEventListener('DOMContentLoaded', initializePageCopyPanels);", $pageTemplate);
    }
}
