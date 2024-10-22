<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content;

use Integrated\Common\Content\Channel\ChannelInterface;

/**
 * Interface for Content documents with Channels.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
interface ChannelableInterface
{
    /**
     * Return the current channels.
     *
     * @return ChannelInterface[]
     */
    public function getChannels();

    /**
     * Set the current channels.
     *
     * @param ChannelInterface[] $channels
     *
     * @return $this
     */
    public function setChannels(iterable $channels);

    /**
     * Add a channel.
     *
     * @return $this
     */
    public function addChannel(ChannelInterface $channel);

    /**
     * Check if a channel is added.
     *
     * @return bool
     */
    public function hasChannel(ChannelInterface $channel);

    /**
     * Remove a channel.
     *
     * @return $this
     */
    public function removeChannel(ChannelInterface $channel);

    /**
     * Remove all channels.
     *
     * @return $this
     */
    public function removeChannels();
}
