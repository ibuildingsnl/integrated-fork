<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Twig\Extension;

use Integrated\Bundle\PageBundle\Services\SolrUrlExtractor;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Common\Content\ContentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class UrlExtension extends AbstractExtension
{
    /**
     * @var UrlResolver
     */
    protected $urlResolver;

    /**
     * @var SolrUrlExtractor
     */
    protected $solrUrlExtractor;

    private ?RequestStack $requestStack;

    private ?UrlGeneratorInterface $urlGenerator;

    private ?UriSigner $uriSigner;

    private int $sessionBridgeTtlSeconds;

    public function __construct(
        UrlResolver $urlResolver,
        SolrUrlExtractor $solrUrlExtractor,
        ?RequestStack $requestStack = null,
        ?UrlGeneratorInterface $urlGenerator = null,
        ?UriSigner $uriSigner = null,
        int $sessionBridgeTtlSeconds = 300
    ) {
        $this->urlResolver = $urlResolver;
        $this->solrUrlExtractor = $solrUrlExtractor;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->uriSigner = $uriSigner;
        $this->sessionBridgeTtlSeconds = $sessionBridgeTtlSeconds;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('integrated_url', $this->getUrl(...)),
            new TwigFunction('integrated_session_bridge_url', $this->getSessionBridgeUrl(...)),
        ];
    }

    /**
     * @param null $channelId
     * @param bool $fallback
     *
     * @return string|null
     */
    public function getUrl($document, $channelId = null, $fallback = true)
    {
        try {
            if ($document instanceof ContentInterface) {
                return $this->urlResolver->generateUrl($document, $channelId, $fallback);
            }

            // probably solr document
            return $this->solrUrlExtractor->getUrl($document, $channelId);
        } catch (RouteNotFoundException $e) {
            // Missing content-type page routes should not break rendering contexts
            // like admin content edit toolbars.
            return null;
        }
    }

    public function getSessionBridgeUrl(string $domain, string $sessionId, mixed $path = '/'): string
    {
        $domain = trim($domain);
        $sessionId = trim($sessionId);
        $normalizedPath = $this->normalizeInternalPath($path);

        if ($domain === '' || $sessionId === '' || $this->urlGenerator === null || $this->uriSigner === null) {
            return $normalizedPath;
        }

        $scheme = $this->requestStack?->getCurrentRequest()?->getScheme() ?? 'https';
        $bridgePath = $this->urlGenerator->generate('integrated_website_enter_session', [
            'sessionId' => $sessionId,
            'path' => $normalizedPath,
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

        return $this->uriSigner->sign(
            $scheme.'://'.$domain.$bridgePath,
            time() + $this->sessionBridgeTtlSeconds
        );
    }

    private function normalizeInternalPath(mixed $path): string
    {
        $path = trim((string) $path);

        if ($path === '' || strpos($path, '/') !== 0 || strpos($path, '//') === 0) {
            return '/';
        }

        return $path;
    }

    public function getName(): string
    {
        return 'integrated_page_url';
    }
}
