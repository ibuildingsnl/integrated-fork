<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Router proxy that keeps a custom URL generator while delegating route matching to the default router.
 */
class Router implements RouterInterface, RequestMatcherInterface, WarmableInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly object $generator,
        private RequestContext $context = new RequestContext(),
    ) {
    }

    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): RequestContext
    {
        return $this->context;
    }

    public function match(string $pathinfo): array
    {
        return $this->getMatcher()->match($pathinfo);
    }

    public function matchRequest(Request $request): array
    {
        $matcher = $this->getMatcher();

        if ($matcher instanceof RequestMatcherInterface) {
            return $matcher->matchRequest($request);
        }

        return $matcher->match($request->getPathInfo());
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->router->getRouteCollection();
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        if ($this->router instanceof WarmableInterface) {
            return $this->router->warmUp($cacheDir, $buildDir);
        }

        return [];
    }

    private function getMatcher(): UrlMatcherInterface
    {
        $this->router->setContext($this->getContext());

        return $this->router;
    }

    private function getGenerator(): UrlGeneratorInterface
    {
        if ($this->generator instanceof UrlGeneratorInterface) {
            $this->generator->setContext($this->getContext());

            return $this->generator;
        }

        $this->router->setContext($this->getContext());

        return $this->router;
    }
}
