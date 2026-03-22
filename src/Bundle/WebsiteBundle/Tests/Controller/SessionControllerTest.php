<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Integrated\Bundle\WebsiteBundle\Controller\SessionController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;

final class SessionControllerTest extends TestCase
{
    public function testEnterSessionDeniesUnsignedRequests(): void
    {
        $controller = new SessionController(new UriSigner('session-bridge-secret'));

        $response = $controller->enterSession('guest-session', Request::create(
            'https://target.example.test/enter-session/guest-session',
            'GET',
            ['path' => '/preview']
        ));

        self::assertSame(403, $response->getStatusCode());
        self::assertFalse($response->headers->has('Set-Cookie'));
    }

    public function testEnterSessionAcceptsValidSignedRequestAndRestoresSessionCookie(): void
    {
        $signer = new UriSigner('session-bridge-secret');
        $controller = new SessionController($signer);
        $signedUrl = $signer->sign(
            'https://target.example.test/enter-session/guest-session?path=%2Fpreview',
            time() + 300
        );

        $response = $controller->enterSession('guest-session', Request::create($signedUrl));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/preview', $response->headers->get('Location'));
        $cookies = $response->headers->getCookies();
        self::assertCount(1, $cookies);
        self::assertSame('PHPSESSID', $cookies[0]->getName());
        self::assertSame('guest-session', $cookies[0]->getValue());
    }

    public function testEnterSessionDeniesExpiredSignedRequests(): void
    {
        $signer = new UriSigner('session-bridge-secret');
        $controller = new SessionController($signer);
        $signedUrl = $signer->sign(
            'https://target.example.test/enter-session/guest-session?path=%2Fpreview',
            time() - 1
        );

        $response = $controller->enterSession('guest-session', Request::create($signedUrl));

        self::assertSame(403, $response->getStatusCode());
        self::assertFalse($response->headers->has('Set-Cookie'));
    }
}
