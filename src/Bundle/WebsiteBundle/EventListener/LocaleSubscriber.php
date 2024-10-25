<?php

namespace Integrated\Bundle\WebsiteBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
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

        if (!str_contains($request->getPathInfo(), '/admin/')) {
            $channel = $this->manager->findByDomain($event->getRequest()->getHost());

            if ($channel) {
                if ($channel instanceof Channel && $channel->getLanguage() !== '') {
                    if ($channel instanceof Channel) {
                        $request->setLocale($channel->getLanguage());
                    }
                }
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
