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
    protected EventDispatcherInterface $eventDispatcher;
    /** @var class-string<ContentHistory> */
    protected string $className;

    /**
     * @param class-string<ContentHistory> $className
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, string $className)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->className = $className;
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $this->dispatch($dm, $uow->getScheduledDocumentInsertions(), ContentHistoryEvent::INSERT);
        $this->dispatch($dm, $uow->getScheduledDocumentUpdates(), ContentHistoryEvent::UPDATE);
        $this->dispatch($dm, $uow->getScheduledDocumentDeletions(), ContentHistoryEvent::DELETE);
    }

    /**
     * @param array<int|string, object> $documents
     */
    protected function dispatch(DocumentManager $dm, array $documents, string $action): void
    {
        /** @var class-string<ContentHistory> $historyClass */
        $historyClass = $this->className;
        $classMetadata = $dm->getClassMetadata($historyClass);

        foreach ($documents as $document) {
            if (!$document instanceof ContentInterface) {
                continue;
            }

            $history = new $historyClass($document, $action);
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
     * @return array<string, mixed>
     */
    protected function getOriginalData(DocumentManager $dm, ContentInterface $document, string $action): array
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

        /** @var class-string<ContentHistory> $historyClass */
        $historyClass = $this->className;
        $last = $dm->getRepository($historyClass)->findOneBy(
            ['contentId' => $history->getContentId(), 'action' => ContentHistoryEvent::UPDATE],
            ['date' => 'desc']
        );

        if (!$last instanceof ContentHistory) {
            return false;
        }

        if ($this->shouldMergeWithPreviousUpdate($dm, $last, $history)) {
            return true;
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
            if ($lastUser->getId() !== $currentUser->getId()) {
                return false;
            }

            if ($lastUser->getName() !== $currentUser->getName()) {
                return false;
            }
        }

        return true;
    }

    private function shouldMergeWithPreviousUpdate(DocumentManager $dm, ContentHistory $last, ContentHistory $current): bool
    {
        $lastRequest = $last->getRequest();
        $currentRequest = $current->getRequest();

        $lastRequestId = $lastRequest?->getRequestId();
        $currentRequestId = $currentRequest?->getRequestId();

        if (!\is_string($lastRequestId) || $lastRequestId === '' || !\is_string($currentRequestId) || $currentRequestId === '') {
            return false;
        }

        if ($lastRequestId !== $currentRequestId) {
            return false;
        }

        $merged = $this->mergeChangeSets($last->getChangeSet(), $current->getChangeSet());
        $last->setChangeSet($merged);

        /** @var class-string<ContentHistory> $historyClass */
        $historyClass = $this->className;
        $classMetadata = $dm->getClassMetadata($historyClass);
        $dm->persist($last);
        $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($classMetadata, $last);

        return true;
    }

    /**
     * @param array<string|int, mixed> $base
     * @param array<string|int, mixed> $delta
     *
     * @return array<string|int, mixed>
     */
    private function mergeChangeSets(array $base, array $delta): array
    {
        $merged = $base;

        foreach ($delta as $key => $value) {
            if (\array_key_exists($key, $merged)) {
                $merged[$key] = $this->mergeChangeSetValue($merged[$key], $value);
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }

    private function mergeChangeSetValue(mixed $baseValue, mixed $deltaValue): mixed
    {
        if (!\is_array($baseValue) || !\is_array($deltaValue)) {
            return $deltaValue;
        }

        if ($this->isDiffPair($baseValue) && $this->isDiffPair($deltaValue)) {
            return [$baseValue[0], $deltaValue[1]];
        }

        if ($this->isDiffPair($baseValue) || $this->isDiffPair($deltaValue)) {
            return $deltaValue;
        }

        $merged = $baseValue;

        foreach ($deltaValue as $key => $value) {
            if (\array_key_exists($key, $merged)) {
                $merged[$key] = $this->mergeChangeSetValue($merged[$key], $value);
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }

    /** @param array<string|int, mixed> $value */
    private function isDiffPair(array $value): bool
    {
        return array_keys($value) === [0, 1];
    }
}
