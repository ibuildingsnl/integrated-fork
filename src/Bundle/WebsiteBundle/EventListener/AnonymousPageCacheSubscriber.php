<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\EventListener;

use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AnonymousPageCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly int $ttl = 600,
        private readonly ?TokenStorageInterface $tokenStorage = null,
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

        if ($this->isAuthenticatedRequest($request)) {
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
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, '1');
    }

    private function isCacheableRequest(Request $request): bool
    {
        if (!\in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return false;
        }

        if ($request->isXmlHttpRequest()) {
            return false;
        }

        if ($request->query->getBoolean('integrated_website_edit')) {
            return false;
        }

        $route = (string) $request->attributes->get('_route', '');

        return str_starts_with($route, ContentTypePageLoader::ROUTE_PREFIX.'_');
    }

    private function isAuthenticatedRequest(Request $request): bool
    {
        $hasSessionCookie = false;
        $sessionCookieName = session_name();
        if (\is_string($sessionCookieName) && $sessionCookieName !== '' && $request->cookies->has($sessionCookieName)) {
            $hasSessionCookie = true;
        }

        $hasRememberMeCookie = $request->cookies->has('REMEMBERME');
        if (!$hasSessionCookie && !$hasRememberMeCookie) {
            return false;
        }

        $token = $this->tokenStorage?->getToken();
        if ($token === null) {
            return $hasRememberMeCookie;
        }

        $user = $token->getUser();
        if (\is_object($user)) {
            return true;
        }

        if (!\is_string($user)) {
            return false;
        }

        $normalized = strtolower(trim($user));

        return $normalized !== '' && $normalized !== 'anon.' && $normalized !== 'anonymous';
    }
}
