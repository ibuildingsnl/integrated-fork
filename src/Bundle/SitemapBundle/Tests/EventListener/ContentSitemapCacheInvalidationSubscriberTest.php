<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\SitemapBundle\EventListener\ContentSitemapCacheInvalidationSubscriber;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class ContentSitemapCacheInvalidationSubscriberTest extends TestCase
{
    public function testPostUpdateInvalidatesOldAndNewChannelsAndContentTypes(): void
    {
        $oldChannel = new Channel();
        $oldChannel->setId('old-channel');

        $newChannel = new Channel();
        $newChannel->setId('new-channel');

        $content = new Article();
        $content->setChannels([$newChannel]);
        $content->setPrimaryChannel($newChannel);
        $content->setContentType('new-type');

        $versionManager = new SitemapCacheVersionManager(new ArrayAdapter());
        $subscriber = new ContentSitemapCacheInvalidationSubscriber($versionManager);

        $preUpdateArgs = new PreUpdateEventArgs(
            $content,
            $this->createMock(DocumentManager::class),
            [
                'channels' => [[$oldChannel], [$newChannel]],
                'primaryChannel' => [$oldChannel, $newChannel],
                'contentType' => ['old-type', 'new-type'],
            ]
        );

        $postUpdateArgs = new LifecycleEventArgs(
            $content,
            $this->createMock(DocumentManager::class)
        );

        $subscriber->preUpdate($preUpdateArgs);
        $subscriber->postUpdate($postUpdateArgs);

        self::assertSame(2, $versionManager->getChannelContentVersion('old-channel'));
        self::assertSame(2, $versionManager->getChannelContentVersion('new-channel'));
        self::assertSame(2, $versionManager->getChannelTypeVersion('old-channel', 'old-type'));
        self::assertSame(2, $versionManager->getChannelTypeVersion('old-channel', 'new-type'));
        self::assertSame(2, $versionManager->getChannelTypeVersion('new-channel', 'old-type'));
        self::assertSame(2, $versionManager->getChannelTypeVersion('new-channel', 'new-type'));
    }
}
