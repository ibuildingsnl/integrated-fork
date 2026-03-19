<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Services\ContentTypeInformation;
use PHPUnit\Framework\TestCase;

final class ContentTypeInformationTest extends TestCase
{
    public function testGetSitemapAllowedContentTypesUsesDefaultsAndOverrides(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())
            ->method('findAll')
            ->willReturn([
                $this->createContentType('article'),
                $this->createContentType('file'),
                $this->createContentType('image', ['sitemap' => 'enabled']),
                $this->createContentType('video', ['sitemap' => 'disabled']),
                $this->createContentType('restricted', ['channels' => ['restricted' => ['channel-a']]]),
                $this->createContentType('publication_disabled', ['publication' => 'disabled', 'sitemap' => 'enabled']),
            ]);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::once())
            ->method('getRepository')
            ->with(ContentType::class)
            ->willReturn($repository);

        $service = new ContentTypeInformation($documentManager);

        self::assertSame(
            ['article', 'image', 'restricted'],
            $service->getSitemapAllowedContentTypes('channel-a', ['file', 'image', 'comment'])
        );
    }

    public function testGetPublishingAllowedContentTypesRespectsChannelAndPublicationRestrictions(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())
            ->method('findAll')
            ->willReturn([
                $this->createContentType('article'),
                $this->createContentType('disabled_channel', ['channels' => ['disabled' => 2]]),
                $this->createContentType('restricted', ['channels' => ['restricted' => ['channel-a']]]),
                $this->createContentType('publication_disabled', ['publication' => 'disabled']),
            ]);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::once())
            ->method('getRepository')
            ->with(ContentType::class)
            ->willReturn($repository);

        $service = new ContentTypeInformation($documentManager);

        self::assertSame(
            ['article', 'restricted'],
            $service->getPublishingAllowedContentTypes('channel-a')
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createContentType(string $id, array $options = []): ContentType
    {
        return (new ContentType())
            ->setId($id)
            ->setName(ucfirst($id))
            ->setClass('App\\Content\\'.ucfirst($id))
            ->setOptions($options);
    }
}
