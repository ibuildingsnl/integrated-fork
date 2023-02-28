<?php

namespace Integrated\Bundle\UserBundle\Context;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Common\Content\Channel\ChannelContextInterface;

class ScopeContext
{
    /**
     * @var ChannelContextInterface
     */
    private $context;

    public function __construct(ChannelContextInterface $context)
    {
        $this->context = $context;
    }

    public function getScope(): ?Scope
    {
        if (!$channel = $this->context->getChannel()) {
            return null;
        }

        if (!$channel instanceof Channel) {
            return null;
        }

        return $channel->getScope();
    }
}
