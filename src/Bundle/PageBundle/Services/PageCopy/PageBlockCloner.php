<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\ContainerBlock;
use Integrated\Bundle\BlockBundle\Document\Block\ContentItemsBlock;
use Integrated\Bundle\BlockBundle\Document\Block\FeaturedItemsBlock;
use Integrated\Bundle\BlockBundle\Document\Block\HtmlBlock;
use Integrated\Bundle\BlockBundle\Document\Block\InlineTextBlock;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;

final class PageBlockCloner
{
    public function cloneBlock(Block $source, string $newBlockId, AbstractPage $copiedPage): Block
    {
        $target = match (true) {
            $source instanceof InlineTextBlock => $this->cloneInlineTextBlock($source, $copiedPage),
            $source instanceof TextBlock => $this->cloneTextBlock($source),
            $source instanceof HtmlBlock => $this->cloneHtmlBlock($source),
            $source instanceof ContainerBlock => $this->cloneContainerBlock($source),
            $source instanceof ContentItemsBlock => $this->cloneContentItemsBlock($source),
            $source instanceof FeaturedItemsBlock => $this->cloneFeaturedItemsBlock($source),
            default => throw new \InvalidArgumentException(\sprintf('Unsupported block type "%s".', $source::class)),
        };

        $this->copyBaseFields($source, $target, $newBlockId);

        return $target;
    }

    private function cloneInlineTextBlock(InlineTextBlock $source, AbstractPage $copiedPage): InlineTextBlock
    {
        $target = new InlineTextBlock($copiedPage);
        $target->setContent($source->getContent());

        return $target;
    }

    private function cloneTextBlock(TextBlock $source): TextBlock
    {
        $target = new TextBlock();
        $target->setContent($source->getContent());
        $target->setRequiredRelation($source->getRequiredRelation());
        $target->setRequiredItems($source->getRequiredItems());
        $this->copyPublishTitleFields($source, $target);

        return $target;
    }

    private function cloneHtmlBlock(HtmlBlock $source): HtmlBlock
    {
        $target = new HtmlBlock();
        $target->setContent($source->getContent());
        $target->setRequiredRelation($source->getRequiredRelation());
        $target->setRequiredItems($source->getRequiredItems());
        $this->copyPublishTitleFields($source, $target);

        return $target;
    }

    private function cloneContainerBlock(ContainerBlock $source): ContainerBlock
    {
        $target = new ContainerBlock();
        $target->setItems($source->getItems());

        return $target;
    }

    private function cloneContentItemsBlock(ContentItemsBlock $source): ContentItemsBlock
    {
        $target = new ContentItemsBlock();
        $target->setItems($source->getItems());
        $target->setGridSize($source->getGridSize());

        return $target;
    }

    private function cloneFeaturedItemsBlock(FeaturedItemsBlock $source): FeaturedItemsBlock
    {
        $target = new FeaturedItemsBlock();
        $target->setItems($source->getItems());
        $this->copyPublishTitleFields($source, $target);

        return $target;
    }

    private function copyBaseFields(Block $source, Block $target, string $newBlockId): void
    {
        $target->setId($newBlockId);
        $target->setTitle($source->getTitle());
        $target->setCssClass($source->getCssClass());
        $target->setLayout($source->getLayout());
        $target->setCreatedAt(new \DateTime());
        $target->setUpdatedAt(new \DateTime());
        $target->setPublishedAt($this->cloneDateTime($source->getPublishedAt()));
        $target->setPublishedUntil($this->cloneDateTime($source->getPublishedUntil()));
        $target->setDisabled($source->isDisabled());
        $target->setLocked($source->isLocked());
        $target->setGroups($source->getGroups());
        $target->setRelations(new ArrayCollection($source->getRelations()->toArray()));
    }

    private function copyPublishTitleFields(object $source, object $target): void
    {
        if (method_exists($source, 'getPublishedTitle') && method_exists($target, 'setPublishedTitle')) {
            $target->setPublishedTitle($source->getPublishedTitle());
        }

        if (method_exists($source, 'getUseTitle') && method_exists($target, 'setUseTitle')) {
            $target->setUseTitle($source->getUseTitle());
        }
    }

    private function cloneDateTime(?\DateTime $value): ?\DateTime
    {
        return $value === null ? null : clone $value;
    }
}
