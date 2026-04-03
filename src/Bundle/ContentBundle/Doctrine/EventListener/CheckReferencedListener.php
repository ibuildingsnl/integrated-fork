<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Doctrine\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class CheckReferencedListener.
 *
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
#[AsDocumentListener(event: Events::preRemove)]
class CheckReferencedListener
{
    public function __construct(
        private readonly SearchContentReferenced $searchContentReferenced,
    ) {
    }

    /**
     * @throws AccessDeniedException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        if ($document instanceof Content || $document instanceof SearchSelection) {
            $referenced = $this->searchContentReferenced->getReferenced($document);
            if ($referenced !== []) {
                throw new AccessDeniedException($this->buildMessage($document, $referenced));
            }
        }
    }

    /**
     * @param array<string, array<string, mixed>> $referenced
     */
    private function buildMessage(object $document, array $referenced): string
    {
        $documentLabel = $this->describeDocument($document);
        $blockedBy = [];

        foreach (\array_slice($referenced, 0, 5) as $reference) {
            $name = trim((string) ($reference['name'] ?? ''));
            $id = trim((string) ($reference['id'] ?? ''));

            if ($name !== '' && $id !== '') {
                $blockedBy[] = \sprintf('%s (%s)', $name, $id);
                continue;
            }

            if ($name !== '') {
                $blockedBy[] = $name;
                continue;
            }

            if ($id !== '') {
                $blockedBy[] = $id;
            }
        }

        if ($blockedBy === []) {
            return \sprintf('Cannot remove referenced document %s.', $documentLabel);
        }

        $suffix = \count($referenced) > \count($blockedBy)
            ? \sprintf(' and %d more', \count($referenced) - \count($blockedBy))
            : '';

        return \sprintf(
            'Cannot remove referenced document %s. Blocked by: %s%s.',
            $documentLabel,
            implode(', ', $blockedBy),
            $suffix
        );
    }

    private function describeDocument(object $document): string
    {
        $class = $document::class;
        $id = \is_callable([$document, 'getId']) ? trim((string) $document->getId()) : '';

        if ($document instanceof Content && \is_callable([$document, 'getTitle'])) {
            $title = trim((string) $document->getTitle());
            if ($title !== '' && $id !== '') {
                return \sprintf('%s "%s" (%s)', $class, $title, $id);
            }
            if ($title !== '') {
                return \sprintf('%s "%s"', $class, $title);
            }
        }

        if ($document instanceof SearchSelection && \is_callable([$document, 'getTitle'])) {
            $title = trim((string) $document->getTitle());
            if ($title !== '' && $id !== '') {
                return \sprintf('%s "%s" (%s)', $class, $title, $id);
            }
            if ($title !== '') {
                return \sprintf('%s "%s"', $class, $title);
            }
        }

        if ($id !== '') {
            return \sprintf('%s (%s)', $class, $id);
        }

        return $class;
    }
}
