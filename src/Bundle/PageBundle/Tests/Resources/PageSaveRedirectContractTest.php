<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageSaveRedirectContractTest extends TestCase
{
    public function testNewActionRedirectsToEditPageAfterSave(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString("return \$this->redirectToRoute('integrated_page_page_edit', ['id' => \$page->getId()]);", $controller);
    }

    public function testEditActionRedirectsBackToEditPageAfterSave(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($controller);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count($controller, "return \$this->redirectToRoute('integrated_page_page_edit', ['id' => \$page->getId()]);")
        );
    }
}
