<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Grid\GridFactory;
use Integrated\Bundle\WebsiteBundle\Controller\GridController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class GridControllerLegacyLockdownTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    /** @var GridFactory&MockObject */
    private GridFactory $gridFactory;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->gridFactory = $this->createMock(GridFactory::class);
    }

    public function testLegacyGridSaveReturns410AfterV2Cutover(): void
    {
        $this->documentManager->expects($this->never())->method('flush');

        $controller = $this->createController();
        $response = $controller->save(Request::create('/grid/save', 'POST', [], [], [], [], '{"page":"page-id"}'));

        self::assertSame(410, $response->getStatusCode());
        self::assertStringContainsString('Legacy grid save disabled', (string) $response->getContent());
    }

    private function createController(): GridController
    {
        return new class($this->documentManager, $this->gridFactory) extends GridController {
            protected function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return true;
            }
        };
    }
}

