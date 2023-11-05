<?php

namespace Integrated\Bundle\IQLBundle\Tests\Filtering;

use Integrated\Bundle\IQLBundle\Domain\Query;
use Integrated\Bundle\IQLBundle\Specification\PublishedAfter;
use Integrated\Bundle\IQLBundle\Specification\PublishedBefore;
use Integrated\Bundle\IQLBundle\Specification\PublishedOn;
use Integrated\Bundle\IQLBundle\Specification\PublishedTo;
use Integrated\Bundle\IQLBundle\Specification\WithContent;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenAfter;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Tests\Filtering\Double\Publications;
use Integrated\Bundle\IQLBundle\Specification\WrittenOn;
use Integrated\Common\Test\Fixture\PublicationMother;
use PHPUnit\Framework\TestCase;

final class FilteringPublicationsTest extends TestCase
{
    private Publications $publications;

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
    }

    public function testFilterPublishedOn()
    {
        $published1634 = $this->publications->findBy(Query::filter(PublishedOn::date('01-01-1634')));
        $published3019 = $this->publications->findBy(Query::filter(PublishedOn::date('25-03-3019')));

        self::assertCount(1, $published1634);
        self::assertCount(1, $published3019);
    }

    public function testFilterPublishedBeforeAfter()
    {
        $publishedBefore = $this->publications->findBy(Query::filter(PublishedBefore::date('01-01-2000')));
        $publishedAfter = $this->publications->findBy(Query::filter(PublishedAfter::date('01-01-2000')));

        self::assertCount(2, $publishedBefore);
        self::assertCount(3, $publishedAfter);
    }

    public function testFilterPublishedTo()
    {
        $newspaper = $this->publications->findBy(Query::filter(PublishedTo::channel('newspaper')));
        $library = $this->publications->findBy(Query::filter(PublishedTo::channel('library')));

        self::assertCount(2, $newspaper);
        self::assertCount(1, $library);
    }

    public function testFilterWrittenOn()
    {
        $written1634 = $this->publications->findBy(Query::filter(WrittenOn::date('01-01-1634')));
        $written3019 = $this->publications->findBy(Query::filter(WrittenOn::date('25-03-3019')));

        self::assertCount(2, $written1634);
        self::assertCount(1, $written3019);
    }

    public function testFilterWrittenBeforeAfter()
    {
        $writtenBefore = $this->publications->findBy(Query::filter(WrittenBefore::date('01-01-2000')));
        $writtenAfter = $this->publications->findBy(Query::filter(WrittenAfter::date('01-01-2000')));

        self::assertCount(3, $writtenBefore);
        self::assertCount(2, $writtenAfter);
    }

    public function testFilterContentType()
    {
        $articles = $this->publications->findBy(Query::filter(WithContentType::of('article')));
        $blogs = $this->publications->findBy(Query::filter(WithContentType::of('blog')));

        self::assertCount(3, $articles);
        self::assertCount(2, $blogs);
    }

    public function testFilterContent()
    {
        $withRecent = $this->publications->findBy(Query::filter(WithContent::containing('recent')));
        $withMountain = $this->publications->findBy(Query::filter(WithContent::containing('mountain')));

        self::assertCount(2, $withRecent);
        self::assertCount(1, $withMountain);
    }
}
