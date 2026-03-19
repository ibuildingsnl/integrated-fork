<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageCopyControllerFlowTest extends TestCase
{
    public function testCopyControllerMirrorsCopyErrorsToFlashMessages(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString("\$this->addFlash('warning', \$exception->getMessage());", $controller);
        $this->assertStringContainsString('$this->flashUniqueFormErrorsAsWarnings($form);', $controller);
        $this->assertStringContainsString('private function flashUniqueFormErrorsAsWarnings(FormInterface $form): void', $controller);
    }
}
