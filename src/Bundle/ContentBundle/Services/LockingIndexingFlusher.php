<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Services\Exception\StorageException;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Symfony\Component\Lock\LockFactory;

final class LockingIndexingFlusher implements Flusher
{
    public function __construct(
        private readonly QueueSubscriber $queueSubscriber,
        private readonly LockFactory $lockFactory,
        private readonly IndexerInterface $indexer,
    ) {}

    public function flush(): void
    {
        $lock = $this->lockFactory->createLock(self::class);
        $lock->acquire(true);
        $this->queueSubscriber->setPriority(QueueInterface::PRIORITY_HIGH);

        try {
            $this->indexer->setOption('queue.size', 2);
            $this->indexer->execute(); // @todo make more reliable
        } catch (\Exception $exception) {
            throw StorageException::from($exception);
        } finally {
            $lock->release();
        }
    }
}
