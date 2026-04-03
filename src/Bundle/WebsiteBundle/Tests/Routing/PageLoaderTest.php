<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Routing;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\WebsiteBundle\Routing\PageLoader;
use PHPUnit\Framework\TestCase;

class PageLoaderTest extends TestCase
{
    public function testLoadIncludesDisabledPagesInRouteCollection(): void
    {
        $page = new Page();
        $page->setPath('/draft-page');
        $page->setLayout('default.html.twig');
        $page->setDisabled(true);
        $this->setDocumentId($page, 'draft-page-id');

        $loader = new class($this->createMock(DocumentManager::class), [$page]) extends PageLoader {
            /** @var array<int, Page> */
            private array $pages;

            /** @param array<int, Page> $pages */
            public function __construct(DocumentManager $dm, array $pages)
            {
                parent::__construct($dm);
                $this->pages = $pages;
            }

            protected function getPages(): iterable
            {
                return $this->pages;
            }
        };

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
