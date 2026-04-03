<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\ContentItemsBlock;
use Integrated\Bundle\BlockBundle\Document\Block\Embedded\Relation as BlockRelation;
use Integrated\Bundle\BlockBundle\Document\Block\HtmlBlock;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\CommentBundle\Document\Comment;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\RelationAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\BulkAction;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation as ContentRelation;

final class ContentReverseReferenceCleaner
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly SearchContentReferenced $searchContentReferenced,
    ) {
    }

    public function cleanup(Content $content, bool $flush = true): int
    {
        if (!$flush) {
            return $this->cleanupWithoutFlush($content)['changed'];
        }

        $changed = 0;

        foreach ($this->searchContentReferenced->getReferencedDocuments($content) as $document) {
            if ($document instanceof Content) {
                $changed += $this->cleanupContentRelations($document, $content);
                continue;
            }

            if ($document instanceof Block) {
                $changed += $this->cleanupBlockRelations($document, $content);
                continue;
            }

            if ($document instanceof BulkAction) {
                $changed += $this->cleanupBulkAction($document, $content);
                continue;
            }

            if ($document instanceof Comment) {
                $this->documentManager->remove($document);
                ++$changed;
            }
        }

        if ($changed > 0) {
            $this->documentManager->flush();
        }

        return $changed;
    }

    /**
     * @return array{changed: int, rollback: \Closure(): void}
     */
    public function cleanupWithoutFlush(Content $content): array
    {
        $changed = 0;
        $rollbacks = [];

        try {
            foreach ($this->searchContentReferenced->getReferencedDocuments($content) as $document) {
                if ($document instanceof Content) {
                    $rollbacks[] = $this->snapshotContentRelations($document);
                    $changed += $this->cleanupContentRelations($document, $content);

                    continue;
                }

                if ($document instanceof Block) {
                    $rollbacks[] = $this->snapshotBlock($document);
                    $changed += $this->cleanupBlockRelations($document, $content);

                    continue;
                }

                if ($document instanceof BulkAction) {
                    $rollbacks[] = $this->snapshotBulkAction($document);
                    $changed += $this->cleanupBulkAction($document, $content);

                    continue;
                }

                if ($document instanceof Comment) {
                    $rollbacks[] = $this->snapshotCommentRemoval($document);
                    $this->documentManager->remove($document);
                    ++$changed;
                }
            }
        } catch (\Throwable $exception) {
            $this->runRollbacks($rollbacks);

            throw $exception;
        }

        return [
            'changed' => $changed,
            'rollback' => function () use ($rollbacks): void {
                $this->runRollbacks($rollbacks);
            },
        ];
    }

    private function cleanupContentRelations(Content $document, Content $target): int
    {
        $changed = 0;

        foreach ($document->getRelations() as $relation) {
            if (!$relation instanceof ContentRelation) {
                continue;
            }

            if ($this->removeReferenceFromContentRelation($relation, $target)) {
                if (count($relation->getReferences()) === 0) {
                    $document->removeRelation($relation);
                }

                ++$changed;
            }
        }

        if ($changed > 0) {
            $this->documentManager->persist($document);
        }

        return $changed;
    }

    private function cleanupBlockRelations(Block $document, Content $target): int
    {
        $changed = 0;

        foreach ($document->getRelations() as $relation) {
            if (!$relation instanceof BlockRelation) {
                continue;
            }

            if ($relation->removeReference($target)) {
                if (count($relation->getReferences()) === 0) {
                    $document->removeRelation($relation);
                }

                ++$changed;
            }
        }

        if ($document instanceof ContentItemsBlock) {
            $items = $this->filterOutContent($document->getItems(), $target);
            if (count($items) !== count($document->getItems())) {
                $document->setItems($items);
                ++$changed;
            }
        }

        if ($document instanceof HtmlBlock) {
            $requiredItems = $this->filterOutContent($document->getRequiredItems(), $target);
            if (count($requiredItems) !== count($document->getRequiredItems())) {
                $document->setRequiredItems($requiredItems);
                ++$changed;
            }
        }

        if ($document instanceof TextBlock) {
            $requiredItems = $this->filterOutContent($document->getRequiredItems(), $target);
            if (count($requiredItems) !== count($document->getRequiredItems())) {
                $document->setRequiredItems($requiredItems);
                ++$changed;
            }
        }

        if ($changed > 0) {
            $this->documentManager->persist($document);
        }

        return $changed;
    }

    private function cleanupBulkAction(BulkAction $document, Content $target): int
    {
        $changed = 0;

        $selection = $document->getSelection();
        if (count($selection) > count($this->filterOutContent($selection, $target))) {
            $document->removeSelection($target);
            ++$changed;
        }

        foreach ($document->getActions() as $action) {
            if (!$action instanceof RelationAction) {
                continue;
            }

            $references = iterator_to_array($action->getReferences());
            if (count($references) > count($this->filterOutContent($references, $target))) {
                $action->removeReference($target);
                ++$changed;
            }
        }

        if ($changed > 0) {
            $this->documentManager->persist($document);
        }

        return $changed;
    }

    /**
     * @param iterable<Content> $contents
     *
     * @return array<int, Content>
     */
    private function filterOutContent(iterable $contents, Content $target): array
    {
        $filtered = [];
        $targetId = (string) $target->getId();

        foreach ($contents as $content) {
            if (!$content instanceof Content) {
                continue;
            }

            if ((string) $content->getId() === $targetId) {
                continue;
            }

            $filtered[] = $content;
        }

        return $filtered;
    }

    private function removeReferenceFromContentRelation(ContentRelation $relation, Content $target): bool
    {
        foreach ($relation->getReferences() as $reference) {
            if ((string) $reference->getId() !== (string) $target->getId()) {
                continue;
            }

            return $relation->removeReference($reference);
        }

        return false;
    }

    /**
     * @return \Closure(): void
     */
    private function snapshotContentRelations(Content $document): \Closure
    {
        $relations = $this->cloneRelationCollection((array) $document->getRelations());

        return function () use ($document, $relations): void {
            $this->writeObjectProperty($document, 'relations', new ArrayCollection($relations));
        };
    }

    /**
     * @return \Closure(): void
     */
    private function snapshotBlock(Block $document): \Closure
    {
        $relations = $this->cloneRelationCollection($document->getRelations()->toArray());
        $items = $document instanceof ContentItemsBlock ? $document->getItems() : null;
        $requiredItems = $document instanceof HtmlBlock || $document instanceof TextBlock ? $document->getRequiredItems() : null;

        return function () use ($document, $relations, $items, $requiredItems): void {
            $this->writeObjectProperty($document, 'relations', new ArrayCollection($relations));

            if ($document instanceof ContentItemsBlock && $items !== null) {
                $document->setItems($items);
            }

            if (($document instanceof HtmlBlock || $document instanceof TextBlock) && $requiredItems !== null) {
                $document->setRequiredItems($requiredItems);
            }
        };
    }

    /**
     * @return \Closure(): void
     */
    private function snapshotBulkAction(BulkAction $document): \Closure
    {
        $selection = $document->getSelection();
        $references = [];

        foreach ($document->getActions() as $action) {
            if (!$action instanceof RelationAction) {
                continue;
            }

            $references[spl_object_id($action)] = iterator_to_array($action->getReferences());
        }

        return function () use ($document, $selection, $references): void {
            $document->setSelection($selection);

            foreach ($document->getActions() as $action) {
                if (!$action instanceof RelationAction) {
                    continue;
                }

                $actionId = spl_object_id($action);
                if (!isset($references[$actionId])) {
                    continue;
                }

                $action->setReferences($references[$actionId]);
            }
        };
    }

    /**
     * @return \Closure(): void
     */
    private function snapshotCommentRemoval(Comment $document): \Closure
    {
        return function () use ($document): void {
            $this->documentManager->persist($document);
        };
    }

    /**
     * @param array<int, object> $relations
     *
     * @return array<int, object>
     */
    private function cloneRelationCollection(array $relations): array
    {
        return array_map(function (object $relation): object {
            $snapshot = clone $relation;

            if (method_exists($relation, 'getReferences')) {
                $this->writeObjectProperty(
                    $snapshot,
                    'references',
                    new ArrayCollection(iterator_to_array($relation->getReferences()))
                );
            }

            return $snapshot;
        }, $relations);
    }

    /**
     * @param array<int, \Closure(): void> $rollbacks
     */
    private function runRollbacks(array $rollbacks): void
    {
        for ($i = count($rollbacks) - 1; $i >= 0; --$i) {
            $rollbacks[$i]();
        }
    }

    private function writeObjectProperty(object $document, string $property, mixed $value): void
    {
        $reflection = new \ReflectionObject($document);

        do {
            if ($reflection->hasProperty($property)) {
                $reflectionProperty = $reflection->getProperty($property);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($document, $value);

                return;
            }

            $reflection = $reflection->getParentClass();
        } while ($reflection !== false);
    }
}
