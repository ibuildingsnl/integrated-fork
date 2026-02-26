<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\ChannelBundle\Event\GetResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ExternalReturnRedirectListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            IntegratedChannelEvents::CONFIG_EDIT_REQUEST => ['handleExternalReturn', -100],
        ];
    }

    public function handleExternalReturn(GetResponseConfigEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $id = $session->get('externalReturnId');
        $config = $event->getConfig();

        if ($id !== $config->getId() || !$session->has('postReturnUri')) {
            return;
        }

        $returnUri = $session->get('postReturnUri');
        $session->remove('externalReturnId');
        $session->remove('postReturnUri');
        $event->setResponse(new RedirectResponse($returnUri));
    }
}
