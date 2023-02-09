<?php

namespace Integrated\Common\Services;

use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;

final class SolrIndexingFlusher implements Flusher
{
    public function __construct(
        private readonly QueueSubscriber $queueSubscriber,
        private readonly IndexerInterface $indexer,
    ) {
    }

    public function flush(): void
    {
        $this->queueSubscriber->setPriority(QueueInterface::PRIORITY_HIGH);
        try {
            $this->indexer->setOption('queue.size', 2);
            $this->indexer->execute(); // @todo make more reliable
        } catch (\Exception $exception) {
            throw FlushingException::from($exception);
        }
    }
}
