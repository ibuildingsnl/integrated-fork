<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class FormTypeChoiceIntegrationTest extends TestCase
{
    public function testContentChoiceScriptUsesScopedInitializerAndSelector(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/public/js/content_choice.js');

        $this->assertIsString($script);
        $this->assertStringContainsString('function initIntegratedContentChoice($)', $script);
        $this->assertStringContainsString('select.integrated_content_choice:not(.integrated_content_parent_choice)', $script);
        $this->assertStringContainsString("if (\$element.data('select2')) {", $script);
        $this->assertStringNotContainsString('function initContentChoice(', $script);
    }

    public function testParentChoiceScriptUsesDedicatedSelectorAndAjaxUrl(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/public/js/parent_choice.js');

        $this->assertIsString($script);
        $this->assertStringContainsString('function initIntegratedParentChoice($)', $script);
        $this->assertStringContainsString('select.integrated_content_parent_choice', $script);
        $this->assertStringContainsString("url: \$element.data('ajax-url')", $script);
        $this->assertStringNotContainsString('function initContentChoice(', $script);
    }

    public function testParentChoiceWidgetAddsDedicatedClass(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('integrated_content_parent_choice', $template);
    }

    public function testMediaControllerNormalizesContentTypesWithHelper(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/MediaController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('private function normalizeContentTypes(Request $request): void', $controller);
        $this->assertStringContainsString('$this->normalizeContentTypes($request);', $controller);
        $this->assertStringNotContainsString('if ($contentType = $request->query->get(\'contenttypes\')) {', $controller);
    }
}
