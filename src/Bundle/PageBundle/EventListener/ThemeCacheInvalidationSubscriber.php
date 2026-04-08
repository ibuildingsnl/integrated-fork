<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\EventListener;

use Integrated\Bundle\ChannelBundle\Event\FilterResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ThemeCacheInvalidationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ThemeResolver $themeResolver)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CHANNEL_CREATED => 'invalidate',
            Events::CHANNEL_UPDATED => 'invalidate',
            Events::CHANNEL_DELETED => 'invalidate',
            IntegratedChannelEvents::CONFIG_CREATE_RESPONSE => 'invalidateConfig',
            IntegratedChannelEvents::CONFIG_EDIT_RESPONSE => 'invalidateConfig',
            IntegratedChannelEvents::CONFIG_DELETE_RESPONSE => 'invalidateConfig',
        ];
    }

    public function invalidate(ChannelEvent $event): void
    {
        $this->themeResolver->invalidateForChannel($event->getChannel());
    }

    public function invalidateConfig(FilterResponseConfigEvent $event): void
    {
        foreach ($event->getConfig()->getChannels() as $channelId) {
            $this->themeResolver->invalidateForChannelId((string) $channelId);
        }
    }
}
