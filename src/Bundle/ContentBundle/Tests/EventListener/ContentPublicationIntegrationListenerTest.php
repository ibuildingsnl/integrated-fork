<?php

namespace Integrated\Bundle\ContentBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\EventListener\ContentPublicationIntegrationListener;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;

class ContentPublicationIntegrationListenerTest extends TestCase
{
    private ContentPublicationIntegrationListener $listener;

    protected function setUp(): void
    {
        $this->listener = new ContentPublicationIntegrationListener(
            $this->createMock(PublicationRepositoryInterface::class),
            $this->createMock(DocumentManager::class),
        );
    }

    public function testIsPublicationChangedIgnoresEquivalentSettingsWithDifferentKeyOrder(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');
        $time = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00'));
        $publication = new Publication($content, $channel, $time, [
            'caption' => 'A short intro',
            'meta' => [
                'title' => 'Demo',
                'description' => 'Same values, different order',
            ],
        ]);

        $changed = $this->invokeIsPublicationChanged($publication, [
            'time' => (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00')),
            'settings' => [
                'meta' => [
                    'description' => 'Same values, different order',
                    'title' => 'Demo',
                ],
                'caption' => 'A short intro',
            ],
        ]);

        self::assertFalse($changed);
    }

    public function testIsPublicationChangedDetectsNestedSettingsDifferences(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');
        $time = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00'));
        $publication = new Publication($content, $channel, $time, [
            'meta' => [
                'title' => 'Original title',
            ],
        ]);

        $changed = $this->invokeIsPublicationChanged($publication, [
            'time' => (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00')),
            'settings' => [
                'meta' => [
                    'title' => 'Updated title',
                ],
            ],
        ]);

        self::assertTrue($changed);
    }

    public function testIsPublicationChangedIgnoresEquivalentPublishTimeAcrossTimeZones(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');
        $utcTime = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27T10:00:00+00:00'));
        $publication = new Publication($content, $channel, $utcTime, []);

        $cetTime = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27T11:00:00+01:00'));
        $changed = $this->invokeIsPublicationChanged($publication, [
            'time' => $cetTime,
            'settings' => [],
        ]);

        self::assertFalse($changed);
    }

    /**
     * @param array{time?: mixed, settings?: mixed} $data
     */
    private function invokeIsPublicationChanged(Publication $publication, array $data): bool
    {
        $method = new \ReflectionMethod(ContentPublicationIntegrationListener::class, 'isPublicationChanged');
        $method->setAccessible(true);

        return $method->invoke($this->listener, $publication, $data);
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

