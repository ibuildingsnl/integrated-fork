<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class SitemapRoutingTest extends TestCase
{
    public function testPagesRouteMatchesDedicatedController(): void
    {
        $parameters = $this->createMatcher()->match('/sitemap-pages-1.xml');

        self::assertSame('integrated_sitemap_list_pages', $parameters['_route']);
        self::assertStringContainsString('DefaultController::listPages', $parameters['_controller']);
        self::assertSame('1', (string) $parameters['page']);
    }

    public function testTypeRouteStillMatchesTypedSitemapPath(): void
    {
        $parameters = $this->createMatcher()->match('/sitemap-article-1.xml');

        self::assertSame('integrated_sitemap_list_by_type', $parameters['_route']);
        self::assertStringContainsString('DefaultController::listByType', $parameters['_controller']);
        self::assertSame('article', $parameters['type']);
        self::assertSame('1', (string) $parameters['page']);
    }

    public function testPagesRouteRejectsZeroPageNumber(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->createMatcher()->match('/sitemap-pages-0.xml');
    }

    private function createMatcher(): UrlMatcher
    {
        $loader = new XmlFileLoader(new FileLocator(__DIR__.'/../../Resources/config'));
        $routes = $loader->load('routing.xml');

        return new UrlMatcher($routes, new RequestContext('/'));
    }
}
