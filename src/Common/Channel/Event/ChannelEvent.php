<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Event;

use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class ChannelEvent extends Event
{
    /**
     * @var ChannelInterface
     */
    protected $channel;

    public function __construct(ChannelInterface $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return ChannelInterface
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
