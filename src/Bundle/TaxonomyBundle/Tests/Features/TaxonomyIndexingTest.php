<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexer;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOptions;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles\MemoryTaxonomyRepository;
use Integrated\Bundle\UserBundle\Model\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy;

final class TaxonomyIndexingTest extends TestCase
{
    private TaxonomyOverview $indexer;
    private MemoryTaxonomyRepository $taxonomies;

    protected function setUp(): void
    {
        $this->taxonomies = new MemoryTaxonomyRepository();
        $this->indexer = new TaxonomyIndexer($this->taxonomies, new AuthorizationChecker(
            $tokens = new TokenStorage(),
            new AccessDecisionManager([], new AffirmativeStrategy(true)),
        ));
        $tokens->setToken(new PreAuthenticatedToken(new User(), 'main', ['foo']));
    }

    public function testViewingAnEmptyListWhenThereAreNoTaxonomies()
    {
        $list = $this->indexer->overviewFor('taxonomy');

        self::assertEmpty($list);
    }

    public function testViewingOneItemListWhenThereIsOneTaxonomy()
    {
        $this->add($this->taxonomy('foo', 'One', 'one'));

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertCount(1, $list);
        self::assertInstanceOf(IndexedItem::class, $list[0]);
    }

    public function testSortingItemsByRanking()
    {
        $this->add(
            $this->taxonomy('foo', 'Two', 'two', 'm'),
            $this->taxonomy('bar', 'One', 'one', 'Z'),
        );

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertCount(2, $list);
        self::assertEquals('One', $list[0]->getTitle());
        self::assertEquals('Two', $list[1]->getTitle());
    }

    public function testSortingItemsByRankingAndThenByTitle()
    {
        $this->add(
            $this->taxonomy('baz', 'B', 'b', 'm'),
            $this->taxonomy('foo', 'First', 'first', 'a'),
            $this->taxonomy('bar', 'A', 'a', 'm'),
            $this->taxonomy('qux', 'Last', 'last', 'z'),
        );

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertCount(4, $list);
        self::assertEquals('First', $list[0]->getTitle());
        self::assertEquals('A', $list[1]->getTitle());
        self::assertEquals('B', $list[2]->getTitle());
        self::assertEquals('Last', $list[3]->getTitle());
    }

    public function testSortingItemsByTitleCaseInsensitiveWhenRankMatches(): void
    {
        $this->add(
            $this->taxonomy('parent', 'AutomationNL', 'automationnl'),
            $this->taxonomy('beurzen', 'Beurzen en evenementen', 'beurzen-en-evenementen', null, 'parent'),
            $this->taxonomy('aaaaa', 'aaaaa', 'aaaaa', null, 'parent'),
        );

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertCount(3, $list);
        self::assertSame('AutomationNL', $list[0]->getTitle());
        self::assertSame('aaaaa', $list[1]->getTitle());
        self::assertSame('Beurzen en evenementen', $list[2]->getTitle());
    }

    public function testAddingDepthToAnItemWithParent()
    {
        $this->add(
            $this->taxonomy('bar', 'Child', 'child', null, 'foo'),
            $this->taxonomy('foo', 'Parent', 'parent'),
        );

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertEquals('Parent', $list[0]->getTitle());
        self::assertEquals(0, $list[0]->getDepth());
        self::assertEquals('Child', $list[1]->getTitle());
        self::assertEquals(1, $list[1]->getDepth());
    }

    public function testAddingDeeperDepthToAnItemWithChainOfParents()
    {
        $this->add(
            $this->taxonomy('bar', 'Child', 'child', null, 'foo'),
            $this->taxonomy('baz', 'Grandchild', 'grand-child', null, 'bar'),
            $this->taxonomy('foo', 'Parent', 'parent'),
        );

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertEquals('Parent', $list[0]->getTitle());
        self::assertEquals(0, $list[0]->getDepth());
        self::assertEquals('Child', $list[1]->getTitle());
        self::assertEquals(1, $list[1]->getDepth());
        self::assertEquals('Grandchild', $list[2]->getTitle());
        self::assertEquals(2, $list[2]->getDepth());
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

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertEquals('Parent!', $list[0]->getTitle());
        self::assertEquals(0, $list[0]->getDepth());
        self::assertEquals('Child', $list[1]->getTitle());
        self::assertEquals(1, $list[1]->getDepth());
        self::assertEquals('Grandchild', $list[2]->getTitle());
        self::assertEquals(2, $list[2]->getDepth());
        self::assertEquals('Other Child', $list[3]->getTitle());
        self::assertEquals(1, $list[3]->getDepth());
        self::assertEquals('Without Children', $list[4]->getTitle());
        self::assertEquals(0, $list[4]->getDepth());
    }

    public function testViewingTheUsageCountForEachTaxonomyItem()
    {
        $this->setUsages(['foo' => 0, 'bar' => 1001]);
        $this->add(
            $this->taxonomy('foo', 'Unused', 'unused'),
            $this->taxonomy('bar', 'Many Usages', 'used'),
        );

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertEquals(1001, $list[0]->getCount());
        self::assertEquals(0, $list[1]->getCount());
    }

