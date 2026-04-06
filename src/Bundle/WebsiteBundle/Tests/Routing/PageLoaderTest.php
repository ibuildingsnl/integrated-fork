<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Routing;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\WebsiteBundle\Routing\PageLoader;
use PHPUnit\Framework\TestCase;

class PageLoaderTest extends TestCase
{
    public function testLoadIncludesDisabledPagesInRouteCollection(): void
    {
        $page = [
            '_id' => 'draft-page-id',
            'path' => '/draft-page',
            'channel' => ['$id' => 'channel-1'],
        ];

        $loader = new class($this->createMock(DocumentManager::class), [$page]) extends PageLoader {
            /** @var array<int, array<string, mixed>> */
            private array $pages;

            /** @param array<int, array<string, mixed>> $pages */
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
        self::assertSame('request.attributes.get("_channel") == "channel-1"', $route->getCondition());
    }
}
