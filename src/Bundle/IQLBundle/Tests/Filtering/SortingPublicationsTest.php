<?php

namespace Integrated\Bundle\IQLBundle\Tests\Filtering;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\IQLBundle\Domain\Query;
use Integrated\Bundle\IQLBundle\Specification\WithContent;
use Integrated\Bundle\IQLBundle\Tests\Filtering\Double\Publications;
use Integrated\Common\Test\Fixture\PublicationMother;
use PHPUnit\Framework\TestCase;
use Stratadox\Sorting\Sort;
use Stratadox\Specification\Contract\Specifies;

final class SortingPublicationsTest extends TestCase
{
    private Publications $publications;
    private Specifies $specification;

    protected function setUp(): void
    {
        $publicationMother = new PublicationMother();
        $this->publications = new Publications([
            $publicationMother->rankedTaxonomy('a'),
            $publicationMother->rankedTaxonomy('ab'),
            $publicationMother->rankedTaxonomy('b'),
            $publicationMother->oldArticle(),
            $publicationMother->recentArticle(),
            $publicationMother->oldButNew(),
            $publicationMother->blogByBilbo(),
            $publicationMother->blogByFrodo(),
        ]);
        $this->specification = WithContent::containing('e');
    }

    public function testSortDescendingByWriteDate()
    {
        $results = $this->publications->findBy(new Query($this->specification, Sort::descendingBy('createdAt')));

        self::assertEquals('Rings are tricky', $this->titleOf($results[0]));
        self::assertEquals('Recent Article', $this->titleOf($results[1]));
    }

    public function testSortAscendingByWriteDate()
    {
        $results = $this->publications->findBy(new Query($this->specification, Sort::ascendingBy('createdAt')));

        self::assertEquals('Old Article', $this->titleOf($results[0]));
        self::assertEquals('Old but recently published', $this->titleOf($results[1]));
    }

    public function testSortByPublishDate()
    {
        $results = $this->publications->findBy(new Query($this->specification, Sort::descendingBy('time')));

        self::assertEquals('Rings are tricky', $this->titleOf($results[0]));
        self::assertEquals('Recent Article', $this->titleOf($results[1]));
        self::assertEquals('Old but recently published', $this->titleOf($results[2]));
    }

    public function testSortByUpdateDate()
    {
        $results = $this->publications->findBy(new Query($this->specification, Sort::descendingBy('updatedAt')));

        self::assertEquals('Rings are tricky', $this->titleOf($results[0]));
        self::assertEquals('Old but recently published', $this->titleOf($results[1]));
        self::assertEquals('Recent Article', $this->titleOf($results[2]));
    }

    public function testSortAscendingByTitle()
    {
        $results = $this->publications->findBy(new Query($this->specification, Sort::ascendingBy('title')));

        self::assertEquals('Old Article', $this->titleOf($results[0]));
        self::assertEquals('Old but recently published', $this->titleOf($results[1]));
        self::assertEquals('Recent Article', $this->titleOf($results[2]));
    }

    public function testSortDescendingByTitle()
    {
        $results = $this->publications->findBy(new Query($this->specification, Sort::descendingBy('title')));

        self::assertEquals('There and back again', $this->titleOf($results[0]));
        self::assertEquals('Taxonomy ranked b', $this->titleOf($results[1]));
    }

    public function testSortDescendingByRank()
    {
        $results = $this->publications->findBy(new Query($this->specification, Sort::descendingBy('rank')));

        self::assertEquals('Taxonomy ranked b', $this->titleOf($results[0]));
        self::assertEquals('Taxonomy ranked ab', $this->titleOf($results[1]));
        self::assertEquals('Taxonomy ranked a', $this->titleOf($results[2]));
    }

    public function testSortByMultipleFields()
    {
        $results1 = $this->publications->findBy(new Query(
            $this->specification,
            Sort::ascendingBy('rank')->andThenDescendingBy('time')->andThenAscendingBy('title')
        ));
        $results2 = $this->publications->findBy(new Query(
            $this->specification,
            Sort::ascendingBy('rank')->andThenDescendingBy('time')->andThenDescendingBy('title')
        ));

        // Rank ASC, time DESC, title ASC
        self::assertEquals('Rings are tricky', $this->titleOf($results1[0]));
        self::assertEquals('Old but recently published', $this->titleOf($results1[1]));
        self::assertEquals('Recent Article', $this->titleOf($results1[2]));

        // Rank ASC, time DESC, title DESC
        self::assertEquals('Rings are tricky', $this->titleOf($results2[0]));
        self::assertEquals('Recent Article', $this->titleOf($results2[1]));
        self::assertEquals('Old but recently published', $this->titleOf($results2[2]));
    }

    private function titleOf(Publication $publication): string
    {
        $c = $publication->getContent();
        if (method_exists($c, 'getTitle')) {
            return $c->getTitle();
        }
        return "{$c->getContentType()} #{$c->getId()}";
    }
}
