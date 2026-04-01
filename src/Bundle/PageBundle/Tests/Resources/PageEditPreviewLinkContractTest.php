<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageEditPreviewLinkContractTest extends TestCase
{
    public function testEditActionProvidesPreviewLinkForTemplate(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString("'previewLink' => \$this->buildPreviewLink(\$page, \$request)", $controller);
        $this->assertStringContainsString('private function buildPreviewLink(Page $page, Request $request): ?string', $controller);
    }
}
