<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ChannelBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ChannelBundle\Services\ChannelDistributor;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

/**
 * @todo Move to either custom events or services, rather than triggering business logic in a lifecycle callback
 *
 * @see https://youtu.be/rzGeNYC3oz0?t=1507
 */
class ChannelDistributionListener implements EventSubscriber
{
    public function __construct(
        private readonly ChannelDistributor $distributor
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postRemove,
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof Content) {
            return;
        }

        $this->distributor->delete($document);
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->postUpdate($event);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof Content) {
            return;
        }

        $this->distributor->distribute($document);
    }
}
