<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageContentTypeDuplicateDeleteContractTest extends TestCase
{
    public function testIndexControllerPassesDeletableContentTypePageIdsToTemplate(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString("'deletableContentTypePageIds' => \$this->getDeletableContentTypePageIds(\$pagination)", $controller);
        $this->assertStringContainsString('private function getDeletableContentTypePageIds(iterable $pages): array', $controller);
        $this->assertStringContainsString("->field('contentType.\$id')->equals(\$contentTypeId)", $controller);
    }
}
