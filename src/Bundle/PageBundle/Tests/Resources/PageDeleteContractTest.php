<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageDeleteContractTest extends TestCase
{
    public function testDeleteActionAcceptsAbstractPageSoContentTypePagesCanBeRemoved(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('use Integrated\\Bundle\\PageBundle\\Document\\Page\\AbstractPage;', $controller);
        $this->assertStringContainsString('public function delete(Request $request, AbstractPage $page): Response', $controller);
    }
}
