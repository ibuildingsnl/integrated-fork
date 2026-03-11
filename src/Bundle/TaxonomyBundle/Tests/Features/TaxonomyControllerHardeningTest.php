<?php

declare(strict_types=1);

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use PHPUnit\Framework\TestCase;

final class TaxonomyControllerHardeningTest extends TestCase
{
    public function testIndexControllerGuardsSessionAndNormalizesFilter(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/IndexController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('$request->hasSession() && !$request->query->getBoolean(\'remember\')', $controller);
        $this->assertStringContainsString('private function resolveFilter(Request $request): string', $controller);
        $this->assertStringContainsString('if (!\\is_scalar($filter)) {', $controller);
        $this->assertStringContainsString('return \'root\';', $controller);
        $this->assertStringContainsString('$current = $request->query->get(\'current\');', $controller);
        $this->assertStringContainsString('$currentId = \is_scalar($current) ? trim((string) $current) : \'\';', $controller);
        $this->assertStringContainsString('ActionsType::class', $controller);
        $this->assertStringContainsString('if ($wasPersisted) {', $controller);
        $this->assertStringContainsString('$params[\'current\'] = (string) $content->getId();', $controller);
        $this->assertStringContainsString('unset($params[\'current\']);', $controller);
        $this->assertStringContainsString("'content' => \$content,", $controller);
        $this->assertStringContainsString('if ($form->isSubmitted() && $form->isValid() && $content instanceof Taxonomy) {', $controller);
        $this->assertStringNotContainsString('return $this->redirectToRoute(\'integrated_content_content_index\');', $controller);
    }

    public function testIndexTemplateOnlyBuildsDeleteRouteForPersistedContent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/index/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% if content is defined and content.id is not empty %}', $template);
        $this->assertStringContainsString("content.published in [true, 'true', 1, '1']", $template);
        $this->assertStringNotContainsString("content.published == 'true'", $template);
    }

    public function testIndexTemplateUsesAdminComponentsForListingShell(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/index/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("component('integrated_admin:page_title'", $template);
        $this->assertStringContainsString("component('integrated_admin:options_toolbar'", $template);
        $this->assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        $this->assertStringContainsString("component('integrated_admin:data_table'", $template);
        $this->assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
        $this->assertStringContainsString("component('integrated_admin:row_actions'", $template);
    }

    public function testIndexTemplateUsesAdminPageTitleForEditorPane(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/index/index.html.twig');

        $this->assertIsString($template);
        $this->assertGreaterThanOrEqual(2, substr_count($template, "component('integrated_admin:page_title'"));
    }

    public function testIndexTemplateUsesAdminAsidePanelsForEditorSidebar(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/index/index.html.twig');

        $this->assertIsString($template);
        $this->assertGreaterThanOrEqual(2, substr_count($template, "component('integrated_admin:aside_panel'"));
        $this->assertStringContainsString("title: 'Status'|trans", $template);
        $this->assertStringContainsString("title: 'Content Options'|trans", $template);
    }
}
