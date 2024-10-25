<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\ChannelBundle\Event\FilterResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConnectorDeletionRedirectListener implements EventSubscriberInterface
{
    public const SESSION_PATH = IntegratedChannelEvents::CONFIG_DELETE_RESPONSE.'.returnUri.%s';

    public static function getSubscribedEvents(): array
    {
        return [
            IntegratedChannelEvents::CONFIG_DELETE_RESPONSE => 'redirect',
        ];
    }

    public function redirect(FilterResponseConfigEvent $event): void
    {
        $key = sprintf(self::SESSION_PATH, $event->getConfig()->getAdapter());
        $session = $event->getRequest()->getSession();

        if ($session->has($key)) {
            $event->setResponse(new RedirectResponse($session->get($key)));
            $session->remove($key);
        }
    }
}
