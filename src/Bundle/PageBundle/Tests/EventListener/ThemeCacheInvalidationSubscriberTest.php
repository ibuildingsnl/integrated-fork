<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\EventListener;

use Integrated\Bundle\ChannelBundle\Event\FilterResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\ChannelBundle\Model\Config;
use Integrated\Bundle\PageBundle\EventListener\ThemeCacheInvalidationSubscriber;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThemeCacheInvalidationSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [
                Events::CHANNEL_CREATED => 'invalidate',
                Events::CHANNEL_UPDATED => 'invalidate',
                Events::CHANNEL_DELETED => 'invalidate',
                IntegratedChannelEvents::CONFIG_CREATE_RESPONSE => 'invalidateConfig',
                IntegratedChannelEvents::CONFIG_EDIT_RESPONSE => 'invalidateConfig',
                IntegratedChannelEvents::CONFIG_DELETE_RESPONSE => 'invalidateConfig',
            ],
            ThemeCacheInvalidationSubscriber::getSubscribedEvents()
        );
    }

    public function testInvalidateDelegatesToThemeResolver(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $themeResolver = $this->createMock(ThemeResolver::class);
        $themeResolver
            ->expects($this->once())
            ->method('invalidateForChannel')
            ->with($channel);

        $subscriber = new ThemeCacheInvalidationSubscriber($themeResolver);
        $subscriber->invalidate(new ChannelEvent($channel));
    }

    public function testInvalidateConfigDelegatesForEveryConfiguredChannel(): void
    {
        $config = new Config();
        $config->setChannels(['channel-a', 'channel-b']);
        $invalidatedChannels = [];

        $themeResolver = $this->createMock(ThemeResolver::class);
        $themeResolver
            ->expects($this->exactly(2))
            ->method('invalidateForChannelId')
            ->willReturnCallback(static function (string $channelId) use (&$invalidatedChannels): void {
                $invalidatedChannels[] = $channelId;
            });

        $subscriber = new ThemeCacheInvalidationSubscriber($themeResolver);
        $subscriber->invalidateConfig(new FilterResponseConfigEvent($config, new Request(), new Response()));

        self::assertSame(['channel-a', 'channel-b'], $invalidatedChannels);
    }
}
