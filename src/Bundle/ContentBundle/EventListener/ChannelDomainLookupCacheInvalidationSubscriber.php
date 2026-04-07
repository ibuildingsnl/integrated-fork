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

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ContentBundle\Doctrine\ChannelManager;
use Integrated\Common\Content\Channel\ChannelInterface;

class ChannelDomainLookupCacheInvalidationSubscriber implements EventSubscriber
{
    public function __construct(private readonly ChannelManager $channelManager)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->invalidateIfChannel($args->getDocument());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->invalidateIfChannel($args->getDocument());
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->invalidateIfChannel($args->getDocument());
    }

    private function invalidateIfChannel(object $document): void
    {
        if ($document instanceof ChannelInterface) {
            $this->channelManager->invalidateDomainLookupCache();
        }
    }
}
