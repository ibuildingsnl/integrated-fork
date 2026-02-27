<?php

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;

class PublicationRepositoryTest extends TestCase
{
    public function testForContentByChannelPrefersEditablePublicationOverSuccessfulHistory(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');

        $success = $this->createPublication($content, $channel, Publication::STATUS_SUCCESS, '2026-02-27 12:00:00');
        $failed = $this->createPublication($content, $channel, Publication::STATUS_FAILED, '2026-02-20 12:00:00');

        $repository = $this->createRepository([$success, $failed]);

        $result = $repository->forContentByChannel($content);

        self::assertArrayHasKey('vismagazine', $result);
        self::assertSame($failed, $result['vismagazine']);
    }

    public function testForContentByChannelPrefersMostRecentPublicationWhenStatusIsEqual(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');

        $olderFailed = $this->createPublication($content, $channel, Publication::STATUS_FAILED, '2026-02-20 10:00:00');
        $newerFailed = $this->createPublication($content, $channel, Publication::STATUS_FAILED, '2026-02-27 10:00:00');

        $repository = $this->createRepository([$olderFailed, $newerFailed]);

        $result = $repository->forContentByChannel($content);

        self::assertArrayHasKey('vismagazine', $result);
        self::assertSame($newerFailed, $result['vismagazine']);
    }

    public function testForContentByChannelSkipsPublicationWithoutChannelId(): void
    {
        $content = new Article();
        $validChannel = $this->createChannel('vismagazine');
        $missingChannelId = $this->createChannel(null);

        $validPublication = $this->createPublication($content, $validChannel, Publication::STATUS_FAILED, '2026-02-27 10:00:00');
        $invalidPublication = $this->createPublication($content, $missingChannelId, Publication::STATUS_FAILED, '2026-02-27 09:00:00');

        $repository = $this->createRepository([$invalidPublication, $validPublication]);

        $result = $repository->forContentByChannel($content);

        self::assertCount(1, $result);
        self::assertArrayHasKey('vismagazine', $result);
    }

    /**
     * @param list<Publication> $publications
     */
    private function createRepository(array $publications): PublicationRepository
    {
        $repository = $this->getMockBuilder(PublicationRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forContent'])
            ->getMock();

        $repository
            ->method('forContent')
            ->willReturn($publications);

        return $repository;
    }

    private function createPublication(Article $content, ChannelInterface $channel, string $status, string $startAt): Publication
    {
        $publication = new Publication(
            $content,
            $channel,
            (new PublishTime())->setStartDate(new \DateTimeImmutable($startAt)),
            [],
        );
        $publication->setStatus($status);

        return $publication;
    }

    private function createChannel(?string $id): ChannelInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel
            ->method('getId')
            ->willReturn($id);

        return $channel;
    }
}

