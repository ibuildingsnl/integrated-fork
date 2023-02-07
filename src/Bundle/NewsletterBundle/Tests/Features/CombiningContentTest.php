<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Event;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\JobPosting;
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Video;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Service\CombinatorInterface;
use Integrated\Bundle\NewsletterBundle\Service\ContentCombinator;
use Integrated\Bundle\NewsletterBundle\Service\DocumentTypeValidator;
use Integrated\Bundle\NewsletterBundle\Service\Exception\UnacceptableContentTypeException;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\MemoryContentRepository;
use PHPUnit\Framework\TestCase;

final class CombiningContentTest extends TestCase
{
    private CombinatorInterface $combinator;

    protected function setUp(): void
    {
        $job = $this->contentType('jobposting', JobPosting::class);
        $event = $this->contentType('event', Event::class);
        $article = $this->contentType('article', Article::class);

        $repository = new MemoryContentRepository();

        $repository->add($this->content($job, 'global dictator', 1));
        $repository->add($this->content($job, 'intergalactic time lord', 30));
        $repository->add($this->content($job, 'dishwasher', 12));

        $repository->add($this->content($event, 'event 1', 24));
        $repository->add($this->content($event, 'event 2', 3));

        $repository->add($this->content($article, 'article 1', null));
        $repository->add($this->content($article, 'article 2', 22));
        $repository->add($this->content($article, 'article 3', 2));

        $this->combinator = new DocumentTypeValidator(
            new ContentCombinator($repository),
            Article::class,
            Event::class,
            JobPosting::class,
            News::class,
            Video::class,
            Company::class,
        );
    }

    public function testCombiningAJobPostingAndAnEvent()
    {
        $job = $this->contentType('jobposting', JobPosting::class);
        $event = $this->contentType('event', Event::class);

        $combined = $this->combinator->combine($job, $event);

        self::assertCount(2, $combined->getContent());
        self::assertInstanceOf(JobPosting::class, $combined->getContent()[0]);
        self::assertEquals('global dictator', $combined->getContent()[0]->getTitle());
        self::assertInstanceOf(Event::class, $combined->getContent()[1]);
        self::assertEquals('event 2', $combined->getContent()[1]->getTitle());
    }

    public function testCombiningTwoArticles()
    {
        $article = $this->contentType('article', Article::class);

        $combined = $this->combinator->combine($article, $article);

        self::assertCount(2, $combined->getContent());
        self::assertInstanceOf(Article::class, $combined->getContent()[0]);
        self::assertEquals('article 3', $combined->getContent()[0]->getTitle());
        self::assertInstanceOf(Article::class, $combined->getContent()[1]);
        self::assertEquals('article 2', $combined->getContent()[1]->getTitle());
    }

    public function testSkippingUnpublishedArticles()
    {
        $article = $this->contentType('article', Article::class);

        $combined = $this->combinator->combine($article, $article, $article);

        self::assertCount(2, $combined->getContent());
        self::assertInstanceOf(Article::class, $combined->getContent()[0]);
        self::assertEquals('article 3', $combined->getContent()[0]->getTitle());
        self::assertInstanceOf(Article::class, $combined->getContent()[1]);
        self::assertEquals('article 2', $combined->getContent()[1]->getTitle());
    }

    public function testRefusingUnwantedContentTypes()
    {
        $image = $this->contentType('image', Image::class);

        $this->expectException(UnacceptableContentTypeException::class);

        $this->combinator->combine($image);
    }

    public function testRefusingUnwantedContentTypesEvenWhenCombinedWithValidOnes()
    {
        $article = $this->contentType('article', Article::class);
        $image = $this->contentType('image', Image::class);

        $this->expectException(UnacceptableContentTypeException::class);

        $this->combinator->combine($article, $article, $image);
    }

    private function contentType(string $type, string $class): ContentType
    {
        $contentType = new ContentType();
        $contentType->setId($type);
        $contentType->setName(ucfirst($type));
        $contentType->setClass($class);

        return $contentType;
    }

    private function content(ContentType $type, string $title, ?int $hoursAgo): Content
    {
        /** @var Content $content */
        $content = $type->create();
        if (null !== $hoursAgo) {
            $t = new PublishTime();
            $t->setStartDate(new \DateTime("now - $hoursAgo hours"));
            $t->setEndDate(new \DateTime('next week'));
            $content->setPublishTime($t);
        }
        if ($content instanceof Article) {
            $content->setTitle($title);
        }
        if ($content instanceof File) {
            $content->setTitle($title);
        }
        if ($content instanceof Company) {
            $content->setName($title);
        }

        return $content;
    }
}
