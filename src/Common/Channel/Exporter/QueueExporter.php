<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Exporter;

use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Exporter\Queue\RequestSerializerInterface;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Queue\QueueMessageInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class QueueExporter implements ExporterInterface
{
    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var RequestSerializerInterface
     */
    private $serializer;

    /**
     * @var ExporterInterface
     */
    private $exporter;

    public function __construct(
        QueueInterface $queue,
        RequestSerializerInterface $serializer,
        ExporterInterface $exporter
    ) {
        $this->queue = $queue;
        $this->serializer = $serializer;
        $this->exporter = $exporter;
    }

    /**
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return RequestSerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return ExporterInterface
     */
    public function getExporter()
    {
        return $this->exporter;
    }

    /**
     * Execute a queued exporter run.
     */
    public function execute(): int
    {
        $i = 0;
        foreach ($this->queue->pull(1000) as $message) {
            try {
                $this->process($message)->delete();
            } catch (\Throwable $e) {
                $message->delete();
                throw $e;
            }
            $i++;
        }
        return $i;
    }

    /**
     * @return QueueMessageInterface
     */
    public function process(QueueMessageInterface $message)
    {
        $request = $this->serializer->deserialize($message->getPayload());

        if ($request === null) { // @todo Let serializer throw exception rather than silently returning null
            throw new \InvalidArgumentException('Failed to deserialize the request message.');
        }

        $this->export($request->content, $request->state, $request->channel);

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function export($content, $state, ChannelInterface $channel)
    {
        $this->exporter->export($content, $state, $channel);
    }
}
