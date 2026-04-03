<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content\Channel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Channel context that will change based on the current request.
 *
 * This context will store the channel in the request object. This will
 * allows separate request to have there own channel. It is not recommended
 * to retrieve the channel directly from the request object as only the
 * channel id is stored.
 *
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RequestAwareChannelContext implements ChannelContextInterface
{
    /**
     * @var ChannelManagerInterface
     */
    private $manager;

    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var string
     */
    private $attribute;

    /**
     * @var string
     */
    private $resolvedAttribute;

    /**
     * @var string
     */
    private $channelAttribute;

    /**
     * @param string $attribute
     */
    public function __construct(ChannelManagerInterface $manager, RequestStack $stack, $attribute = '_channel')
    {
        $this->manager = $manager;
        $this->stack = $stack;
        $this->attribute = $attribute;
        $this->resolvedAttribute = $attribute.'_resolved';
        $this->channelAttribute = $attribute.'_object';
    }

    public function getChannel()
    {
        $request = $this->getRequest();

        if (!$request) {
            return null;
        }

        if ($request->attributes->get($this->resolvedAttribute, false)) {
            return $request->attributes->get($this->channelAttribute);
        }

        if (!$request->attributes->has($this->attribute)) {
            return null;
        }

        $channel = $this->manager->find($request->attributes->get($this->attribute));
        $request->attributes->set($this->resolvedAttribute, true);
        $request->attributes->set($this->channelAttribute, $channel);

        return $channel;
    }

    public function setChannel(?ChannelInterface $channel = null)
    {
        $request = $this->getRequest();

        if (!$request) {
            return; // no request so can not store the channel
        }

        if ($channel) {
            $request->attributes->set($this->attribute, $channel->getId());
            $request->attributes->set($this->resolvedAttribute, true);
            $request->attributes->set($this->channelAttribute, $channel);
        } else {
            $request->attributes->remove($this->attribute);
            $request->attributes->remove($this->resolvedAttribute);
            $request->attributes->remove($this->channelAttribute);
        }
    }

    /**
     * Get the current request object.
     */
    protected function getRequest()
    {
        return $this->stack->getCurrentRequest();
    }
}
