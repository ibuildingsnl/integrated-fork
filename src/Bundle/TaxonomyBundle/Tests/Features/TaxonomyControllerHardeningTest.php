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
        $this->assertStringContainsString('$filter = $request->query->all()[\'filter\'] ?? \'root\';', $controller);
        $this->assertStringContainsString('if (!\\is_scalar($filter)) {', $controller);
        $this->assertStringContainsString('return \'root\';', $controller);
        $this->assertStringContainsString('$currentId = trim((string) ($request->query->all()[\'current\'] ?? \'\'));', $controller);
        $this->assertStringContainsString('$form->add(\'actions\', ActionsType::class, [\'buttons\' => [$isPersisted ? \'save\' : \'create\']]);', $controller);
        $this->assertStringContainsString('$params[\'current\'] = (string) $content->getId();', $controller);
        $this->assertStringContainsString("'content' => \$content,", $controller);
        $this->assertStringContainsString('if ($form->isSubmitted() && $form->isValid() && $content instanceof Taxonomy) {', $controller);
        $this->assertStringNotContainsString('return $this->redirectToRoute(\'integrated_content_content_index\');', $controller);
    }
}
