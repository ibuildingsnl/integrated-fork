<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
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

final class TaxonomyFilteringTest extends TestCase
{
    private TaxonomyOverview $indexer;
    private TaxonomyRepositoryInterface $taxonomies;

    protected function setUp(): void
    {
        $this->taxonomies = new MemoryTaxonomyRepository();
        $this->indexer = new TaxonomyIndexer($this->taxonomies, new AuthorizationChecker(
            $tokens = new TokenStorage(),
            new AccessDecisionManager([], new AffirmativeStrategy(true)),
        ));
        $tokens->setToken(new PreAuthenticatedToken(new User(), 'main', ['foo']));
    }

    public function testViewingTheRegularListWhenNotFiltering()
    {
        $this->add(
            $this->taxonomy('trees', 'Trees', 'trees'),
            $this->taxonomy('oaks', 'Oaks', 'oaks', null, 'trees'),
            $this->taxonomy('pear', 'Pear', 'pear', null, 'trees'),
            $this->taxonomy('rocks', 'Rocks', 'rocks'),
        );

        $list = $this->indexer->overviewFor('taxonomy', TaxonomyOptions::filter('root'));

        self::assertEquals('Rocks', $list[0]->getTitle());
        self::assertEquals(0, $list[0]->getDepth());
        self::assertEquals('Trees', $list[1]->getTitle());
        self::assertEquals(0, $list[1]->getDepth());
        self::assertEquals('Oaks', $list[2]->getTitle());
        self::assertEquals(1, $list[2]->getDepth());
        self::assertEquals('Pear', $list[3]->getTitle());
        self::assertEquals(1, $list[3]->getDepth());
    }

    public function testOnlyViewingTheChildrenOfTheFilteredItem()
    {
        $this->add(
            $this->taxonomy('trees', 'Trees', 'trees'),
            $this->taxonomy('oaks', 'Oaks', 'oaks', null, 'trees'),
            $this->taxonomy('pear', 'Pear', 'pear', null, 'trees'),
            $this->taxonomy('rocks', 'Rocks', 'rocks'),
        );

        $list = $this->indexer->overviewFor('taxonomy', TaxonomyOptions::filter('trees'));

        self::assertEquals('Trees', $list[0]->getTitle());
        self::assertEquals(0, $list[0]->getDepth());
        self::assertEquals('Oaks', $list[1]->getTitle());
        self::assertEquals(1, $list[1]->getDepth());
        self::assertEquals('Pear', $list[2]->getTitle());
        self::assertEquals(1, $list[2]->getDepth());
    }

    public function testViewingTheGrandChildrenOfTheFilteredItem()
    {
        $this->add(
            $this->taxonomy('trees', 'Trees', 'trees'),
            $this->taxonomy('oaks', 'Oaks', 'oaks', null, 'trees'),
            $this->taxonomy('pear', 'Pear', 'pear', null, 'trees'),
            $this->taxonomy('red oaks', 'Red Oaks', 'red-oaks', null, 'oaks'),
            $this->taxonomy('rocks', 'Rocks', 'rocks'),
        );

        $list = $this->indexer->overviewFor('taxonomy', TaxonomyOptions::filter('trees'));

        self::assertEquals('Trees', $list[0]->getTitle());
        self::assertEquals(0, $list[0]->getDepth());
        self::assertEquals('Oaks', $list[1]->getTitle());
        self::assertEquals(1, $list[1]->getDepth());
        self::assertEquals('Red Oaks', $list[2]->getTitle());
        self::assertEquals(2, $list[2]->getDepth());
        self::assertEquals('Pear', $list[3]->getTitle());
        self::assertEquals(1, $list[3]->getDepth());
    }

    public function testViewingTheGrandChildrenOfANonRootTaxonomy()
    {
        $this->add(
            $this->taxonomy('trees', 'Trees', 'trees'),
            $this->taxonomy('oaks', 'Oaks', 'oaks', null, 'trees'),
            $this->taxonomy('pear', 'Pear', 'pear', null, 'trees'),
            $this->taxonomy('red oaks', 'Red Oaks', 'red-oaks', null, 'oaks'),
            $this->taxonomy('rocks', 'Rocks', 'rocks'),
        );

        $list = $this->indexer->overviewFor('taxonomy', TaxonomyOptions::filter('oaks'));

        self::assertEquals('Oaks', $list[0]->getTitle());
        self::assertEquals(0, $list[0]->getDepth());
        self::assertEquals('Red Oaks', $list[1]->getTitle());
        self::assertEquals(1, $list[1]->getDepth());
    }

    public function testFindingChildrenOfTheRoot()
    {
        $this->add(
            $this->taxonomy('trees', 'Trees', 'trees'),
            $this->taxonomy('oaks', 'Oaks', 'oaks', null, 'trees'),
            $this->taxonomy('pear', 'Pear', 'pear', null, 'trees'),
            $this->taxonomy('red oaks', 'Red Oaks', 'red-oaks', null, 'oaks'),
            $this->taxonomy('rocks', 'Rocks', 'rocks'),
        );

        $rootItems = $this->indexer->childrenOf('taxonomy', 'root');

        self::assertCount(2, $rootItems);
        self::assertEquals('Trees', $rootItems['trees']);
        self::assertEquals('Rocks', $rootItems['rocks']);
    }

    public function testFindingChildrenOfTheRootSortsByRankThenTitle(): void
    {
        $this->add(
            $this->taxonomy('trees', 'Trees', 'trees', 'm'),
            $this->taxonomy('rocks', 'Rocks', 'rocks', 'm'),
            $this->taxonomy('aaaaa', 'aaaaa', 'aaaaa', 'm'),
            $this->taxonomy('first', 'First', 'first', 'a'),
        );

        $rootItems = $this->indexer->childrenOf('taxonomy', 'root');

        self::assertSame(['First', 'aaaaa', 'Rocks', 'Trees'], array_values($rootItems));
    }

    public function testNonExistingElementsDoNotHaveChildren()
    {
        $this->add(
            $this->taxonomy('trees', 'Trees', 'trees'),
            $this->taxonomy('oaks', 'Oaks', 'oaks', null, 'trees'),
            $this->taxonomy('pear', 'Pear', 'pear', null, 'trees'),
            $this->taxonomy('red oaks', 'Red Oaks', 'red-oaks', null, 'oaks'),
            $this->taxonomy('rocks', 'Rocks', 'rocks'),
        );

        $rootItems = $this->indexer->childrenOf('taxonomy', 'groot');

        self::assertCount(0, $rootItems);
    }

    private function add(Taxonomy ...$taxonomies): void
    {
        foreach ($taxonomies as $taxonomy) {
            $this->taxonomies->add($taxonomy);
        }
    }

    private function taxonomy(
        string $id,
        string $title,
        string $slug,
        ?string $rank = null,
        ?string $parent = null,
        string $contentType = 'taxonomy',
    ): Taxonomy {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setTitle($title);
        $taxonomy->setSlug($slug);
        $taxonomy->setRank($rank);
        $taxonomy->setParentID($parent);
        $taxonomy->setContentType($contentType);

        return $taxonomy;
    }
}
