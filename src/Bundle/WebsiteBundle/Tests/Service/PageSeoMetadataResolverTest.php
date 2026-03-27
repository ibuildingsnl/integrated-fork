<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Service;

use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\WebsiteBundle\Service\PageSeoMetadataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PageSeoMetadataResolverTest extends TestCase
{
    public function testItUsesSeoOverridesWhenPresent(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://example.test/nieuws'));
        $resolver = new PageSeoMetadataResolver($requestStack, true);
        $page = new Page();
        $page->setTitle('Nieuws');
        $page->setDescription('Fallback description');
        $seoMetadata = new SeoMeta();
        $seoMetadata->setMetatitle('SEO nieuws');
        $seoMetadata->setMetadescription('SEO description');
        $page->setSeoMetadata($seoMetadata);
        $page->setCanonicalUrl('https://example.test/nieuws');
        $page->setTwitterCard('summary');
        $page->setFeaturedImage($this->imageWithPath('/storage/featured.jpg'));

        $metadata = $resolver->resolve($page);

        self::assertSame('SEO nieuws', $metadata['title']);
        self::assertSame('SEO description', $metadata['description']);
        self::assertSame('https://example.test/nieuws', $metadata['canonicalUrl']);
        self::assertNull($metadata['robots']);
        self::assertSame('SEO nieuws', $metadata['openGraphTitle']);
        self::assertSame('SEO description', $metadata['openGraphDescription']);
        self::assertSame('https://example.test/storage/featured.jpg', $metadata['openGraphImageUrl']);
        self::assertSame('summary', $metadata['twitterCard']);
        self::assertSame('SEO nieuws', $metadata['twitterTitle']);
        self::assertSame('SEO description', $metadata['twitterDescription']);
        self::assertSame('https://example.test/storage/featured.jpg', $metadata['twitterImageUrl']);
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
        self::assertSame('Nieuws', $metadata['openGraphTitle']);
        self::assertSame('Fallback description', $metadata['openGraphDescription']);
        self::assertNull($metadata['openGraphImageUrl']);
        self::assertSame('summary', $metadata['twitterCard']);
        self::assertSame('Nieuws', $metadata['twitterTitle']);
        self::assertSame('Fallback description', $metadata['twitterDescription']);
        self::assertNull($metadata['twitterImageUrl']);
    }

    public function testItResolvesConfiguredSeoPlaceholders(): void
    {
        $request = Request::create('https://example.test/nieuws-overzicht');
        $request->attributes->set('_channel', ['name' => 'Bioprocessing News']);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $resolver = new PageSeoMetadataResolver($requestStack, true);
        $page = new Page();
        $page->setTitle('Nieuws overzicht');
        $page->setDescription('Fallback description');
        $page->setPath('/nieuws-overzicht');

        $seoMetadata = new SeoMeta();
        $seoMetadata->setMetatitle('%%title%% %%separator%% %%site_title%%');
        $seoMetadata->setMetadescription('Lees %%title%% op %%channel%% via %%slug%%');
        $page->setSeoMetadata($seoMetadata);

        $metadata = $resolver->resolve($page);

        self::assertSame('Nieuws overzicht | Bioprocessing News', $metadata['title']);
        self::assertSame('Lees Nieuws overzicht op Bioprocessing News via nieuws-overzicht', $metadata['description']);
        self::assertSame('Nieuws overzicht | Bioprocessing News', $metadata['openGraphTitle']);
        self::assertSame('Lees Nieuws overzicht op Bioprocessing News via nieuws-overzicht', $metadata['openGraphDescription']);
        self::assertSame('Nieuws overzicht | Bioprocessing News', $metadata['twitterTitle']);
        self::assertSame('Lees Nieuws overzicht op Bioprocessing News via nieuws-overzicht', $metadata['twitterDescription']);
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

    public function testExplicitRobotsDirectiveOverridesPaginationDefault(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([
            'page' => '4',
        ]));

        $resolver = new PageSeoMetadataResolver($requestStack, true);
        $page = new Page();
        $page->setRobotsDirective('index,follow');

        $metadata = $resolver->resolve($page);

        self::assertSame('index,follow', $metadata['robots']);
    }

    private function imageWithPath(string $pathname): Image
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('getPathname')->willReturn($pathname);

        $image = new Image();
        $image->setFile($storage);

        return $image;
    }
}
