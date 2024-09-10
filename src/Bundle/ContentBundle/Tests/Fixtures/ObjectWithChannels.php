<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Fixtures;

use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\ChannelableInterface;

class ObjectWithChannels implements ChannelableInterface
{
    private array $channels;

    public function __construct(array $channels = [])
    {
        $this->channels = $channels;
    }

    public function getChannels()
    {
        return $this->channels;
    }

    public function setChannels(iterable $channels)
    {
        throw new \LogicException();
    }

    public function addChannel(ChannelInterface $channel)
    {
        throw new \LogicException();
    }

    public function hasChannel(ChannelInterface $channel)
    {
        throw new \LogicException();
    }

    public function removeChannel(ChannelInterface $channel)
    {
        throw new \LogicException();
    }

    public function removeChannels()
    {
        throw new \LogicException();
    }
}
