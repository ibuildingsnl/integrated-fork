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

use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\WebsiteBundle\Service\PageSeoMetadataResolver;
use Integrated\Bundle\WebsiteBundle\Service\SeoPlaceholderResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PageSeoExtension extends AbstractExtension
{
    public function __construct(
        private readonly PageSeoMetadataResolver $resolver,
        private readonly SeoPlaceholderResolver $seoPlaceholderResolver,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('integrated_page_seo', $this->resolvePageSeo(...)),
            new TwigFunction('integrated_resolve_seo_placeholders', $this->resolveSeoPlaceholders(...)),
        ];
    }

    /**
     * @return array{title: string, description: ?string, canonicalUrl: ?string, robots: ?string}
     */
    public function resolvePageSeo(?AbstractPage $page): array
    {
        return $this->resolver->resolve($page);
    }

    /**
     * @param array<string, scalar|null> $context
     */
    public function resolveSeoPlaceholders(?string $value, array $context = []): ?string
    {
        return $this->seoPlaceholderResolver->resolve($value, $context);
    }
}
