<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Service;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\WebsiteBundle\Service\PageSeoMetadataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PageSeoMetadataResolverTest extends TestCase
{
    public function testItUsesSeoOverridesWhenPresent(): void
    {
        $resolver = new PageSeoMetadataResolver(new RequestStack(), true);
        $page = new Page();
        $page->setTitle('Nieuws');
        $page->setDescription('Fallback description');
        $page->setSeoTitle('SEO nieuws');
        $page->setSeoDescription('SEO description');
        $page->setCanonicalUrl('https://example.test/nieuws');

        $metadata = $resolver->resolve($page);

        self::assertSame('SEO nieuws', $metadata['title']);
        self::assertSame('SEO description', $metadata['description']);
        self::assertSame('https://example.test/nieuws', $metadata['canonicalUrl']);
        self::assertNull($metadata['robots']);
    }

    public function testItFallsBackToPageTitleAndDescription(): void
    {
        $resolver = new PageSeoMetadataResolver(new RequestStack(), true);
        $page = new Page();
        $page->setTitle('Nieuws');
        $page->setDescription('Fallback description');

        $metadata = $resolver->resolve($page);

        self::assertSame('Nieuws', $metadata['title']);
        self::assertSame('Fallback description', $metadata['description']);
        self::assertNull($metadata['canonicalUrl']);
        self::assertNull($metadata['robots']);
    }

    public function testItAddsNoindexFollowOnPaginatedRequestsAbovePageOneByDefault(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([
            'bib_nieuwspagina-page' => '4',
        ]));

        $resolver = new PageSeoMetadataResolver($requestStack, true);
        $metadata = $resolver->resolve(new Page());

        self::assertSame('noindex,follow', $metadata['robots']);
    }

    public function testItDoesNotAddPaginationRobotsForFirstPage(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([
            'bib_nieuwspagina-page' => '1',
        ]));

        $resolver = new PageSeoMetadataResolver($requestStack, true);
        $metadata = $resolver->resolve(new Page());

        self::assertNull($metadata['robots']);
    }

    public function testPageLevelOverrideCanDisablePaginatedNoindex(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([
            'page' => '2',
        ]));

        $resolver = new PageSeoMetadataResolver($requestStack, true);
        $page = new Page();
        $page->setPaginationNoindexEnabled(false);

        $metadata = $resolver->resolve($page);

        self::assertNull($metadata['robots']);
    }
}
