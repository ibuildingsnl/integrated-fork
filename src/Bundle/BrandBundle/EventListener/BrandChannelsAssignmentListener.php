<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BrandChannelsAssignmentListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => ['addBrandChannels', 100],
        ];
    }

    public function addBrandChannels(FormEvent $event): void
    {
        if ($event->getForm()->has('channels')) {
            return;
        }
        $content = $event->getData();
        if (!$content instanceof ChannelableInterface) {
            return;
        }

        $brands = $event->getForm()->get('brands')->getData();
        foreach ($brands ?? [] as $brand) {
            if ($brand['publish'] ?? false) {
                // @todo instead merge and set as collection (enforced channels are added afterwards)
                /** @var ChannelLink $channelLink */
                foreach ($brand['channels'] ?? [] as $channelLink) {
                    if ($this->authorizationChecker->isGranted(PermissionInterface::WRITE, $channelLink->channel)) {
                        $content->addChannel($channelLink->channel);
                    }
                }
            }
        }
    }
}