    public function testBatchUsageLookupIsUsedForOverview(): void
    {
        $this->setUsages(['foo' => 1, 'bar' => 2, 'baz' => 3]);
        $this->add(
            $this->taxonomy('foo', 'Foo'),
            $this->taxonomy('bar', 'Bar'),
            $this->taxonomy('baz', 'Baz'),
        );

        $this->indexer->overviewFor('taxonomy');

        self::assertSame(1, $this->taxonomies->getUsageBatchLookupCalls());
        self::assertSame(0, $this->taxonomies->getUsageLookupCalls());
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

        $list = $this->indexer->overviewFor('taxonomy');

        self::assertEquals('Without Children', $list[0]->getTitle());
        self::assertEquals(16, $list[0]->getCount());
        self::assertEquals(0, $list[0]->getDepth());

        self::assertEquals('Parent!', $list[1]->getTitle());
        self::assertEquals(1, $list[1]->getCount());
        self::assertEquals(0, $list[1]->getDepth());

        self::assertEquals('Other Child', $list[2]->getTitle());
        self::assertEquals(100, $list[2]->getCount());
        self::assertEquals(1, $list[2]->getDepth());

        self::assertEquals('Child', $list[3]->getTitle());
        self::assertEquals(2, $list[3]->getCount());
        self::assertEquals(1, $list[3]->getDepth());

        self::assertEquals('Grandchild', $list[4]->getTitle());
        self::assertEquals(5, $list[4]->getCount());
        self::assertEquals(2, $list[4]->getDepth());

        self::assertEquals('Other Grandchild', $list[5]->getTitle());
        self::assertEquals(52, $list[5]->getCount());
        self::assertEquals(2, $list[5]->getDepth());
    }

    public function testNotShowingTaxonomiesOfDifferentContentType()
    {
        $this->add(
            $this->taxonomy('foo', 'foo', 'foo'),
            $this->taxonomy('bar', 'bar', 'bar'),
            $this->taxonomy('baz', 'baz', 'baz'),
        );

        $list = $this->indexer->overviewFor('different-content-type');

        self::assertEmpty($list);
    }

    public function testOnlyShowingTaxonomiesOfTheChosenContentType()
    {
        $this->add(
            $this->taxonomy('foo', 'foo', 'foo', null, null, 'tag'),
            $this->taxonomy('bar', 'bar', 'bar', null, null, 'tag'),
            $this->taxonomy('baz', 'baz', 'baz', null, null, 'category'),
        );

        $list = $this->indexer->overviewFor('tag');

        self::assertCount(2, $list);
    }

    public function testOnlyShowingTaxonomiesThatFitOnTheFirstPage()
    {
        $this->add(
            $this->taxonomy('1'),
            $this->taxonomy('2'),
            $this->taxonomy('3'),
            $this->taxonomy('4'),
            $this->taxonomy('5'),
        );

        $list = $this->indexer->overviewFor('taxonomy', TaxonomyOptions::page(1, 3));

        self::assertCount(3, $list);
        self::assertEquals('1', $list[0]->getTitle());
    }

    public function testOnlyShowingTaxonomiesThatFitOnTheSecondPage()
    {
        $this->add(
            $this->taxonomy('1'),
            $this->taxonomy('2'),
            $this->taxonomy('3'),
            $this->taxonomy('4'),
            $this->taxonomy('5'),
        );

        $list = $this->indexer->overviewFor('taxonomy', TaxonomyOptions::page(2, 3));

        self::assertCount(2, $list);
        self::assertEquals('4', $list[0]->getTitle());
    }

    public function testFilteringPreventsInfiniteRecursionInParentCycle(): void
    {
        $this->add(
            $this->taxonomy('a', 'A', 'a', null, 'b'),
            $this->taxonomy('b', 'B', 'b', null, 'a'),
        );

        $list = $this->indexer->overviewFor('taxonomy', TaxonomyOptions::filter('a'));

        self::assertCount(2, $list);
        self::assertSame('A', $list[0]->getTitle());
        self::assertSame('B', $list[1]->getTitle());
        self::assertSame(2, $this->indexer->countFor('taxonomy', 'a'));
    }

    private function add(Taxonomy ...$taxonomies): void
    {
        foreach ($taxonomies as $taxonomy) {
            $this->taxonomies->add($taxonomy);
        }
    }

    private function setUsages(array $usageCounts): void
    {
        foreach ($usageCounts as $id => $count) {
            $this->taxonomies->setUsageCount($id, $count);
        }
    }

    private function taxonomy(
        string $id,
        ?string $title = null,
        ?string $slug = null,
        ?string $rank = null,
        ?string $parent = null,
        string $contentType = 'taxonomy',
    ): Taxonomy {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setTitle($title ?? $id);
        $taxonomy->setSlug($slug ?? $title ?? $id);
        $taxonomy->setRank($rank);
        $taxonomy->setParentID($parent);
        $taxonomy->setContentType($contentType);

        return $taxonomy;
    }
}
