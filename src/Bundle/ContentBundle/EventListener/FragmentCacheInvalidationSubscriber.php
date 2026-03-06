<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Common\Channel\Events as ChannelEvents;
use Integrated\Common\Content\Form\Events as ContentEvents;
use Integrated\Common\ContentType\Events as ContentTypeEvents;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FragmentCacheInvalidationSubscriber implements EventSubscriberInterface
{
    private const CHANNELS_CACHE_NAMESPACE = 'integrated_content_fragments_channels';
    private const NAVDROPDOWNS_CACHE_NAMESPACE = 'integrated_content_fragments_navdropdowns';

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvents::CHANNEL_CREATED => 'onNavigationStructureChanged',
            ChannelEvents::CHANNEL_UPDATED => 'onNavigationStructureChanged',
            ChannelEvents::CHANNEL_DELETED => 'onNavigationStructureChanged',
            ContentTypeEvents::CONTENT_TYPE_CREATED => 'onNavigationStructureChanged',
            ContentTypeEvents::CONTENT_TYPE_UPDATED => 'onNavigationStructureChanged',
            ContentTypeEvents::CONTENT_TYPE_DELETED => 'onNavigationStructureChanged',
            ContentEvents::CONTENT_DISTRIBUTED => 'onAssignedContentChanged',
            ContentEvents::CONTENT_DELETED => 'onAssignedContentChanged',
        ];
    }

    public function onNavigationStructureChanged(object $event): void
    {
        $this->clearNamespace(self::CHANNELS_CACHE_NAMESPACE);
        $this->clearNamespace(self::NAVDROPDOWNS_CACHE_NAMESPACE);
    }

    public function onAssignedContentChanged(object $event): void
    {
        $this->clearNamespace(self::NAVDROPDOWNS_CACHE_NAMESPACE);
    }

    private function clearNamespace(string $namespace): void
    {
        (new FilesystemAdapter($namespace))->clear();
    }
}

