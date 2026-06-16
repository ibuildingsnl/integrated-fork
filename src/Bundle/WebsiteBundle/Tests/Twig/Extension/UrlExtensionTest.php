<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Twig\Extension;

use Integrated\Bundle\PageBundle\Services\SolrUrlExtractor;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Bundle\WebsiteBundle\Twig\Extension\UrlExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\TwigFunction;

final class UrlExtensionTest extends TestCase
{
    public function testSessionBridgeHelperBuildsSignedAbsoluteUrl(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://cms.example.test/admin/pages'));

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('integrated_website_enter_session', ['sessionId' => 'session-123', 'path' => '/preview'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/enter-session/session-123?path=%2Fpreview');

        $signer = new UriSigner('session-bridge-secret');
        $extension = new UrlExtension(
            $this->createMock(UrlResolver::class),
            $this->createMock(SolrUrlExtractor::class),
            $requestStack,
            $router,
            $signer,
            300,
        );

        $functions = $this->indexFunctions($extension);
        self::assertArrayHasKey('integrated_session_bridge_url', $functions);

        $url = ($functions['integrated_session_bridge_url']->getCallable())('target.example.test', 'session-123', '/preview');

        self::assertStringStartsWith('https://target.example.test/enter-session/session-123?', $url);
        self::assertTrue($signer->check($url));

        parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);
        self::assertSame('/preview', $query['path'] ?? null);
        self::assertArrayHasKey('_expiration', $query);
        self::assertArrayHasKey('_hash', $query);
    }

    public function testSessionBridgeHelperNormalizesUnsafeTargetPaths(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://cms.example.test/admin/pages'));

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('integrated_website_enter_session', ['sessionId' => 'session-123', 'path' => '/'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/enter-session/session-123?path=%2F');

        $extension = new UrlExtension(
            $this->createMock(UrlResolver::class),
            $this->createMock(SolrUrlExtractor::class),
            $requestStack,
            $router,
            new UriSigner('session-bridge-secret'),
            300,
        );

        $functions = $this->indexFunctions($extension);
        self::assertArrayHasKey('integrated_session_bridge_url', $functions);

        $url = ($functions['integrated_session_bridge_url']->getCallable())('target.example.test', 'session-123', 'https://evil.example/phish');

        parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);
        self::assertSame('/', $query['path'] ?? null);
    }

    /**
     * @return array<string, TwigFunction>
     */
    private function indexFunctions(UrlExtension $extension): array
    {
        $indexed = [];

        foreach ($extension->getFunctions() as $function) {
            $indexed[$function->getName()] = $function;
        }

        return $indexed;
    }
}
