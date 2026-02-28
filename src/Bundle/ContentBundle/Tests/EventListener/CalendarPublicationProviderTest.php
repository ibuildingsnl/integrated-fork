<?php

namespace Integrated\Bundle\ContentBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\Event\CalendarEvent;
use Integrated\Bundle\ContentBundle\EventListener\CalendarPublicationProvider;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;

class CalendarPublicationProviderTest extends TestCase
{
    public function testAddPublicationScheduleLoadsBrandsOnceForMultiplePublications(): void
    {
        $assetManager = $this->createMock(AssetManager::class);
        $assetManager
            ->expects($this->exactly(2))
            ->method('add');

        $publicationRepository = $this->createMock(PublicationRepositoryInterface::class);
        $publicationRepository
            ->expects($this->once())
            ->method('forDateRange')
            ->willReturn([
                $this->createPublication('publication-1'),
                $this->createPublication('publication-2'),
            ]);

        $brandProfile = $this->createMock(BrandProfile::class);
        $brandProfile
            ->method('getColor')
            ->willReturn('#123456');
        $brandProfile
            ->method('getFavicon')
            ->willReturn(null);

        $brand = $this->createMock(Brand::class);
        $brand
            ->method('hasChannel')
            ->willReturn(true);
        $brand
            ->method('getProfile')
            ->willReturn($brandProfile);
        $brand
            ->method('getId')
            ->willReturn('vismagazine');
        $brand
            ->method('getName')
            ->willReturn('Vismagazine');

        $brands = $this->createMock(BrandRepository::class);
        $brands
            ->expects($this->once())
            ->method('all')
            ->willReturn([$brand]);

        $documentRepository = $this->createMock(DocumentRepository::class);
        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(File::class)
            ->willReturn($documentRepository);

        $provider = new CalendarPublicationProvider(
            $assetManager,
            $publicationRepository,
            $brands,
            $documentManager,
            $this->createMock(ImageExtension::class),
        );

        $provider->addPublicationSchedule(new CalendarEvent([
            'start' => new \DateTimeImmutable('2026-02-01 00:00:00'),
            'end' => new \DateTimeImmutable('2026-02-28 23:59:59'),
        ]));
    }

    public function testAddPublicationScheduleRespectsConfiguredLimit(): void
    {
        $capturedInlineScript = null;

        $assetManager = $this->createMock(AssetManager::class);
        $assetManager
            ->expects($this->exactly(2))
            ->method('add')
            ->willReturnCallback(function (string $asset) use (&$capturedInlineScript): void {
                if (str_starts_with($asset, 'const publicationSchedule = ')) {
                    $capturedInlineScript = $asset;
                }
            });

        $publicationRepository = $this->createMock(PublicationRepositoryInterface::class);
        $publicationRepository
            ->expects($this->once())
            ->method('forDateRange')
            ->willReturn([
                $this->createPublication('publication-1'),
                $this->createPublication('publication-2'),
                $this->createPublication('publication-3'),
            ]);

        $brandProfile = $this->createMock(BrandProfile::class);
        $brandProfile
            ->method('getColor')
            ->willReturn('#123456');
        $brandProfile
            ->method('getFavicon')
            ->willReturn(null);

        $brand = $this->createMock(Brand::class);
        $brand
            ->method('hasChannel')
            ->willReturn(true);
        $brand
            ->method('getProfile')
            ->willReturn($brandProfile);
        $brand
            ->method('getId')
            ->willReturn('vismagazine');
        $brand
            ->method('getName')
            ->willReturn('Vismagazine');

        $brands = $this->createMock(BrandRepository::class);
        $brands
            ->expects($this->once())
            ->method('all')
            ->willReturn([$brand]);

        $documentRepository = $this->createMock(DocumentRepository::class);
        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(File::class)
            ->willReturn($documentRepository);

        $provider = new CalendarPublicationProvider(
            $assetManager,
            $publicationRepository,
            $brands,
            $documentManager,
            $this->createMock(ImageExtension::class),
        );

        $provider->addPublicationSchedule(new CalendarEvent([
            'start' => new \DateTimeImmutable('2026-02-01 00:00:00'),
            'end' => new \DateTimeImmutable('2026-02-28 23:59:59'),
            'limit' => 2,
        ]));

        self::assertIsString($capturedInlineScript);
        $json = substr($capturedInlineScript, strlen('const publicationSchedule = '));
        self::assertIsString($json);
        $scheduledPublications = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(2, $scheduledPublications);
    }

    public function testAddPublicationScheduleSkipsContentLoadWhenChannelHasNoMappedBrand(): void
    {
        $assetManager = $this->createMock(AssetManager::class);
        $assetManager
            ->expects($this->exactly(2))
            ->method('add');

        $publication = $this->createMock(Publication::class);
        $publication
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn($this->createChannel('unmapped-channel'));
        $publication
            ->expects($this->never())
            ->method('getContent');

        $publicationRepository = $this->createMock(PublicationRepositoryInterface::class);
        $publicationRepository
            ->expects($this->once())
            ->method('forDateRange')
            ->willReturn([$publication]);

        $brand = $this->createMock(Brand::class);
        $brand
            ->expects($this->once())
            ->method('hasChannel')
            ->willReturn(false);

        $brands = $this->createMock(BrandRepository::class);
        $brands
            ->expects($this->once())
            ->method('all')
            ->willReturn([$brand]);

        $documentRepository = $this->createMock(DocumentRepository::class);
        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(File::class)
            ->willReturn($documentRepository);

        $provider = new CalendarPublicationProvider(
            $assetManager,
            $publicationRepository,
            $brands,
            $documentManager,
            $this->createMock(ImageExtension::class),
        );

        $provider->addPublicationSchedule(new CalendarEvent([
            'start' => new \DateTimeImmutable('2026-02-01 00:00:00'),
            'end' => new \DateTimeImmutable('2026-02-28 23:59:59'),
        ]));
    }

    private function createPublication(string $id): Publication
    {
        $content = new Article();
        $content->setId($id);
        $content->setTitle('Demo article '.$id);

        $time = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-10 10:00:00'));

        return new Publication(
            $content,
            $this->createChannel('vismagazine'),
            $time,
            [],
        );
    }

    private function createChannel(string $id): ChannelInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel
            ->method('getId')
            ->willReturn($id);
        $channel
            ->method('getType')
            ->willReturn(new ChannelType('website', 'Website', true, true, null, null, 'www'));

        return $channel;
    }
}
