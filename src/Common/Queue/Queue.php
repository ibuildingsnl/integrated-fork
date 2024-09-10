<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Queue;

use Integrated\Common\Queue\Provider\QueueProviderInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Queue implements QueueInterface
{
    /**
     * @var string
     */
    protected $channel;

    /**
     * @var QueueProviderInterface
     */
    protected $provider;

    /**
     * @param string $channel
     */
    public function __construct(QueueProviderInterface $provider, $channel)
    {
        $this->provider = $provider;
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return QueueProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    public function push($payload, $delay = 0, $priority = self::PRIORITY_MEDIUM, $attempt = 0)
    {
        $this->provider->push($this->channel, $payload, $delay, $priority, $attempt);
    }

    public function pull($limit = 1)
    {
        return $this->provider->pull($this->channel, $limit);
    }

    public function count(): int
    {
        return $this->provider->count($this->channel);
    }

    public function clear()
    {
        $this->provider->clear($this->channel);
    }
}
