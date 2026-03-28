<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Service;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PageSeoMetadataResolver
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly bool $defaultPaginatedNoindex = true,
        private readonly SeoPlaceholderResolver $seoPlaceholderResolver = new SeoPlaceholderResolver(),
    ) {
    }

    /**
     * @return array{title: string, description: ?string, canonicalUrl: ?string, robots: ?string, openGraphTitle: ?string, openGraphDescription: ?string, openGraphImageUrl: ?string, twitterCard: ?string, twitterTitle: ?string, twitterDescription: ?string, twitterImageUrl: ?string}
     */
    public function resolve(?AbstractPage $page): array
    {
        $title = 'Untitled';
        $description = null;
        $canonicalUrl = null;
        $paginationNoindexEnabled = $this->defaultPaginatedNoindex;
        $robots = null;
        $openGraphTitle = null;
        $openGraphDescription = null;
        $openGraphImageUrl = null;
        $twitterCard = null;
        $twitterTitle = null;
        $twitterDescription = null;
        $twitterImageUrl = null;

        if ($page instanceof AbstractPage) {
            $title = trim((string) $page->getTitle()) !== '' ? (string) $page->getTitle() : $title;
        }

        if ($page instanceof Page) {
            $seoMetadata = $page->getSeoMetadata();
            $seoTitle = $this->resolveSeoMetaString($seoMetadata, 'getMetatitle');
            if ($seoTitle !== '') {
                $title = $seoTitle;
            } else {
                $legacySeoTitle = trim((string) $page->getSeoTitle());
                if ($legacySeoTitle !== '') {
                    $title = $legacySeoTitle;
                }
            }

            $description = $this->resolveSeoMetaStringOrNull($seoMetadata, 'getMetadescription');
            if ($description === null) {
                $description = $this->normalizeString($page->getSeoDescription());
            }
            if ($description === null) {
                $description = $this->normalizeString($page->getDescription());
            }

            $placeholderContext = $this->buildPlaceholderContext($page, $this->requestStack->getMainRequest());
            $title = $this->seoPlaceholderResolver->resolve($title, $placeholderContext) ?? $title;
            $description = $description !== null ? $this->seoPlaceholderResolver->resolve($description, $placeholderContext) : null;

            $canonicalUrl = $this->normalizeString($page->getCanonicalUrl());
            $robots = $this->normalizeDirective($page->getRobotsDirective());

            if (null !== $page->isPaginationNoindexEnabled()) {
                $paginationNoindexEnabled = (bool) $page->isPaginationNoindexEnabled();
            }

            $openGraphTitle = $title;
            $openGraphDescription = $description;
            $openGraphImageUrl = $this->resolveFeaturedImageUrl($page->getFeaturedImage(), $this->requestStack->getMainRequest());

            $twitterCard = $this->normalizeDirective($page->getTwitterCard());
            if ($twitterCard === null) {
                $twitterCard = $openGraphImageUrl !== null ? 'summary_large_image' : 'summary';
            }

            $twitterTitle = $openGraphTitle;
            $twitterDescription = $openGraphDescription;
            $twitterImageUrl = $openGraphImageUrl;
        } else {
            $openGraphTitle = $title;
            $openGraphDescription = $description;
            $twitterCard = 'summary';
            $twitterTitle = $title;
            $twitterDescription = $description;
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonicalUrl' => $canonicalUrl,
            'robots' => $robots ?? ($paginationNoindexEnabled && $this->isPaginatedRequestAbovePageOne($this->requestStack->getMainRequest())
                ? 'noindex,follow'
                : null),
            'openGraphTitle' => $openGraphTitle,
            'openGraphDescription' => $openGraphDescription,
            'openGraphImageUrl' => $openGraphImageUrl,
            'twitterCard' => $twitterCard,
            'twitterTitle' => $twitterTitle,
            'twitterDescription' => $twitterDescription,
            'twitterImageUrl' => $twitterImageUrl,
        ];
    }

    private function resolveSeoMetaString(?SeoMeta $seoMeta, string $getter): string
    {
        if (!$seoMeta instanceof SeoMeta || !method_exists($seoMeta, $getter)) {
            return '';
        }

        return trim((string) $seoMeta->{$getter}());
    }

    private function resolveSeoMetaStringOrNull(?SeoMeta $seoMeta, string $getter): ?string
    {
        $value = $this->resolveSeoMetaString($seoMeta, $getter);

        return $value === '' ? null : $value;
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!\is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeDirective(mixed $value): ?string
    {
        $value = $this->normalizeString($value);
        if ($value === null || $value === 'default') {
            return null;
        }

        return $value;
    }

    private function resolveFeaturedImageUrl(?Image $image, ?Request $request): ?string
    {
        if (!$image instanceof Image) {
            return null;
        }

        $file = $image->getFile();
        if (!$file instanceof StorageInterface) {
            return null;
        }

        $path = trim((string) $file->getPathname());
        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        if ($request instanceof Request) {
            return $request->getSchemeAndHttpHost().$path;
        }

        return $path;
    }

    /**
     * @return array{title: string, site_title: string, separator: string, slug: string, channel: string}
     */
    private function buildPlaceholderContext(Page $page, ?Request $request): array
    {
        $channelName = $this->resolveChannelName($request);
        $siteTitle = $channelName;

        if ($siteTitle === '' && $request instanceof Request) {
            $siteTitle = trim($request->getHost());
        }

        return [
            'title' => trim((string) $page->getTitle()),
            'site_title' => $siteTitle,
            'separator' => '|',
            'slug' => trim((string) $page->getPath()),
            'channel' => $channelName !== '' ? $channelName : $siteTitle,
        ];
    }

    private function resolveChannelName(?Request $request): string
    {
        if (!$request instanceof Request) {
            return '';
        }

        $channel = $request->attributes->get('_channel');
        if (\is_array($channel)) {
            return trim((string) ($channel['name'] ?? $channel['label'] ?? $channel['title'] ?? ''));
        }

        if (\is_object($channel)) {
            foreach (['getName', 'getLabel', 'getTitle'] as $method) {
                if (method_exists($channel, $method)) {
                    return trim((string) $channel->{$method}());
                }
            }
        }

        return '';
    }

    private function isPaginatedRequestAbovePageOne(?Request $request): bool
    {
        if (!$request instanceof Request) {
            return false;
        }

        foreach ($request->query->all() as $name => $value) {
            if (!$this->isPaginationParameterName((string) $name)) {
                continue;
            }

            $page = $this->extractPositiveInt($value);
            if ($page > 1) {
                return true;
            }
        }

        return false;
    }

    private function isPaginationParameterName(string $name): bool
    {
        return $name === 'page' || str_ends_with($name, '-page');
    }

    private function extractPositiveInt(mixed $value): int
    {
        if (\is_array($value)) {
            return 0;
        }

        if (\is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (\is_string($value) && preg_match('/^\d+$/', $value) === 1) {
            return (int) $value;
        }

        return 0;
    }
}
