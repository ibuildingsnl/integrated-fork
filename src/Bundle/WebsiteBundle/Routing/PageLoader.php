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
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class PageLoader implements LoaderInterface
{
    public const ROUTE_PREFIX = 'integrated_website_page_';

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Page loader is already added');
        }

        $routes = new RouteCollection();

        $pages = $this->getPages();

        foreach ($pages as $page) {
            $condition = '';
            $channelId = $this->extractReferenceId($page['channel'] ?? null);
            if ($channelId !== null) {
                $condition = 'request.attributes.get("_channel") == "'.$channelId.'"';
            }

            $route = new Route(
                (string) ($page['path'] ?? ''),
                [
                    '_controller' => 'Integrated\Bundle\WebsiteBundle\Controller\PageController::show',
                    'page' => (string) ($page['_id'] ?? ''),
                ],
                [],
                [],
                '',
                [],
                [],
                $condition
            );

            $routes->add(self::ROUTE_PREFIX.(string) ($page['_id'] ?? ''), $route);
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null): bool
    {
        return 'integrated_website_page' === $type;
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    protected function getPages(): iterable
    {
        return $this->dm->createQueryBuilder(Page::class)
            ->select(['id', 'path', 'channel'])
            ->hydrate(false)
            ->getQuery()
            ->getIterator();
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

    public function getResolver(): LoaderResolverInterface
    {
        return new LoaderResolver();
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
    }
}
