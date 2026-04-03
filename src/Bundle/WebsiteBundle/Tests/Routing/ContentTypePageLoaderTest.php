<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Routing;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContentTypePageLoaderTest extends TestCase
{
    public function testLoadBuildsRoutesFromLightweightQueryResults(): void
    {
        $channel = new Channel();
        $channel->setId('bakkersinbedrijf');

        /** @var ContentTypePage&MockObject $page */
        $page = $this->getMockBuilder(ContentTypePage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getControllerService', 'getControllerAction', 'getChannel'])
            ->getMock();
        $page
            ->expects($this->exactly(2))
            ->method('getControllerService')
            ->willReturn('App\\Controller\\NewsController');
        $page
            ->expects($this->exactly(2))
            ->method('getControllerAction')
            ->willReturn('show');
        $page
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        /** @var UrlResolver&MockObject $urlResolver */
        $urlResolver = $this->createMock(UrlResolver::class);

        $urlResolver
            ->expects($this->once())
            ->method('getRoutePath')
            ->with($page)
            ->willReturn('/artikelen/{slug}');
        $urlResolver
            ->expects($this->once())
            ->method('getRouteName')
            ->with($page)
            ->willReturn(ContentTypePageLoader::ROUTE_PREFIX.'_news_page');

        $loader = new class($this->createMock(DocumentManager::class), $urlResolver, [$page]) extends ContentTypePageLoader {
            /** @var array<int, ContentTypePage> */
            private array $pages;

            /** @param array<int, ContentTypePage> $pages */
            public function __construct(DocumentManager $dm, UrlResolver $urlResolver, array $pages)
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
        /** @var ContentTypePage&MockObject $page */
        $page = $this->getMockBuilder(ContentTypePage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getControllerService'])
            ->getMock();
        $page
            ->expects($this->once())
            ->method('getControllerService')
            ->willReturn('');

        /** @var UrlResolver&MockObject $urlResolver */
        $urlResolver = $this->createMock(UrlResolver::class);

        $urlResolver->expects($this->never())->method('getRoutePath');
        $urlResolver->expects($this->never())->method('getRouteName');

        $loader = new class($this->createMock(DocumentManager::class), $urlResolver, [$page]) extends ContentTypePageLoader {
            /** @var array<int, ContentTypePage> */
            private array $pages;

            /** @param array<int, ContentTypePage> $pages */
            public function __construct(DocumentManager $dm, UrlResolver $urlResolver, array $pages)
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
