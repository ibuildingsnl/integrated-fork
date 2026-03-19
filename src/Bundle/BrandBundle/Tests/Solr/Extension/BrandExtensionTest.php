<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Solr\Extension;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Solr\Extension\BrandExtension;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Converter\Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BrandExtensionTest extends TestCase
{
    public function testBuildIndexesWebsiteChannelPresentationData(): void
    {
        $websiteChannel = new Channel();
        $websiteChannel->setId('testmerk_website');
        $websiteChannel->setName('Testmerk Website');
        $websiteChannel->setType(new ChannelType('website', 'Website'));

        $newsletterChannel = new Channel();
        $newsletterChannel->setId('testmerk_newsletter');
        $newsletterChannel->setName('Testmerk Newsletter');
        $newsletterChannel->setType(new ChannelType('newsletter', 'Newsletter'));

        $brandProfile = new BrandProfile();
        $brandProfile->name = 'Testmerk';
        $favicon = $this->createMock(Image::class);
        $favicon->method('getFile')->willReturn($this->createStorageMock('/storage/brands/testmerk/favicon.png'));
        $brandProfile->setFavicon($favicon);

        $brand = new Brand($brandProfile);
        $brand->setId('testmerk');
        $brand->addChannelLink(new ChannelLink(new ChannelType('Website', 'Website'), $websiteChannel, true));
        $brand->addChannelLink(new ChannelLink(new ChannelType('Newsletter', 'Newsletter'), $newsletterChannel, false));

        $content = new class extends Content {
            public function getTitle(): string
            {
                return 'Test';
            }

            public function __toString(): string
            {
                return $this->getTitle();
            }
        };
        $content->addChannel($newsletterChannel);

        $repository = $this->createMock(BrandRepository::class);
        $repository->method('all')->willReturn([$brand]);

        $extension = new BrandExtension($repository);
        $container = new Container();
        $extension->build($container, $content);

        self::assertSame(['testmerk'], $container->get('facet_brands'));
        self::assertSame(['testmerk_website'], $container->get('website_channel_ids_string'));
        self::assertSame(['Testmerk'], $container->get('website_channel_names_string'));
        self::assertSame(['/storage/brands/testmerk/favicon.png'], $container->get('website_channel_favicon_paths_string'));
    }

    public function testBuildFallsBackToWebsiteChannelNameWithoutBrandFavicon(): void
    {
        $websiteChannel = new Channel();
        $websiteChannel->setId('standalone_website');
        $websiteChannel->setName('Standalone Website');
        $websiteChannel->setType(new ChannelType('website', 'Website'));

        $content = new class extends Content {
            public function getTitle(): string
            {
                return 'Standalone';
            }

            public function __toString(): string
            {
                return $this->getTitle();
            }
        };
        $content->addChannel($websiteChannel);

        $repository = $this->createMock(BrandRepository::class);
        $repository->method('all')->willReturn([]);

        $extension = new BrandExtension($repository);
        $container = new Container();
        $extension->build($container, $content);

        self::assertSame(['None'], $container->get('facet_brands'));
        self::assertSame(['standalone_website'], $container->get('website_channel_ids_string'));
        self::assertSame(['Standalone Website'], $container->get('website_channel_names_string'));
        self::assertSame([''], $container->get('website_channel_favicon_paths_string'));
    }

    private function createStorageMock(string $pathname): Storage&MockObject
    {
        $storage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPathname'])
            ->getMock();
        $storage->method('getPathname')->willReturn($pathname);

        return $storage;
    }
}
