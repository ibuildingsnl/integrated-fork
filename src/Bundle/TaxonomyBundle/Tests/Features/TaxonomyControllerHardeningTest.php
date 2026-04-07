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
        $this->assertStringContainsString('new TaxonomyOptions($filter, $offset, $limit, false)', $controller);
        $this->assertStringContainsString('public function usageCounts(Request $request, string $type): Response', $controller);
        $this->assertStringContainsString('$ids = $request->query->all(\'ids\');', $controller);
        $this->assertStringContainsString("'counts' => \$this->taxonomies->countUsagesFor(\$taxonomyIds)", $controller);
    }

    public function testIndexTemplateOnlyBuildsDeleteRouteForPersistedContent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/index/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% if content is defined and content.id is not empty %}', $template);
        $this->assertStringContainsString("content.published in [true, 'true', 1, '1']", $template);
        $this->assertStringNotContainsString("content.published == 'true'", $template);
        $this->assertStringContainsString('data-taxonomy-usage-url="{{ usage_count_url }}"', $template);
        $this->assertStringContainsString('data-taxonomy-usage-count-id="{{ item.taxonomyId }}"', $template);
        $this->assertStringContainsString('fetch(usageUrl + \'?\' + params.toString()', $template);
        $this->assertStringContainsString('window.addEventListener(\'turbo:load\', initializeTaxonomyUsageCounts);', $template);
        $this->assertStringContainsString("window.sessionStorage.getItem('integrated.taxonomy.usage-counts')", $template);
        $this->assertStringContainsString("window.sessionStorage.setItem('integrated.taxonomy.usage-counts'", $template);
    }

    public function testTaxonomyRoutingExposesLazyUsageCountEndpoint(): void
    {
        $xmlRoutes = file_get_contents(__DIR__.'/../../Resources/config/routing.xml');
        $yamlRoutes = file_get_contents(__DIR__.'/../../Resources/config/routing.yaml');

        $this->assertIsString($xmlRoutes);
        $this->assertIsString($yamlRoutes);
        $this->assertStringContainsString('route id="integrated_taxonomy_usage_counts"', $xmlRoutes);
        $this->assertStringContainsString('path="/taxonomy/{type}/usage-counts"', $xmlRoutes);
        $this->assertStringContainsString('integrated_taxonomy_usage_counts:', $yamlRoutes);
        $this->assertStringContainsString("path: '/taxonomy/{type}/usage-counts'", $yamlRoutes);
    }
}
