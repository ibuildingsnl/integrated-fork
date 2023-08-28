<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BrandChannelsAssignmentListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // @todo make it a pre-submit, to integrate with channel permission listener?
            FormEvents::POST_SUBMIT => ['addBrandChannels', 100],
        ];
    }

    public function addBrandChannels(FormEvent $event): void
    {
        if ($event->getForm()->has('channels')) {
            return;
        }
        $content = $event->getData();
        if (!$content instanceof Content) {
            return;
        }

        $brands = $event->getForm()->get('brands')->getData();
        foreach ($brands ?? [] as $brand) {
            if ($brand['publish'] ?? false) {
                // @todo instead merge with enforced channels and set as collection
                /** @var ChannelLink $channelLink */
                foreach ($brand['channels'] ?? [] as $channelLink) {
                    $content->addChannel($channelLink->channel);
                }
            }
        }
    }
}
