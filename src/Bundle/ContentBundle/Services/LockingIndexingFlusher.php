<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Symfony\Component\Lock\LockFactory;

final class LockingIndexingFlusher implements Flusher
{
    public function __construct(
        private readonly ObjectManager $doctrine,
        private readonly QueueSubscriber $queueSubscriber,
        private readonly LockFactory $lockFactory,
        private readonly IndexerInterface $indexer,
    ) {
    }

    public function flush(): void
    {
        try {
            $this->doctrine->flush();

            $lock = $this->lockFactory->createLock(ContentController::class);
            $lock->acquire(true);
        } catch (\Exception $exception) {
            throw FlushingException::from($exception);
        }

        $this->queueSubscriber->setPriority(QueueInterface::PRIORITY_HIGH);
        try {
            $this->indexer->setOption('queue.size', 2);
            $this->indexer->execute(); // @todo make more reliable
        } catch (\Exception $exception) {
            throw FlushingException::from($exception);
        } finally {
            $lock->release();
        }
    }
}
