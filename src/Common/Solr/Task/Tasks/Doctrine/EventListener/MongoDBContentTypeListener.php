<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Task\Tasks\Doctrine\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Common\ContentType\ContentTypeFieldInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Task\Tasks\ContentTypeQueueTask;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
#[AsDocumentListener(event: Events::postUpdate)]
class MongoDBContentTypeListener
{
    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * constructor.
     */
    public function __construct(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof ContentTypeInterface) {
            return;
        }

        if (!$this->shouldQueueContentType($event, $document)) {
            return;
        }

        $this->queue->push(new ContentTypeQueueTask($document->getId()));
    }

    protected function shouldQueueContentType(LifecycleEventArgs $event, ContentTypeInterface $document): bool
    {
        $changeSet = $this->getDocumentChangeSet($event, $document);
        if ($changeSet === []) {
            return true;
        }

        if (array_diff(array_keys($changeSet), ['fields']) !== []) {
            return true;
        }

        if (!isset($changeSet['fields'][0], $changeSet['fields'][1])) {
            return true;
        }

        return !$this->isFormOnlyFieldValueChange($changeSet['fields'][0], $changeSet['fields'][1]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDocumentChangeSet(LifecycleEventArgs $event, ContentTypeInterface $document): array
    {
        try {
            return $event->getDocumentManager()->getUnitOfWork()->getDocumentChangeSet($document);
        } catch (\Throwable) {
            return [];
        }
    }

    private function isFormOnlyFieldValueChange(mixed $beforeFields, mixed $afterFields): bool
    {
        $before = $this->normalizeFields($beforeFields);
        $after = $this->normalizeFields($afterFields);

        if ($before === null || $after === null) {
            return false;
        }

        return $before === $after;
    }

    /**
     * @return array<string, array{name: string, class: string, options: array<string, mixed>}>|null
     */
    private function normalizeFields(mixed $fields): ?array
    {
        if (!\is_array($fields) && !$fields instanceof \Traversable) {
            return null;
        }

        $normalized = [];
        foreach ($fields as $field) {
            if (!$field instanceof ContentTypeFieldInterface) {
                return null;
            }

            $name = trim((string) $field->getName());
            if ($name === '') {
                return null;
            }

            $options = $field->getOptions();
            unset($options['value']);
            ksort($options);

            $normalized[$name] = [
                'name' => $name,
                'class' => $field::class,
                'options' => $options,
            ];
        }

        ksort($normalized);

        return $normalized;
    }
}
