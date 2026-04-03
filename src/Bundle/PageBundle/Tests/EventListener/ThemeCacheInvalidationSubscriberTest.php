<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\EventListener;

use Integrated\Bundle\PageBundle\EventListener\ThemeCacheInvalidationSubscriber;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;

class ThemeCacheInvalidationSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [
                Events::CHANNEL_CREATED => 'invalidate',
                Events::CHANNEL_UPDATED => 'invalidate',
                Events::CHANNEL_DELETED => 'invalidate',
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
}
