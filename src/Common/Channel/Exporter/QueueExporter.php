<?php

namespace Integrated\Common\Channel\Exporter;

use Integrated\Common\Channel\Exporter\Queue\RequestSerializerInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Queue\QueueMessageInterface;

class QueueExporter implements ExporterInterface, QueueExporterInterface
{
    private \Closure $retryDelay;

    public const CONTENT_REMOVED = 'removed';

    public function __construct(
        private readonly QueueInterface $queue,
        private readonly RequestSerializerInterface $serializer,
        private readonly ExporterInterface $exporter,
        private readonly int $maxAttempts,
        \Closure $retryDelay = null,
    ) {
        $this->retryDelay = $retryDelay ?: fn (int $attempt) => 150 + $attempt * 150;
    }

    public function getQueue(): QueueInterface
    {
        return $this->queue;
    }

    public function getSerializer(): RequestSerializerInterface
    {
        return $this->serializer;
    }

    public function getExporter(): ExporterInterface
    {
        return $this->exporter;
    }

    public function hasMessages(): bool
    {
        return $this->queue->count() > 0;
    }

    /**
     * Execute a queued exporter run.
     * TODO: This removes a queuemessage even though it fails. Shouldn't we keep it in the queue for a retry?
     */
    public function exportMessages(int $limit = 1000): int
    {
        $i = 0;
        foreach ($this->queue->pull($limit) as $message) {
            try {
                $this->process($message)->delete();
            } catch (\Throwable $e) {
                if ($message->getAttempts() < $this->maxAttempts) {
                    // In case of e.g. network error, retry processing in a couple of seconds
                    $this->queue->push(
                        $message->getPayload(),
                        ($this->retryDelay)($message->getAttempts(), $message),
                        $message->getPriority(),
                        $message->getAttempts() + 1,
                    );
                }
                $message->delete();
                throw $e;
            }
            ++$i;
        }

        return $i;
    }

    public function process(QueueMessageInterface $message): QueueMessageInterface
    {
        $request = $this->serializer->deserialize($message->getPayload());

        if ($request === self::CONTENT_REMOVED) {
            return $message;
        }

        if ($request === null) { // @todo Let serializer throw exception rather than silently returning null
            throw new \InvalidArgumentException('Failed to deserialize the request message.');
        }

        $this->export($request->content, $request->state, $request->channel, $request->settings);

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function export($content, $state, ChannelInterface $channel, array $settings = [])
    {
        $this->exporter->export($content, $state, $channel, $settings);
    }
}
