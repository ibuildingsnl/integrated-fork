<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentHistoryBundle\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ContentHistoryBundle\Document\ContentHistory;
use Integrated\Bundle\ContentHistoryBundle\Event\ContentHistoryEvent;
use Integrated\Common\Content\ContentInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
#[AsDocumentListener(event: Events::onFlush)]
class ContentHistorySubscriber
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var string
     */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $className)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->className = $className;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $this->dispatch($dm, $uow->getScheduledDocumentInsertions(), ContentHistoryEvent::INSERT);
        $this->dispatch($dm, $uow->getScheduledDocumentUpdates(), ContentHistoryEvent::UPDATE);
        $this->dispatch($dm, $uow->getScheduledDocumentDeletions(), ContentHistoryEvent::DELETE);
    }

    /**
     * @param string $action
     */
    protected function dispatch(DocumentManager $dm, array $documents, $action)
    {
        $classMetadata = $dm->getClassMetadata($this->className);

        foreach ($documents as $document) {
            if (!$document instanceof ContentInterface) {
                continue;
            }

            $history = new $this->className($document, $action);
            $originalData = $this->getOriginalData($dm, $document, $action);

            $this->eventDispatcher->dispatch(new ContentHistoryEvent($history, $document, $originalData), $action);

            if (\count($history->getChangeSet())) {
                if ($this->shouldSkipDuplicateUpdate($dm, $history, $action)) {
                    continue;
                }

                $dm->persist($history);
                $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($classMetadata, $history);
            }
        }
    }

    /**
     * @param string $action
     *
     * @return array
     */
    protected function getOriginalData(DocumentManager $dm, ContentInterface $document, $action)
    {
        if ($action == ContentHistoryEvent::INSERT) {
            return [];
        }

        return (array) $dm->createQueryBuilder($document::class)->hydrate(false)
            ->field('id')->equals($document->getId())
            ->getQuery()->getSingleResult();
    }

    private function shouldSkipDuplicateUpdate(DocumentManager $dm, object $history, string $action): bool
    {
        if ($action !== ContentHistoryEvent::UPDATE || !$history instanceof ContentHistory) {
            return false;
        }

        $last = $dm->getRepository($this->className)->findOneBy(
            ['contentId' => $history->getContentId(), 'action' => ContentHistoryEvent::UPDATE],
            ['date' => 'desc']
        );

        if (!$last instanceof ContentHistory) {
            return false;
        }

        if ($last->getChangeSet() !== $history->getChangeSet()) {
            return false;
        }

        $secondsBetween = abs($history->getDate()->getTimestamp() - $last->getDate()->getTimestamp());
        if ($secondsBetween > 2) {
            return false;
        }

        $lastRequest = $last->getRequest();
        $currentRequest = $history->getRequest();
        if ($lastRequest !== null && $currentRequest !== null && $lastRequest->getEndpoint() !== $currentRequest->getEndpoint()) {
            return false;
        }

        $lastUser = $last->getUser();
        $currentUser = $history->getUser();
        if ($lastUser !== null && $currentUser !== null) {
            if ($lastUser->getId() !== null && $currentUser->getId() !== null && $lastUser->getId() !== $currentUser->getId()) {
                return false;
            }

            if ($lastUser->getName() !== null && $currentUser->getName() !== null && $lastUser->getName() !== $currentUser->getName()) {
                return false;
            }
        }

        return true;
    }
}
