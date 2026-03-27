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

use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PageSeoMetadataResolver
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly bool $defaultPaginatedNoindex = true,
    ) {
    }

    /**
     * @return array{title: string, description: ?string, canonicalUrl: ?string, robots: ?string}
     */
    public function resolve(?AbstractPage $page): array
    {
        $title = 'Untitled';
        $description = null;
        $canonicalUrl = null;
        $paginationNoindexEnabled = $this->defaultPaginatedNoindex;

        if ($page instanceof AbstractPage) {
            $title = trim((string) $page->getTitle()) !== '' ? (string) $page->getTitle() : $title;
        }

        if ($page instanceof Page) {
            $seoTitle = trim((string) $page->getSeoTitle());
            if ($seoTitle !== '') {
                $title = $seoTitle;
            }

            $description = $this->normalizeString($page->getSeoDescription());
            if ($description === null) {
                $description = $this->normalizeString($page->getDescription());
            }

            $canonicalUrl = $this->normalizeString($page->getCanonicalUrl());

            if (null !== $page->isPaginationNoindexEnabled()) {
                $paginationNoindexEnabled = (bool) $page->isPaginationNoindexEnabled();
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonicalUrl' => $canonicalUrl,
            'robots' => $paginationNoindexEnabled && $this->isPaginatedRequestAbovePageOne($this->requestStack->getMainRequest())
                ? 'noindex,follow'
                : null,
        ];
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!\is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
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
