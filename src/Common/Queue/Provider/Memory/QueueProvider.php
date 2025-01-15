<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Queue\Provider\Memory;

use Integrated\Common\Queue\Provider\QueueProviderInterface;
use Stratadox\Clock\Clock;
use Stratadox\Clock\DateTimeClock;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class QueueProvider implements QueueProviderInterface
{
    private array $queue = [];
    private readonly Clock $clock;

    public function __construct(?Clock $clock = null)
    {
        $this->clock = $clock ?: new DateTimeClock();
    }

    public function push($channel, $payload, $delay = 0, $priority = 0, $attempt = 0)
    {
        // TODO: add priority

        $channel = (string) $channel;
        $timestamp = $this->clock->now()->getTimestamp();

        if (!isset($this->queue[$channel])) {
            $this->queue[$channel] = [];
        }

        $this->queue[$channel][] = new QueueMessage(
            $payload,
            $attempt,
            min(max((int) $priority, -10), 10),
            $timestamp,
            $timestamp,
            $timestamp + $delay,
        );
    }

    public function pull($channel, $limit = 1)
    {
        // this is a in memory queue so delay is ignored.

        $channel = (string) $channel;

        if (!isset($this->queue[$channel])) {
            return [];
        }

        $limit = (int) $limit;
        $limit = $limit > 1 ? $limit : 1;

        return \array_slice($this->currentlyAvailable($channel), 0, $limit);
    }

    public function clear($channel)
    {
        $this->queue[$channel] = [];
    }

    public function count($channel): int
    {
        return \count($this->currentlyAvailable($channel));
    }

    private function currentlyAvailable(string $channel): array
    {
        return array_filter(
            $this->queue[$channel] ?? [],
            fn (QueueMessage $m) => $m->getExecuteAt() <= $this->clock->now()->getTimestamp()
        );
    }
}
