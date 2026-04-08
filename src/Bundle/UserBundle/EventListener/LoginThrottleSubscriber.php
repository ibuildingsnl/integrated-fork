<?php

namespace Integrated\Bundle\UserBundle\EventListener;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginThrottleSubscriber implements EventSubscriberInterface
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900;
    private const BLOCK_SECONDS = 900;
    private const CACHE_KEY_PREFIX = 'integrated_user_login_throttle_';

    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9],
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isLoginCheckRequest($request)) {
            return;
        }

        $cacheKey = $this->buildCacheKey($request);
        $state = $this->getState($cacheKey);
        $now = time();

        $windowResetAt = (int) ($state['window_reset_at'] ?? 0);
        if ($windowResetAt <= $now) {
            $this->cache->deleteItem($cacheKey);

            return;
        }

        $blockedUntil = (int) ($state['blocked_until'] ?? 0);
        if ($blockedUntil > $now) {
            $this->addWarningFlash(
                $request,
                'Too many login attempts. Please wait 15 minutes and try again.'
            );

            $event->setResponse(new RedirectResponse($this->resolveLoginPath($request), 303));
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isLoginCheckRequest($request)) {
            return;
        }

        $cacheKey = $this->buildCacheKey($request);
        $state = $this->getState($cacheKey);
        $now = time();

        $count = (int) ($state['count'] ?? 0);
        $windowResetAt = (int) ($state['window_reset_at'] ?? 0);

        if ($windowResetAt <= $now) {
            $count = 0;
            $windowResetAt = $now + self::WINDOW_SECONDS;
        }

        ++$count;
        $blockedUntil = (int) ($state['blocked_until'] ?? 0);
        if ($count >= self::MAX_ATTEMPTS) {
            $blockedUntil = max($blockedUntil, $now + self::BLOCK_SECONDS);
        }

        $expiresAt = max($windowResetAt, $blockedUntil);
        $this->saveState($cacheKey, [
            'count' => $count,
            'window_reset_at' => $windowResetAt,
            'blocked_until' => $blockedUntil,
        ], $expiresAt);
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isLoginCheckRequest($request)) {
            return;
        }

        $this->cache->deleteItem($this->buildCacheKey($request));
    }

    private function isLoginCheckRequest(Request $request): bool
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            return false;
        }

        $path = $request->getPathInfo();

        return $path === '/admin/login-check' || $path === '/login-check';
    }

    private function buildCacheKey(Request $request): string
    {
        $path = $request->getPathInfo();
        $ip = (string) ($request->getClientIp() ?? 'unknown');
        $username = $this->normalizeUsername($request->request->get('_username'));

        return self::CACHE_KEY_PREFIX.hash('sha256', $path.'|'.$ip.'|'.$username);
    }

    private function resolveLoginPath(Request $request): string
    {
        return str_starts_with($request->getPathInfo(), '/admin/')
            ? '/admin/login'
            : '/login';
    }

    private function normalizeUsername(mixed $username): string
    {
        if (!\is_scalar($username)) {
            return '';
        }

        return strtolower(trim((string) $username));
    }

    private function addWarningFlash(Request $request, string $message): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        $this->startSessionIfNeeded($session);

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('warning', $message);

            return;
        }

        $flashBag = $session->getBag('flashes');
        if ($flashBag instanceof FlashBagInterface) {
            $flashBag->add('warning', $message);
        }
    }

    private function startSessionIfNeeded(SessionInterface $session): void
    {
        if (!$session->isStarted()) {
            $session->start();
        }
    }

    /** @return array{count?: int, window_reset_at?: int, blocked_until?: int} */
    private function getState(string $cacheKey): array
    {
        $item = $this->cache->getItem($cacheKey);
        $payload = $item->isHit() ? $item->get() : null;

        return \is_array($payload) ? $payload : [];
    }

    /**
     * @param array{count: int, window_reset_at: int, blocked_until: int} $state
     */
    private function saveState(string $cacheKey, array $state, int $expiresAt): void
    {
        $item = $this->cache->getItem($cacheKey);
        $item->set($state);
        $item->expiresAt((new \DateTimeImmutable())->setTimestamp($expiresAt));
        $this->cache->save($item);
    }
}
