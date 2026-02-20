<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChannelDeletedListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly DocumentManager $dm,
        private readonly BrandRepository $brands,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CHANNEL_DELETED => 'channelDeleted',
        ];
    }

    public function channelDeleted(ChannelEvent $event): void
    {
        $channelId = $event->getChannel()->getId();

        $dirty = false;
        foreach ($this->brands->all() as $brand) {
            $toRemove = [];
            foreach ($brand->getChannelLinks() as $link) {
                if ($link->channel?->getId() === $channelId) {
                    $toRemove[] = $link;
                }
            }

            if (!$toRemove) {
                continue;
            }

            foreach ($toRemove as $link) {
                $brand->removeChannelLink($link);
            }

            $dirty = true;
        }

        if ($dirty) {
            $this->dm->flush();
        }
    }
}
