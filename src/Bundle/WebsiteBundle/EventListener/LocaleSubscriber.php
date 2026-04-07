<?php

namespace Integrated\Bundle\WebsiteBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ChannelContextInterface|ChannelManagerInterface $context,
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_contains($request->getPathInfo(), '/admin/')) {
            $channel = $this->context instanceof ChannelContextInterface
                ? $this->context->getChannel()
                : $this->context->findByDomain($request->getHost());

            if ($channel instanceof Channel && $channel->getLanguage() !== '') {
                $request->setLocale($channel->getLanguage());
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [['onKernelRequest', 20]],
        ];
    }
}
