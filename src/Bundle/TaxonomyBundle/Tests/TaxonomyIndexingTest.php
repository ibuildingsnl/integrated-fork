<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexer;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexerInterface;
use Integrated\Bundle\TaxonomyBundle\Services\UsageCounter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TaxonomyIndexingTest extends TestCase
{
    private TaxonomyIndexerInterface $indexer;
    /** @var ObjectRepository&MockObject */
    private ObjectRepository $taxonomies;
    /** @var UsageCounter&MockObject */
    private UsageCounter $usageCounter;
    private bool $usagesDeclared = false;

    protected function setUp(): void
    {
        $this->taxonomies = $this->createMock(ObjectRepository::class);
        $this->usageCounter = $this->createMock(UsageCounter::class);
        $this->indexer = new TaxonomyIndexer(
            $this->taxonomies,
            $this->usageCounter,
        );
    }

    public function testViewingAnEmptyListWhenThereAreNoTaxonomies()
    {
        $this->add();

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertEmpty($list);
    }

    public function testViewingOneItemListWhenThereIsOneTaxonomy()
    {
        $this->add($this->taxonomy('foo', 'One', 'one'));

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertCount(1, $list);
        self::assertInstanceOf(IndexedItem::class, $list[0]);
    }

    public function testSortingItemsByRanking()
    {
        $this->add(
            $this->taxonomy('foo', 'Two', 'two', 'm'),
            $this->taxonomy('bar', 'One', 'one', 'Z'),
        );

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertCount(2, $list);
        self::assertEquals('One', $list[0]->title);
        self::assertEquals('Two', $list[1]->title);
    }

    public function testSortingItemsByRankingAndThenByTitle()
    {
        $this->add(
            $this->taxonomy('baz', 'B', 'b', 'm'),
            $this->taxonomy('foo', 'First', 'first', 'a'),
            $this->taxonomy('bar', 'A', 'a', 'm'),
            $this->taxonomy('qux', 'Last', 'last', 'z'),
        );

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertCount(4, $list);
        self::assertEquals('First', $list[0]->title);
        self::assertEquals('A', $list[1]->title);
        self::assertEquals('B', $list[2]->title);
        self::assertEquals('Last', $list[3]->title);
    }

    public function testAddingDepthToAnItemWithParent()
    {
        $this->add(
            $this->taxonomy('bar', 'Child', 'child', null, 'foo'),
            $this->taxonomy('foo', 'Parent', 'parent'),
        );

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertEquals('Parent', $list[0]->title);
        self::assertEquals(0, $list[0]->depth);
        self::assertEquals('Child', $list[1]->title);
        self::assertEquals(1, $list[1]->depth);
    }

    public function testAddingDeeperDepthToAnItemWithChainOfParents()
    {
        $this->add(
            $this->taxonomy('bar', 'Child', 'child', null, 'foo'),
            $this->taxonomy('baz', 'Grandchild', 'grand-child', null, 'bar'),
            $this->taxonomy('foo', 'Parent', 'parent'),
        );

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertEquals('Parent', $list[0]->title);
        self::assertEquals(0, $list[0]->depth);
        self::assertEquals('Child', $list[1]->title);
        self::assertEquals(1, $list[1]->depth);
        self::assertEquals('Grandchild', $list[2]->title);
        self::assertEquals(2, $list[2]->depth);
    }

    public function testIndexingMultipleChildrenWithGrandchildren()
    {
        $this->add(
            $this->taxonomy('bar', 'Child', 'child', null, 'foo'),
            $this->taxonomy('baz', 'Grandchild', 'grand-child', null, 'bar'),
            $this->taxonomy('foo', 'Parent!', 'parent'),
            $this->taxonomy('qux', 'Other Child', 'other-child', null, 'foo'),
            $this->taxonomy('fred', 'Without Children', 'parent-without-children'),
        );

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertEquals('Parent!', $list[0]->title);
        self::assertEquals(0, $list[0]->depth);
        self::assertEquals('Child', $list[1]->title);
        self::assertEquals(1, $list[1]->depth);
        self::assertEquals('Grandchild', $list[2]->title);
        self::assertEquals(2, $list[2]->depth);
        self::assertEquals('Other Child', $list[3]->title);
        self::assertEquals(1, $list[3]->depth);
        self::assertEquals('Without Children', $list[4]->title);
        self::assertEquals(0, $list[4]->depth);
    }

    public function testViewingTheUsageCountForEachTaxonomyItem()
    {
        $this->setUsages(['foo' => 0, 'bar' => 1001]);
        $this->add(
            $this->taxonomy('foo', 'Unused', 'unused'),
            $this->taxonomy('bar', 'Many Usages', 'used'),
        );

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertEquals(1001, $list[0]->count);
        self::assertEquals(0, $list[1]->count);
    }

    public function testIndexingMultipleChildrenWithRankedGrandchildrenAndUsageCounts()
    {
        $this->setUsages([
            'bar' => 2,
            'baz' => 5,
            'foo' => 1,
            'qux' => 100,
            'fred' => 16,
            'zoo' => 52,
        ]);
        $this->add(
            $this->taxonomy('bar', 'Child', 'child', 'm', 'foo'),
            $this->taxonomy('baz', 'Grandchild', 'grand-child', null, 'bar'),
            $this->taxonomy('foo', 'Parent!', 'parent', 'b'),
            $this->taxonomy('qux', 'Other Child', 'other-child', 'c', 'foo'),
            $this->taxonomy('fred', 'Without Children', 'parent-without-children', 'a'),
            $this->taxonomy('zoo', 'Other Grandchild', 'other-grand-child', 'f', 'bar'),
        );

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertEquals('Without Children', $list[0]->title);
        self::assertEquals(16, $list[0]->count);
        self::assertEquals(0, $list[0]->depth);

        self::assertEquals('Parent!', $list[1]->title);
        self::assertEquals(1, $list[1]->count);
        self::assertEquals(0, $list[1]->depth);

        self::assertEquals('Other Child', $list[2]->title);
        self::assertEquals(100, $list[2]->count);
        self::assertEquals(1, $list[2]->depth);

        self::assertEquals('Child', $list[3]->title);
        self::assertEquals(2, $list[3]->count);
        self::assertEquals(1, $list[3]->depth);

        self::assertEquals('Grandchild', $list[4]->title);
        self::assertEquals(5, $list[4]->count);
        self::assertEquals(2, $list[4]->depth);

        self::assertEquals('Other Grandchild', $list[5]->title);
        self::assertEquals(52, $list[5]->count);
        self::assertEquals(2, $list[5]->depth);
    }

    private function add(Taxonomy ...$taxonomies): void
    {
        $this->taxonomies->method('findAll')->willReturn($taxonomies);
        if (!$this->usagesDeclared) {
            $this->usageCounter->method('countUsages')->willReturn(0);
        }
    }

    private function setUsages(array $usageCounts): void
    {
        $this->usageCounter->method('countUsages')->willReturnCallback(fn (string $id) => $usageCounts[$id] ?? 0);
        $this->usagesDeclared = true;
    }

    private function taxonomy(string $id, string $title, string $slug, string $rank = null, string $parent = null): Taxonomy
    {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setTitle($title);
        $taxonomy->setSlug($slug);
        $taxonomy->setRank($rank);
        $taxonomy->setParentID($parent);

        return $taxonomy;
    }
}
