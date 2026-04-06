<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Document\Block;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Query\Builder;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Solarium\Core\Query\DocumentInterface;

/**
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class BlockRepository extends ServiceDocumentRepository
{
    private ?bool $hasPagesWithoutBlockIds = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Block::class);
    }

    /**
     * @return array
     */
    public function getTypeChoices(MetadataFactoryInterface $factory, ?array $ids = null)
    {
        $qb = $this->createAggregationBuilder();

        if (null !== $ids) {
            $qb->match()->field('_id')->in($ids);
        }

        $qb->group()
           ->field('_id')
           ->expression('$class')
           ->field('total')
           ->sum(1);

        $groupCountBlock = $qb->getAggregation();

        $typeCount = [];
        foreach ($groupCountBlock as $result) {
            $typeCount[$result['_id']] = $result['total'];
        }

        $typeChoices = [];
        foreach ($factory->getAllMetadata() as $metaData) {
            $class = $metaData->getClass();

            if (\array_key_exists($class, $typeCount) && $typeCount[$class]) {
                $typeChoices[$metaData->getType().' ('.$typeCount[$class].')'] = $class;
            }
        }

        ksort($typeChoices);

        return $typeChoices;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\Query\Query
     */
    public function pagesByBlockQb(Block $block)
    {
        return $this->dm
            ->createQueryBuilder(Page::class)
            ->field('blockIds')->equals($block->getId())
            ->getQuery();
    }

    /**
     * @param array<int, mixed> $ids
     *
     * @return array<int, Block>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $result = $this->createQueryBuilder()
            ->field('_id')->in(array_values($ids))
            ->getQuery()
            ->execute();

        if ($result instanceof \Traversable) {
            return array_values(iterator_to_array($result, false));
        }

        return is_array($result) ? array_values($result) : [];
    }

    /**
     * @return \Doctrine\ODM\MongoDB\Query\Query
     *
     * @internal heavy fallback query for legacy pages without denormalized block ids
     */
    private function legacyPagesByBlockQb(Block $block)
    {
        $blockId = json_encode(
            (string) $block->getId(),
            \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT
        );

        if ($blockId === false) {
            $blockId = '""';
        }

        return $this->dm
            ->createQueryBuilder(Page::class)
            ->where('function() {
                var block_id = '.$blockId.';

                var checkItem = function(item) {
                        if ("block" in item && item.block.$id === block_id) {
                            return true;
                        }

                        if ("row" in item) {
                            if (recursiveFindInRows(item.row)) {
                                return true;
                            }
                        }
                    }

                    var recursiveFindInRows = function(row) {
                        if ("columns" in row) {
                            for (var c in row.columns) {
                                if ("items" in row.columns[c]) {
                                    for (var i in row.columns[c].items) {
                                        if (checkItem(row.columns[c].items[i])) {
                                            return true;
                                        }
                                    }
                                }
                            }
                        }
                    };

                    for (k in this.grids) {
                        for (i in this.grids[k].items) {

                            if (checkItem(this.grids[k].items[i])) {
                                return true;
                            }
                        }
                    }

                    return false;

            }')
            ->getQuery();
    }

    /**
     * Get items which have the current document linked.
     *
     * @param bool $filterPublished
     *
     * @throws \Exception
     */
    public function getUsedBy(ArrayCollection $content, ?Relation $relation = null, ?Content $excludeContent = null, $filterPublished = true): Builder
    {
        if ($excludeContent !== null) {
            $excludeContent = $excludeContent->getId();
        }

        $contentIds = [];
        foreach ($content as $contentItem) {
            if ($contentItem instanceof ContentInterface) {
                if (!$excludeContent) {
                    $excludeContent = $contentItem->getId();
                }

                $contentIds[] = $contentItem->getId();
            }

            if ($contentItem instanceof DocumentInterface) {
                if (property_exists($contentItem, 'type_id')) {
                    if (!$excludeContent) {
                        $excludeContent = $contentItem->type_id;
                    }

                    $contentIds[] = $contentItem->type_id;
                }
            }
        }

        $query = $this->createQueryBuilder()
                      ->field('relations.references.$id')->in($contentIds)
                      ->field('id')->notEqual($excludeContent);

        if ($filterPublished) {
            $query->field('disabled')->equals(false)
                  ->field('publishTime.startDate')->lte(new \DateTime())
                  ->field('publishTime.endDate')->gte(new \DateTime());
        }

        if ($relation) {
            $query->field('relations.relationId')->equals($relation->getId());
        }

        return $query;
    }

    /**
     * Check if given block is used on some page.
     *
     * @return bool
     *
     * @internal heavy query, multiple calls make page slow
     */
    public function isUsed(Block $block)
    {
        if ($this->pagesByBlockQb($block)->getSingleResult()) {
            return true;
        }

        if (!$this->hasPagesWithoutBlockIds()) {
            return false;
        }

        return $this->legacyPagesByBlockQb($block)->getSingleResult() ? true : false;
    }

    private function hasPagesWithoutBlockIds(): bool
    {
        if ($this->hasPagesWithoutBlockIds !== null) {
            return $this->hasPagesWithoutBlockIds;
        }

        $count = $this->dm
            ->createQueryBuilder(Page::class)
            ->field('blockIds')->exists(false)
            ->count()
            ->getQuery()
            ->execute();

        $this->hasPagesWithoutBlockIds = is_numeric($count) && (int) $count > 0;

        return $this->hasPagesWithoutBlockIds;
    }
}
