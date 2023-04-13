<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyLister;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOptions;
use Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles\MemoryTaxonomyRepository;
use Integrated\Bundle\UserBundle\Model\User;
use Knp\Component\Pager\Paginator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy;

final class TaxonomyListTest extends TestCase
{
    private TaxonomyLister $list;
    private TaxonomyRepositoryInterface $taxonomies;

    protected function setUp(): void
    {
        $this->taxonomies = new MemoryTaxonomyRepository();
        $this->list = new TaxonomyLister(
            $this->taxonomies,
            new AuthorizationChecker(
                $tokens = new TokenStorage(),
                new AccessDecisionManager([], new AffirmativeStrategy(true)),
            ),
        );
        $tokens->setToken(new PreAuthenticatedToken(new User(), 'main', ['foo']));
    }

    public function testShowingOnlyTheThreeTagsThatFitOnThePage()
    {
        $this->add(
            $this->taxonomy('foo', 'Foo'),
            $this->taxonomy('bar', 'Bar'),
            $this->taxonomy('baz', 'Baz'),
            $this->taxonomy('yolo', 'Yolo'),
        );

        $list = $this->list->buildTaxonomyIndex('tag', TaxonomyOptions::page(1, 3));

        self::assertCount(3, $list);
        self::assertEquals('Bar', $list[0]->getTitle());
        self::assertEquals('Baz', $list[1]->getTitle());
        self::assertEquals('Foo', $list[2]->getTitle());
    }

    public function testShowingOnlyTheFiveTagsThatFitOnThePage()
    {
        $this->add(
            $this->taxonomy('foo', 'Foo'),
            $this->taxonomy('bar', 'Bar'),
            $this->taxonomy('baz', 'Baz'),
            $this->taxonomy('yolo', 'Yolo'),
            $this->taxonomy('tag', 'Tag'),
            $this->taxonomy('abc', 'Abc'),
        );

        $list = $this->list->buildTaxonomyIndex('tag', TaxonomyOptions::page(1, 5));

        self::assertCount(5, $list);
        self::assertEquals('Abc', $list[0]->getTitle());
        self::assertEquals('Bar', $list[1]->getTitle());
        self::assertEquals('Baz', $list[2]->getTitle());
        self::assertEquals('Foo', $list[3]->getTitle());
        self::assertEquals('Tag', $list[4]->getTitle());
    }

    private function add(Taxonomy ...$taxonomies): void
    {
        foreach ($taxonomies as $taxonomy) {
            $this->taxonomies->add($taxonomy);
        }
    }

    private function setUsages(array $usageCounts): void
    {
        if (!$this->taxonomies instanceof MemoryTaxonomyRepository) {
            return;
        }
        foreach ($usageCounts as $id => $count) {
            $this->taxonomies->setUsageCount($id, $count);
        }
    }

    private function taxonomy(
        string $id,
        string $title,
        string $slug = null,
        string $rank = null,
        string $contentType = 'tag'
    ): Taxonomy {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setTitle($title);
        $taxonomy->setSlug($slug ?: $title);
        $taxonomy->setRank($rank);
        $taxonomy->setContentType($contentType);

        return $taxonomy;
    }
}
