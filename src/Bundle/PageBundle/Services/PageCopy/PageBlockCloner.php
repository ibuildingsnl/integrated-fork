<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\Proxy;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\ContainerBlock;
use Integrated\Bundle\BlockBundle\Document\Block\ContentItemsBlock;
use Integrated\Bundle\BlockBundle\Document\Block\FeaturedItemsBlock;
use Integrated\Bundle\BlockBundle\Document\Block\HtmlBlock;
use Integrated\Bundle\BlockBundle\Document\Block\InlineTextBlock;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
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
            $source instanceof ContentBlock => $this->cloneContentBlock($source),
            default => $this->cloneCustomBlock($source),
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

    private function cloneContentBlock(ContentBlock $source): ContentBlock
    {
        /** @var ContentBlock $target */
        $target = $this->instantiateSameBlockClass($source);
        $target->setSearchSelection($source->getSearchSelection());
        $target->setItemsPerPage($source->getItemsPerPage());
        $target->setMaxItems($source->getMaxItems());
        $target->setGridSize($source->getGridSize());
        $target->setReadMoreUrl($source->getReadMoreUrl());
        $target->setReadMoreText($source->getReadMoreText());
        $target->setFacetFields($source->getFacetFields());
        $this->copyPublishTitleFields($source, $target);

        return $target;
    }

    private function cloneCustomBlock(Block $source): Block
    {
        $target = $this->instantiateSameBlockClass($source);

        foreach ($this->getCopyableGetterSetterMap($source, $target) as [$getter, $setter]) {
            $target->{$setter}($this->cloneFieldValue($source->{$getter}()));
        }

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

    private function instantiateSameBlockClass(Block $source): Block
    {
        /** @var class-string<Block> $class */
        $class = $this->resolveConcreteBlockClass($source);
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (
            !$reflection->isInstantiable() ||
            ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0)
        ) {
            throw new \InvalidArgumentException(\sprintf('Unsupported block type "%s".', $source::class));
        }

        /** @var Block $target */
        $target = $reflection->newInstance();

        return $target;
    }

    /**
     * @return class-string<Block>
     */
    private function resolveConcreteBlockClass(Block $source): string
    {
        if ($source instanceof Proxy) {
            $parentClass = \get_parent_class($source);
            if (\is_string($parentClass) && \is_a($parentClass, Block::class, true)) {
                return $parentClass;
            }
        }

        return $source::class;
    }

    /**
     * @return list<array{string, string}>
     */
    private function getCopyableGetterSetterMap(object $source, object $target): array
    {
        $pairs = [];
        $excludedProperties = [
            'Id',
            'Type',
            'Page',
            'Title',
            'CssClass',
            'Layout',
            'CreatedAt',
            'UpdatedAt',
            'PublishedAt',
            'PublishedUntil',
            'Disabled',
            'Locked',
            'Groups',
            'Relations',
        ];

        foreach (\get_class_methods($source) as $method) {
            if (\str_starts_with($method, 'get')) {
                $property = \substr($method, 3);
            } elseif (\str_starts_with($method, 'is')) {
                $property = \substr($method, 2);
            } else {
                continue;
            }

            if ($property === '' || \in_array($property, $excludedProperties, true)) {
                continue;
            }

            $setter = 'set'.$property;
            if (!\method_exists($target, $setter)) {
                continue;
            }

            $sourceReflection = new \ReflectionMethod($source, $method);
            $targetReflection = new \ReflectionMethod($target, $setter);

            if ($sourceReflection->getNumberOfRequiredParameters() !== 0 || $targetReflection->getNumberOfParameters() !== 1) {
                continue;
            }

            $pairs[] = [$method, $setter];
        }

        return $pairs;
    }

    private function cloneFieldValue(mixed $value): mixed
    {
        if ($value instanceof \DateTime) {
            return clone $value;
        }

        if ($value instanceof \DateTimeImmutable) {
            return clone $value;
        }

        if (\is_array($value)) {
            return \array_map(fn (mixed $item): mixed => $this->cloneFieldValue($item), $value);
        }

        return $value;
    }
}
