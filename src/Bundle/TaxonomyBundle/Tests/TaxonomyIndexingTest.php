<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TaxonomyIndexingTest extends TestCase
{
    private TaxonomyIndexer $indexer;
    /** @var ObjectRepository&MockObject  */
    private ObjectRepository $taxonomies;

    protected function setUp(): void
    {
        $this->taxonomies = $this->createMock(ObjectRepository::class);
        $this->indexer = new TaxonomyIndexer(
            $this->taxonomies
        );
    }

    /** @test */
    public function viewing_an_empty_list_when_there_are_no_taxonomies()
    {
        $this->add();

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertEmpty($list);
    }

    /** @test */
    public function viewing_a_one_item_list_when_there_is_one_taxonomy()
    {
        $this->add($this->taxonomy('foo', 'One', 'one'));

        $list = $this->indexer->buildTaxonomyIndex();

        self::assertCount(1, $list);
        self::assertInstanceOf(IndexedItem::class, $list[0]);
    }

    /** @test */
    public function sorting_items_by_ranking()
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

    /** @test */
    public function sorting_items_by_ranking_and_then_by_title()
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

    /** @test */
    public function adding_a_depth_to_an_item_with_a_parent()
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

    /** @test */
    public function adding_a_deeper_depth_to_an_item_with_chain_of_parents()
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

    /** @test */
    public function indexing_multiple_children_with_grandchildren()
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

    private function add(Taxonomy ...$taxonomies): void
    {
        $this->taxonomies->method('findAll')->willReturn($taxonomies);
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
