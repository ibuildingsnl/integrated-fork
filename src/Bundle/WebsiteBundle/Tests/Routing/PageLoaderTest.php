<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Routing;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\WebsiteBundle\Routing\PageLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageLoaderTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    /** @var DocumentRepository<Page>&MockObject */
    private DocumentRepository $repository;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->repository = $this->createMock(DocumentRepository::class);
    }

    public function testLoadIncludesDisabledPagesInRouteCollection(): void
    {
        $page = new Page();
        $page->setPath('/draft-page');
        $page->setLayout('default.html.twig');
        $page->setDisabled(true);
        $this->setDocumentId($page, 'draft-page-id');

        $this->repository->expects($this->never())->method('findBy');
        $this->repository->expects($this->once())->method('findAll')->willReturn([$page]);
        $this->documentManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Page::class)
            ->willReturn($this->repository);

        $loader = new PageLoader($this->documentManager);
        $routes = $loader->load('.', 'integrated_website_page');
        $route = $routes->get(PageLoader::ROUTE_PREFIX.'draft-page-id');

        self::assertNotNull($route);
        self::assertSame('/draft-page', $route->getPath());
        self::assertSame('draft-page-id', $route->getDefault('page'));
    }

    private function setDocumentId(Page $page, string $id): void
    {
        $reflection = new \ReflectionObject($page);
        while ($reflection && !$reflection->hasProperty('id')) {
            $reflection = $reflection->getParentClass();
        }

        self::assertNotFalse($reflection);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($page, $id);
    }
}
