<?php

namespace Integrated\Bundle\IQLBundle\Tests\Filtering;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
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
        $newestFirst = $this->publications->findBy(new Query($this->specification, Sort::descendingBy('createdAt')));

        // @todo sorting
        self::assertEquals('Rings are tricky', $this->titleOf($newestFirst[0]));
    }

    private function titleOf(Publication $publication): string
    {
        $c = $publication->getContent();
        if ($c instanceof Article) {
            return $c->getTitle();
        }
        return "{$c->getContentType()} #{$c->getId()}";
    }
}
