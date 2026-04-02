<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\EventListener;

use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AnonymousPageCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly int $ttl = 600,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -192]];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isCacheableRequest($request)) {
            return;
        }

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return;
        }

        $response = $event->getResponse();
        if (!$response->isSuccessful()) {
            return;
        }

        if ($response->headers->getCookies() !== []) {
            return;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (stripos($contentType, 'text/html') === false) {
            return;
        }

        $robots = strtolower(trim((string) $response->headers->get('X-Robots-Tag', '')));
        if ($robots !== '' && str_contains($robots, 'noindex')) {
            return;
        }

        if ($response->headers->hasCacheControlDirective('no-store')) {
            return;
        }

        $ttl = max(0, $this->ttl);
        $response->setPublic();
        $response->setMaxAge($ttl);
        $response->setSharedMaxAge($ttl);
    }

    private function isCacheableRequest(Request $request): bool
    {
        if (!\in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return false;
        }

        if ($request->isXmlHttpRequest()) {
            return false;
        }

        $route = (string) $request->attributes->get('_route', '');

        return str_starts_with($route, ContentTypePageLoader::ROUTE_PREFIX.'_');
    }
}
