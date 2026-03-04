<?php

namespace Integrated\Common\Channel\Exporter;

use Integrated\Common\Channel\Connector\ExporterInterface;
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
        ?\Closure $retryDelay = null,
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
     */
    public function exportMessages(int $limit = 1000): int
    {
        $i = 0;
        foreach ($this->queue->pull($limit) as $message) {
            try {
                $this->process($message)->delete();
            } catch (\Throwable $e) {
                $attempt = $message->getAttempts();

                if ($attempt < $this->maxAttempts) {
                    // In case of e.g. network error, retry processing after a small backoff.
                    $message->release(($this->retryDelay)($attempt, $message));

                    continue;
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

    public function export(object $content, string $state, ChannelInterface $channel, array $settings = []): ?ExporterResponse
    {
        return $this->exporter->export($content, $state, $channel, $settings);
    }
}
