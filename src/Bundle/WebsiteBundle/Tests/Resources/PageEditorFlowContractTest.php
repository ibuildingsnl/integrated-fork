<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageEditorFlowContractTest extends TestCase
{
    public function testRoutingDoesNotImportPageDraftRoutes(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing.xml');

        $this->assertIsString($routing);
        $this->assertStringNotContainsString('routing/page_draft.xml', $routing);
    }
}
