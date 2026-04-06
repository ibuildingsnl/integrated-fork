<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\SitemapBundle\EventListener\PageSitemapCacheInvalidationSubscriber;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class PageSitemapCacheInvalidationSubscriberTest extends TestCase
{
    public function testPostUpdateInvalidatesOldAndNewChannelPageVersions(): void
    {
        $oldChannel = new Channel();
        $oldChannel->setId('old-channel');

        $newChannel = new Channel();
        $newChannel->setId('new-channel');

        $page = new Page();
        $page->setChannel($newChannel);

        $versionManager = new SitemapCacheVersionManager(new ArrayAdapter());
        $subscriber = new PageSitemapCacheInvalidationSubscriber($versionManager);

        $preUpdateArgs = new PreUpdateEventArgs(
            $page,
            $this->createMock(DocumentManager::class),
            ['channel' => [$oldChannel, $newChannel]]
        );

        $postUpdateArgs = new LifecycleEventArgs(
            $page,
            $this->createMock(DocumentManager::class)
        );

        $subscriber->preUpdate($preUpdateArgs);
        $subscriber->postUpdate($postUpdateArgs);

        self::assertSame(2, $versionManager->getChannelPagesVersion('old-channel'));
        self::assertSame(2, $versionManager->getChannelPagesVersion('new-channel'));
    }
}
