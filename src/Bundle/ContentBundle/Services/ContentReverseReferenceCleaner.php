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

    public function cleanup(Content $content): int
    {
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
}
