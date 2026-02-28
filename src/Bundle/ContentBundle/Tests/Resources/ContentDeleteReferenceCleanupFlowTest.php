<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentDeleteReferenceCleanupFlowTest extends TestCase
{
    public function testDeleteControllerSupportsExplicitReferenceCleanupOption(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('$form->add(\'removeReferences\'', $controller);
        $this->assertStringContainsString('$removeReferences = $form->has(\'removeReferences\') && (bool) $form->get(\'removeReferences\')->getData();', $controller);
        $this->assertStringContainsString('if (\count($referenced) > 0 && !$removeReferences) {', $controller);
        $this->assertStringContainsString('$this->removeContentReferences($content);', $controller);
        $this->assertStringContainsString('private function removeContentReferences(Content $content): void', $controller);
    }
}
