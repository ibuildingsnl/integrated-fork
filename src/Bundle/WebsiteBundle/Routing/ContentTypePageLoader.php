<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Routing;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class ContentTypePageLoader extends Loader
{
    public const ROUTE_PREFIX = 'integrated_website_content_type_page';

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var UrlResolver
     */
    protected $urlResolver;

    public function __construct(DocumentManager $dm, UrlResolver $urlResolver)
    {
        $this->dm = $dm;
        $this->urlResolver = $urlResolver;
    }

    public function load(mixed $resource, $type = null): RouteCollection
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Page loader is already added');
        }

        $routes = new RouteCollection();

        $pages = $this->getPages();

        foreach ($pages as $page) {
            $controllerService = trim((string) ($page['controllerService'] ?? ''));
            if ($controllerService === '') {
                continue;
            }

            $pageId = trim((string) ($page['_id'] ?? ''));
            $channelId = $this->extractReferenceId($page['channel'] ?? null);

            $route = new Route(
                $this->getRoutePath((string) ($page['path'] ?? '')),
                ['_controller' => $this->getController($controllerService, (string) ($page['controllerAction'] ?? '')), 'page' => $pageId],
                [],
                [],
                '',
                [],
                [],
                $channelId !== null ? 'request.attributes.get("_channel") == "'.$channelId.'"' : ''
            );

            $routes->add($this->getRouteName($pageId), $route);
        }
        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null): bool
    {
        return self::ROUTE_PREFIX === $type;
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    protected function getPages(): iterable
    {
        return $this->dm->createQueryBuilder(ContentTypePage::class)
            ->select(['id', 'path', 'controllerService', 'controllerAction', 'channel'])
            ->hydrate(false)
            ->getQuery()
            ->getIterator();
    }

    private function getController(string $controllerService, string $controllerAction): string
    {
        if ($controllerAction === '__invoke') {
            return $controllerService;
        }

        return \sprintf('%s::%s', $controllerService, $controllerAction);
    }

    private function getRoutePath(string $path): string
    {
        return preg_replace_callback(
            '/(#)([\s\S]+?)(#)/',
            static fn (array $matches): string => \sprintf('{%s}', $matches[2]),
            $path
        );
    }

    private function getRouteName(string $pageId): string
    {
        return \sprintf('%s_%s', self::ROUTE_PREFIX, $pageId);
    }

    private function extractReferenceId(mixed $reference): ?string
    {
        if (!\is_array($reference)) {
            return null;
        }

        $id = $reference['$id'] ?? null;
        if ($id === null) {
            return null;
        }

        $id = trim((string) $id);

        return $id !== '' ? $id : null;
    }
}
