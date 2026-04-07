<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Twig\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Infrastructure\CachedBrandRepository;
use Integrated\Bundle\BrandBundle\Twig\Extension\BrandExtension;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

final class BrandExtensionTest extends TestCase
{
    public function testMemoizesRepositoryReadsAcrossChannelLookups(): void
    {
        $websiteChannel = new Channel();
        $websiteChannel->setId('website_channel');
        $websiteChannel->setName('Website');

        $newsletterChannel = new Channel();
        $newsletterChannel->setId('newsletter_channel');
        $newsletterChannel->setName('Newsletter');

        $profile = new BrandProfile();
        $profile->name = 'Test brand';

        $brand = new Brand($profile);
        $brand->addChannelLink(new ChannelLink(new ChannelType('website', 'Website'), $websiteChannel, true));
        $brand->addChannelLink(new ChannelLink(new ChannelType('newsletter', 'Newsletter'), $newsletterChannel, false));

        $otherBrand = new Brand((function (): BrandProfile {
            $profile = new BrandProfile();
            $profile->name = 'Other brand';

            return $profile;
        })());

        $repository = $this->createMock(BrandRepository::class);
        $repository
            ->expects(self::once())
            ->method('all')
            ->willReturn([$brand, $otherBrand]);

        $extension = new BrandExtension($repository);

        self::assertSame($brand, $extension->getBrandForChannel($websiteChannel));
        self::assertSame($brand, $extension->getBrandForChannel($websiteChannel));
        self::assertSame($profile, $extension->getBrandProfileForChannel($websiteChannel));
        self::assertSame($websiteChannel, $extension->getBrandWebsiteChannel($websiteChannel));
        self::assertCount(2, $extension->getAllBrands() ?? []);
    }

    public function testCachesOtherBrandsForChannel(): void
    {
        $websiteChannel = new Channel();
        $websiteChannel->setId('website_channel');
        $websiteChannel->setName('Website');

        $profile = new BrandProfile();
        $profile->name = 'Test brand';

        $brand = new Brand($profile);
        $brand->addChannelLink(new ChannelLink(new ChannelType('website', 'Website'), $websiteChannel, true));

        $otherBrand = new Brand((function (): BrandProfile {
            $profile = new BrandProfile();
            $profile->name = 'Other brand';

            return $profile;
        })());

        $repository = $this->createMock(BrandRepository::class);
        $repository
            ->expects(self::once())
            ->method('all')
            ->willReturn([$brand, $otherBrand]);

        $extension = new BrandExtension($repository);

        $first = $extension->getAllOtherBrands($websiteChannel);
        $second = $extension->getAllOtherBrands($websiteChannel);

        self::assertInstanceOf(ArrayCollection::class, $first);
        self::assertSame($first, $second);
        self::assertCount(1, $first);
        self::assertSame($otherBrand, $first->first());
    }

    public function testUsesPersistentChannelLookupCacheToAvoidFullBrandScan(): void
    {
        $websiteChannel = new Channel();
        $websiteChannel->setId('website_channel');

        $profile = new BrandProfile();
        $profile->name = 'Test brand';

        $brand = new Brand($profile);
        $brand->setId('brand_id');

        $repository = $this->createMock(BrandRepository::class);
        $repository
            ->expects(self::never())
            ->method('all');
        $repository
            ->expects(self::once())
            ->method('find')
            ->with('brand_id')
            ->willReturn($brand);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('get')
            ->with(CachedBrandRepository::CHANNEL_LOOKUP_CACHE_KEY, self::isType('callable'))
            ->willReturn(['website_channel' => 'brand_id']);

        $extension = new BrandExtension($repository, $cache);

        self::assertSame($brand, $extension->getBrandForChannel($websiteChannel));
        self::assertSame($brand, $extension->getBrandForChannel($websiteChannel));
    }
}
