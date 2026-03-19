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
        $this->assertStringContainsString('queue-summary', $template);
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

    public function testTemplatesSyncSelectedCheckboxStateWithPagePanels(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');
        $pageTemplate = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($template);
        $this->assertIsString($pageTemplate);
        $this->assertStringContainsString('data-page-copy-select', $template);
        $this->assertStringContainsString('data-page-copy-selected', $template);
        $this->assertStringContainsString('checkbox-switcher', $template);
        $this->assertStringContainsString('switch-input', $template);
        $this->assertStringContainsString('syncSelectedState', $pageTemplate);
        $this->assertStringContainsString("panel.setAttribute('data-page-copy-selected', selected ? 'true' : 'false');", $pageTemplate);
        $this->assertStringContainsString("select.addEventListener('change', function()", $pageTemplate);
    }

    public function testFormThemeOnlyShowsBlocksToggleWhenPageHasBlocks(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% if blockCount > 0 %}', $template);
        $this->assertStringContainsString('data-page-copy-no-blocks', $template);
        $this->assertStringContainsString('{% trans %}No blocks{% endtrans %}', $template);
    }

    public function testTemplatesRenderBulkCloneAndSearchReplaceControls(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');
        $pageTemplate = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($template);
        $this->assertIsString($pageTemplate);
        $this->assertStringContainsString('data-page-copy-bulk-tools', $pageTemplate);
        $this->assertStringContainsString('form-actions form-actions--table-filters', $pageTemplate);
        $this->assertStringContainsString('data-page-copy-search', $pageTemplate);
        $this->assertStringContainsString('data-page-copy-replace', $pageTemplate);
        $this->assertStringContainsString('data-page-copy-apply-replace', $pageTemplate);
        $this->assertStringContainsString('data-page-copy-clone-all', $template);
    }

    public function testCopyTemplateRendersChannelFiltersInsideToolbar(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('data-page-copy-channel-filters', $template);
        $this->assertStringContainsString('data-page-copy-source-channel', $template);
        $this->assertStringContainsString('data-page-copy-target-channel', $template);
        $this->assertStringContainsString('form.sourceChannel is defined', $template);
        $this->assertStringContainsString('form.targetChannel is defined', $template);
    }

    public function testTemplatesExposeBulkInteractionHooks(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');
        $pageTemplate = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($template);
        $this->assertIsString($pageTemplate);
        $this->assertStringContainsString('data-page-copy-new-block-id', $template);
        $this->assertStringContainsString('applySearchReplace', $pageTemplate);
        $this->assertStringContainsString('syncCloneAllState', $pageTemplate);
        $this->assertStringContainsString('input.dataset.proposedBlockId', $pageTemplate);
        $this->assertStringContainsString('data-page-copy-clone-all', $pageTemplate);
    }

    public function testTemplatesResetPanelSelectionStateWhenDeselected(): void
    {
        $pageTemplate = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($pageTemplate);
        $this->assertStringContainsString('hasPersistentExpandedState', $pageTemplate);
        $this->assertStringContainsString('if (!selected && !hasPersistentExpandedState()) {', $pageTemplate);
        $this->assertStringContainsString("syncExpandedState(false);", $pageTemplate);
    }

    public function testPageCopyTypeUsesDataHooksInsteadOfInlineRefreshSubmit(): void
    {
        $formType = file_get_contents(__DIR__.'/../../Form/Type/PageCopyType.php');
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($formType);
        $this->assertIsString($template);
        $this->assertStringNotContainsString("document.page_copy.submit();", $formType);
        $this->assertStringContainsString('data-page-copy-source-channel', $formType);
        $this->assertStringContainsString('data-page-copy-target-channel', $formType);
        $this->assertStringContainsString('submitPageCopyRefresh', $template);
        $this->assertStringContainsString("actionField.value = 'refresh';", $template);
    }

    public function testCopyTemplateDisablesImplicitRenderRestAtFormEnd(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("{{ form_end(form, { render_rest: false }) }}", $template);
        $this->assertStringNotContainsString("{{ form_row(form.actions, {'style':'horizontal', 'state': 'show'}) }}", $template);
    }
}
