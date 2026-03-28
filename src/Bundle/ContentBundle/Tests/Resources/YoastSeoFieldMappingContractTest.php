<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class YoastSeoFieldMappingContractTest extends TestCase
{
    public function testInitializerUsesConfiguredFieldSelectors(): void
    {
        $source = file_get_contents(__DIR__.'/../../YoastSeo/src/index.jsx');

        $this->assertIsString($source);
        $this->assertStringContainsString('configuration.fieldSelectors', $source);
        $this->assertStringContainsString("import 'draft-js-mention-plugin/lib/plugin.css';", $source);
        $this->assertStringContainsString("import './seo-editor.css';", $source);
        $this->assertStringContainsString("const selectField = (key) => fieldSelectors[key] ? document.querySelector(fieldSelectors[key]) : null;", $source);
        $this->assertStringNotContainsString('#integrated_content_title', $source);
    }

    public function testYoastHooksAvoidContentOnlySelectors(): void
    {
        $pageContent = file_get_contents(__DIR__.'/../../YoastSeo/src/hooks/usePageContent.js');
        $analysis = file_get_contents(__DIR__.'/../../YoastSeo/src/hooks/useAnalysis.js');
        $app = file_get_contents(__DIR__.'/../../YoastSeo/src/components/IntegratedYoastApp.jsx');
        $seoTab = file_get_contents(__DIR__.'/../../YoastSeo/src/components/SeoTab.jsx');
        $integratedFields = file_get_contents(__DIR__.'/../../YoastSeo/src/hooks/useIntegratedFields.js');
        $replacementEditorWrapper = file_get_contents(__DIR__.'/../../YoastSeo/node_modules/@yoast/replacement-variable-editor/ReplacementVariableEditor.js');
        $replacementEditor = file_get_contents(__DIR__.'/../../YoastSeo/node_modules/@yoast/replacement-variable-editor/ReplacementVariableEditorStandalone.js');
        $replacementEditorShared = file_get_contents(__DIR__.'/../../YoastSeo/node_modules/@yoast/replacement-variable-editor/shared.js');

        $this->assertIsString($pageContent);
        $this->assertIsString($analysis);
        $this->assertIsString($app);
        $this->assertIsString($seoTab);
        $this->assertIsString($integratedFields);
        $this->assertIsString($replacementEditorWrapper);
        $this->assertIsString($replacementEditor);
        $this->assertIsString($replacementEditorShared);

        $this->assertStringContainsString('editorFieldMapping.title', $pageContent);
        $this->assertStringContainsString('configuration.analysisContentUrl', $pageContent);
        $this->assertStringContainsString('fetch(configuration.analysisContentUrl', $pageContent);
        $this->assertStringContainsString('resolveSeoPlaceholders', $pageContent);
        $this->assertStringContainsString('const hasExplicitTitleOverride = !!titleOverride.trim();', $pageContent);
        $this->assertStringContainsString("setTitleTemplate('{title}');", $pageContent);
        $this->assertStringContainsString('useMemo', $seoTab);
        $this->assertStringContainsString('const replacementVariables = useMemo(', $seoTab);
        $this->assertStringContainsString('mapSeoPlaceholdersToReplacementVariables', $seoTab);
        $this->assertStringContainsString('replacementVariables={replacementVariables}', $seoTab);
        $this->assertStringContainsString('editorFieldMapping[key].value = data', $integratedFields);
        $this->assertStringContainsString('const debouncedLoadPageContent = useRef(debounce(() => loadPageContent(), 500)).current;', $integratedFields);
        $this->assertStringNotContainsString('debouncedUpdateIntegratedFields', $integratedFields);
        $this->assertStringContainsString('isVariablePickerOpen: false', $replacementEditorWrapper);
        $this->assertStringContainsString('yoast-replacement-variable-pill-list', $replacementEditorWrapper);
        $this->assertStringContainsString('onMouseDown={ event => this.handleVariableInsertMouseDown( event, variable.name ) }', $replacementEditorWrapper);
        $this->assertStringContainsString('this.ref.insertReplacementVariable( variableName );', $replacementEditorWrapper);
        $this->assertStringContainsString('white-space: nowrap;', $replacementEditorShared);
        $this->assertStringContainsString('min-width: 152px;', $replacementEditorShared);
        $this->assertStringContainsString('display: inline-flex;', $replacementEditorShared);
        $this->assertStringContainsString('EditorState.forceSelection', $replacementEditor);
        $this->assertStringContainsString('hasFocus: true', $replacementEditor);
        $this->assertStringContainsString('insertReplacementVariable( variableName )', $replacementEditor);
        $this->assertStringContainsString('serializeVariable( variableName )', $replacementEditor);
        $this->assertStringContainsString('suggestionsEnabled: false', $replacementEditor);
        $this->assertStringContainsString('suggestions={ visibleSuggestions }', $replacementEditor);
        $this->assertStringContainsString('onClose={ this.disableSuggestions }', $replacementEditor);
        $this->assertStringContainsString('editorFieldMapping.readabilityScore', $analysis);
        $this->assertStringContainsString('editorFieldMapping.content', $app);
        $this->assertStringContainsString('let shouldLinkInputs = !!titleInput && !!titleOverrideInput && titleInput.value === titleOverrideInput.value;', $app);
        $this->assertStringNotContainsString('let shouldLinkInputs = true;', $app);
        $this->assertStringNotContainsString('yoast-seo-placeholder-toolbar', $seoTab);
        $this->assertStringNotContainsString('insertPlaceholder', $seoTab);
        $this->assertStringContainsString('url: configuration.pageUrl,', $app);
        $this->assertStringNotContainsString('configuration.pageUrl + configuration.pageUrl', $app);
        $this->assertStringNotContainsString('#integrated_content_title', $pageContent);
        $this->assertStringNotContainsString('#integrated_content_seoMetadata', $analysis);
        $this->assertStringNotContainsString('#integrated_content_content', $app);
    }
}
