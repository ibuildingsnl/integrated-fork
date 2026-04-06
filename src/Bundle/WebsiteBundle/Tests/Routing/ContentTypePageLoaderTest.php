<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Routing;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use PHPUnit\Framework\TestCase;

final class ContentTypePageLoaderTest extends TestCase
{
    public function testLoadBuildsRoutesFromLightweightQueryResults(): void
    {
        $urlResolver = $this->createStub(\Integrated\Bundle\PageBundle\Services\UrlResolver::class);
        $page = [
            '_id' => 'news_page',
            'path' => '/artikelen/#slug#',
            'controllerService' => 'App\\Controller\\NewsController',
            'controllerAction' => 'show',
            'channel' => ['$id' => 'bakkersinbedrijf'],
        ];

        $loader = new class($this->createMock(DocumentManager::class), $urlResolver, [$page]) extends ContentTypePageLoader {
            /** @var array<int, array<string, mixed>> */
            private array $pages;

            /** @param array<int, array<string, mixed>> $pages */
            public function __construct(DocumentManager $dm, \Integrated\Bundle\PageBundle\Services\UrlResolver $urlResolver, array $pages)
            {
                parent::__construct($dm, $urlResolver);
                $this->pages = $pages;
            }

            protected function getPages(): iterable
            {
                return $this->pages;
            }
        };

        $routes = $loader->load('.', ContentTypePageLoader::ROUTE_PREFIX);
        $route = $routes->get(ContentTypePageLoader::ROUTE_PREFIX.'_news_page');

        self::assertNotNull($route);
        self::assertSame('/artikelen/{slug}', $route->getPath());
        self::assertSame('App\\Controller\\NewsController::show', $route->getDefault('_controller'));
        self::assertSame('request.attributes.get("_channel") == "bakkersinbedrijf"', $route->getCondition());
    }

    public function testLoadSkipsPagesWithoutControllerService(): void
    {
        $urlResolver = $this->createStub(\Integrated\Bundle\PageBundle\Services\UrlResolver::class);
        $page = [
            '_id' => 'empty',
            'path' => '/artikelen/#slug#',
            'controllerService' => '',
            'controllerAction' => 'show',
            'channel' => ['$id' => 'bakkersinbedrijf'],
        ];

        $loader = new class($this->createMock(DocumentManager::class), $urlResolver, [$page]) extends ContentTypePageLoader {
            /** @var array<int, array<string, mixed>> */
            private array $pages;

            /** @param array<int, array<string, mixed>> $pages */
            public function __construct(DocumentManager $dm, \Integrated\Bundle\PageBundle\Services\UrlResolver $urlResolver, array $pages)
            {
                parent::__construct($dm, $urlResolver);
                $this->pages = $pages;
            }

            protected function getPages(): iterable
            {
                return $this->pages;
            }
        };

        $routes = $loader->load('.', ContentTypePageLoader::ROUTE_PREFIX);

        self::assertCount(0, $routes->all());
    }
}
