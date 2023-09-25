<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Common\Channel\Exporter\Queue\Request;
use Integrated\Common\Channel\Exporter\Queue\RequestSerializerInterface;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Queue\QueueMessageInterface;

class DistributionQueue implements \Countable
{
    public function __construct(
        private readonly QueueInterface $queue,
        private readonly RequestSerializerInterface $serializer,
    ) {
    }

    public function push(Request $message, int $delayInSeconds): void
    {
        $this->queue->push($this->serializer->serialize($message), $delayInSeconds);
    }

    /** @return Request[] */
    public function pull(int $limit): array
    {
        return array_filter(array_map(function (QueueMessageInterface $message) {
            $request = $this->serializer->deserialize($message->getPayload());
            if ($request instanceof Request) {
                $message->delete();
                return $request;
            }
            return null;
        }, $this->queue->pull($limit)));
    }

    public function count(): int
    {
        return $this->queue->count();
    }
}
