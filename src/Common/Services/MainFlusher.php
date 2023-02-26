<?php

namespace Integrated\Common\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;

final class MainFlusher implements Flusher
{
    public function __construct(
        private readonly DocumentManager $doctrine,
        private readonly QueueSubscriber $queueSubscriber,
        private readonly IndexerInterface $indexer,
    ) {
    }

    public function flush(): void
    {
        $uow = $this->doctrine->getUnitOfWork();
        $uow->computeChangeSets();

        $contentChanges = \count(array_filter(array_merge(
            $uow->getScheduledDocumentInsertions(),
            $uow->getScheduledDocumentUpserts(),
            $uow->getScheduledDocumentUpdates(),
            $uow->getScheduledDocumentDeletions(),
        ), fn (object $o) => $o instanceof Content));

        $this->queueSubscriber->setPriority(QueueInterface::PRIORITY_HIGH);
        $this->doctrine->flush();
        try {
            $this->indexer->setOption('queue.size', $contentChanges * 2);
            $this->indexer->execute(); // @todo make more reliable
        } catch (\Exception $exception) {
            throw FlushingException::from($exception);
        }
    }
}
