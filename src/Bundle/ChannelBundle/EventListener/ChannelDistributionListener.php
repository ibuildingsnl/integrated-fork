<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ChannelBundle\EventListener;

use Integrated\Bundle\ChannelBundle\Services\ChannelDistributor;
use Integrated\Bundle\ContentBundle\Event\ContentDistributedEvent;
use Integrated\Bundle\ContentBundle\Event\ContentDeletedEvent;

class ChannelDistributionListener
{
    public function __construct(
        private readonly ChannelDistributor $distributor
    ) {}

    public function onContentDeleted(ContentDeletedEvent $event): void
    {
        $this->distributor->delete($event->getContent());
    }

    public function onContentDistributed(ContentDistributedEvent $event): void
    {
        $this->distributor->distribute($event->getContent());
    }
}
