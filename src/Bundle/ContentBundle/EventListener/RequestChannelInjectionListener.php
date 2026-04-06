<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RequestChannelInjectionListener implements EventSubscriberInterface
{
    /**
     * @var ChannelManagerInterface
     */
    private $manager;

    /**
     * @var ChannelContextInterface
     */
    private $context;

    public function __construct(ChannelManagerInterface $manager, ChannelContextInterface $context)
    {
        $this->manager = $manager;
        $this->context = $context;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 34],
        ];
    }

    public function onRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $channel = $this->getManager()->findByDomain($request->getHost());
        $this->getContext()->setChannel($channel);

        // Safeguard for route conditions that depend on request.attributes._channel.
        // Some runtime paths rely on direct request attributes, not only ChannelContext.
        if ($channel) {
            $request->attributes->set('_channel', $channel->getId());
            $request->attributes->set('_channel_resolved', true);
            $request->attributes->set('_channel_object', $channel);
        } else {
            $request->attributes->remove('_channel');
            $request->attributes->remove('_channel_resolved');
            $request->attributes->remove('_channel_object');
        }

        if ($channel
            && $channel->getPrimaryDomain()
            && strcasecmp($channel->getPrimaryDomain(), $request->getHost()) !== 0
            && $channel->getPrimaryDomainRedirect()
            && $request->getMethod() == 'GET'
        ) {
            $url = $request->getScheme().'://'.$channel->getPrimaryDomain().$request->getRequestUri();
            $event->setResponse(new RedirectResponse($url, \Symfony\Component\HttpFoundation\Response::HTTP_MOVED_PERMANENTLY));
        }
    }

    /**
     * @return ChannelManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return ChannelContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
