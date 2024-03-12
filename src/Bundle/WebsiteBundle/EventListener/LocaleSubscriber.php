<?php

namespace Integrated\Bundle\WebsiteBundle\EventListener;

use Integrated\Common\Content\Channel\ChannelManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ChannelManagerInterface $manager
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $channel = $this->manager->findByDomain($event->getRequest()->getHost());

        if ($channel) {
            if (strlen($channel->getLanguage()) > 0) {
                $request->setLocale($channel->getLanguage() ?: 'nl');
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => [['onKernelRequest', 20]],
        ];
    }
}
