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
        $this->assertStringNotContainsString('data-page-copy-toggle', $template);
        $this->assertStringNotContainsString('id="copy-table"', $template);
    }

    public function testCopyTemplateRendersSummaryAndActionStateMarkup(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');
        $formTheme = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertIsString($formTheme);
        $this->assertStringContainsString('data-page-copy-summary', $template);
        $this->assertStringContainsString('{{ form_errors(form) }}', $template);
        $this->assertStringContainsString('queue-summary', $template);
        $this->assertStringContainsString('overwriteCount', $template);
        $this->assertStringContainsString('data-page-copy-action', $formTheme);
        $this->assertStringContainsString('currentCopyAction', $formTheme);
        $this->assertStringContainsString('overwriteAvailable', $formTheme);
        $this->assertStringContainsString('page-copy-action-selector', $formTheme);
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
        $this->assertStringContainsString('syncPageCopyPanelSelectedState', $pageTemplate);
        $this->assertStringContainsString("panel.setAttribute('data-page-copy-selected', selected ? 'true' : 'false');", $pageTemplate);
        $this->assertStringContainsString("select.addEventListener('change', function()", $pageTemplate);
        $this->assertStringContainsString("event.target.matches('[data-page-copy-select]')", $pageTemplate);
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
        $this->assertStringContainsString('data-page-copy-check-all', $pageTemplate);
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
        $this->assertStringContainsString('applyPageCopySearchReplace', $pageTemplate);
        $this->assertStringContainsString('syncPageCopyCloneAllButtonState', $pageTemplate);
        $this->assertStringContainsString('cloneAllPageCopyBlocks', $pageTemplate);
        $this->assertStringContainsString('setAllPageCopySelections', $pageTemplate);
        $this->assertStringContainsString('normalizePageCopyCloneInputs', $pageTemplate);
        $this->assertStringContainsString('input.dataset.proposedBlockId', $pageTemplate);
        $this->assertStringContainsString('data-page-copy-clone-all', $pageTemplate);
        $this->assertStringContainsString('data-page-copy-check-all', $pageTemplate);
        $this->assertStringContainsString("event.target.closest('[data-page-copy-clone-all]')", $pageTemplate);
        $this->assertStringContainsString("event.target.matches('[data-page-copy-check-all]')", $pageTemplate);
        $this->assertStringContainsString("event.target.closest('[data-page-copy-apply-replace]')", $pageTemplate);
        $this->assertStringContainsString("pageCopyForm.addEventListener('submit'", $pageTemplate);
    }

    public function testTemplatesResetPanelSelectionStateWhenDeselected(): void
    {
        $pageTemplate = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($pageTemplate);
        $this->assertStringContainsString('pageCopyPanelHasPersistentExpandedState', $pageTemplate);
        $this->assertStringContainsString('if (!pageCopyPanelHasPersistentExpandedState(panel)) {', $pageTemplate);
        $this->assertStringContainsString('syncPageCopyPanelExpandedState(panel, false);', $pageTemplate);
    }

    public function testPageCopyTypeUsesDataHooksInsteadOfInlineRefreshSubmit(): void
    {
        $formType = file_get_contents(__DIR__.'/../../Form/Type/PageCopyType.php');
        $pageFormType = file_get_contents(__DIR__.'/../../Form/Type/PageCopyPageType.php');
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($formType);
        $this->assertIsString($pageFormType);
        $this->assertIsString($template);
        $this->assertStringNotContainsString('document.page_copy.submit();', $formType);
        $this->assertStringContainsString('data-page-copy-source-channel', $formType);
        $this->assertStringContainsString('data-page-copy-target-channel', $formType);
        $this->assertStringContainsString('data-page-copy-overwrite-toggle', $pageFormType);
        $this->assertStringContainsString('allowOverwrite', $pageFormType);
        $this->assertStringContainsString('CheckboxSwitcherType::class', $pageFormType);
        $this->assertStringContainsString('submitPageCopyRefresh', $template);
        $this->assertStringContainsString("querySelector('select[data-page-copy-source-channel]')", $template);
        $this->assertStringContainsString("querySelector('select[data-page-copy-target-channel]')", $template);
        $this->assertStringContainsString("window.Turbo.visit(refreshUrl.toString(), { action: 'replace' });", $template);
        $this->assertStringContainsString("refreshUrl.searchParams.set('page_copy[sourceChannel]', sourceChannelField.value);", $template);
        $this->assertStringContainsString("refreshUrl.searchParams.set('page_copy[targetChannel]', targetChannelField.value);", $template);
    }

    public function testCopyTemplateExplicitlyRendersRemainingHiddenFields(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{{ form_rest(form) }}', $template);
        $this->assertStringContainsString('{{ form_end(form, { render_rest: false }) }}', $template);
        $this->assertStringNotContainsString("{{ form_row(form.actions, {'style':'horizontal', 'state': 'show'}) }}", $template);
    }
}
