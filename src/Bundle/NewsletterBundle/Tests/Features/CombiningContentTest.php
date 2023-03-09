<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
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
use Integrated\Bundle\NewsletterBundle\Document\ContentRepository;
use Integrated\Bundle\NewsletterBundle\Service\CombinatorInterface;
use Integrated\Bundle\NewsletterBundle\Service\ContentCombinator;
use Integrated\Bundle\NewsletterBundle\Service\DocumentTypeValidator;
use Integrated\Bundle\NewsletterBundle\Service\Exception\UnacceptableContentTypeException;
use Integrated\Bundle\NewsletterBundle\Service\FeaturedFirstCombinator;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\MemoryContentRepository;
use PHPUnit\Framework\TestCase;
use Stratadox\Sorting\ObjectSorter;

final class CombiningContentTest extends TestCase
{
    private CombinatorInterface $combinator;
    private Channel $channel;
    private Channel $otherChannel;
    private ContentRepository $repository;

    protected function setUp(): void
    {
        $job = $this->contentType('jobposting', JobPosting::class);
        $event = $this->contentType('event', Event::class);
        $article = $this->contentType('article', Article::class);
        $news = $this->contentType('news', News::class);

        $this->channel = $c = $this->channel('default');
        $this->otherChannel = $c2 = $this->channel('channel 2');

        $this->repository = new MemoryContentRepository();

        $this->repository->add($this->content($job, 'global dictator', 1));
        $this->repository->add($this->content($job, 'intergalactic time lord', 30));
        $this->repository->add($this->content($job, 'dishwasher', 12));

        $this->repository->add($this->content($event, 'event 1', 24));
        $this->repository->add($this->content($event, 'event 2', 3));

        $this->repository->add($this->content($article, 'article 1', null));
        $this->repository->add($this->content($article, 'article 2', 22));
        $this->repository->add($this->content($article, 'article 3', 2));

        $this->repository->add($this->content($news, 'something happened', 20, $c));
        $this->repository->add($content = $this->content($news, 'news item was written', 18, $c));
        $content->getCustomFields()->set('ExcludeFromNewsletters', false);
        $this->repository->add($content = $this->content($news, 'news items can now be excluded!', 16, $c));
        $content->getCustomFields()->set('ExcludeFromNewsletters', true);

        $this->repository->add($this->content($news, 'channel 2 news 1', 36, $c2));
        $this->repository->add($this->content($news, 'multi-channel news 1', 35));
        $this->repository->add($this->content($news, 'channel 2 news 2', 34, $c2));
        $this->repository->add($this->content($news, 'multi-channel news 2', 33, $c, $c2));
        $this->repository->add($this->content($news, 'channel 2 news 3', 30, $c2));

        $this->combinator = new FeaturedFirstCombinator(new DocumentTypeValidator(
            new ContentCombinator($this->repository),
            [
                Article::class,
                Event::class,
                JobPosting::class,
                News::class,
                Video::class,
                Company::class,
            ],
        ), new ObjectSorter());
    }

    public function testCombiningAJobPostingAndAnEvent()
    {
        $job = $this->contentType('jobposting', JobPosting::class);
        $event = $this->contentType('event', Event::class);

        $combined = $this->combinator->combine([$job, $event]);

        self::assertCount(2, $combined->getContent());
        self::assertInstanceOf(JobPosting::class, $combined->getContent()[0]);
        self::assertEquals('global dictator', $combined->getContent()[0]->getTitle());
        self::assertInstanceOf(Event::class, $combined->getContent()[1]);
        self::assertEquals('event 2', $combined->getContent()[1]->getTitle());
    }

    public function testCombiningTwoArticles()
    {
        $article = $this->contentType('article', Article::class);

        $combined = $this->combinator->combine([$article, $article]);

        self::assertCount(2, $combined->getContent());
        self::assertInstanceOf(Article::class, $combined->getContent()[0]);
        self::assertEquals('article 3', $combined->getContent()[0]->getTitle());
        self::assertInstanceOf(Article::class, $combined->getContent()[1]);
        self::assertEquals('article 2', $combined->getContent()[1]->getTitle());
    }

    public function testSkippingUnpublishedArticles()
    {
        $article = $this->contentType('article', Article::class);

        $combined = $this->combinator->combine([$article, $article, $article]);

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

        $this->combinator->combine([$image]);
    }

    public function testRefusingUnwantedContentTypesEvenWhenCombinedWithValidOnes()
    {
        $article = $this->contentType('article', Article::class);
        $image = $this->contentType('image', Image::class);

        $this->expectException(UnacceptableContentTypeException::class);

        $this->combinator->combine([$article, $article, $image]);
    }

    public function testSkippingContentThatWasMarkedAsExcluded()
    {
        $news = $this->contentType('news', News::class);

        $combined = $this->combinator->combine([$news, $news]);

        self::assertCount(2, $combined->getContent());
        self::assertInstanceOf(News::class, $combined->getContent()[0]);
        self::assertEquals('news item was written', $combined->getContent()[0]->getTitle());
        self::assertInstanceOf(News::class, $combined->getContent()[1]);
        self::assertEquals('something happened', $combined->getContent()[1]->getTitle());
    }

    public function testLimitingContentToOneChannel()
    {
        $news = $this->contentType('news', News::class);

        $combined = $this->combinator->combine([$news, $news, $news, $news, $news, $news], [$this->channel]);

        self::assertCount(4, $combined->getContent());
        self::assertEquals('news item was written', $combined->getContent()[0]->getTitle());
        self::assertEquals('something happened', $combined->getContent()[1]->getTitle());
        self::assertEquals('multi-channel news 2', $combined->getContent()[2]->getTitle());
        self::assertEquals('multi-channel news 1', $combined->getContent()[3]->getTitle());
    }

    public function testLimitingContentToAnotherChannel()
    {
        $news = $this->contentType('news', News::class);

        $combined = $this->combinator->combine([$news, $news, $news, $news, $news, $news], [$this->otherChannel]);

        self::assertCount(5, $combined->getContent());
        self::assertEquals('channel 2 news 3', $combined->getContent()[0]->getTitle());
        self::assertEquals('multi-channel news 2', $combined->getContent()[1]->getTitle());
        self::assertEquals('channel 2 news 2', $combined->getContent()[2]->getTitle());
        self::assertEquals('multi-channel news 1', $combined->getContent()[3]->getTitle());
        self::assertEquals('channel 2 news 1', $combined->getContent()[4]->getTitle());
    }

    public function testShowingFeaturedContentFirst()
    {
        $news = $this->contentType('news', News::class);
        $featured = $this->content($news, 'featured!', 31);
        $featured->setFeatured(true);
        $this->repository->add($featured);

        $combined = $this->combinator->combine([$news, $news, $news, $news]);

        self::assertCount(4, $combined->getContent());
        self::assertEquals('featured!', $combined->getContent()[0]->getTitle());
        self::assertEquals('news item was written', $combined->getContent()[1]->getTitle());
        self::assertEquals('something happened', $combined->getContent()[2]->getTitle());
        self::assertEquals('channel 2 news 3', $combined->getContent()[3]->getTitle());
    }

    public function testShowingBothFeaturedContentFirst()
    {
        $news = $this->contentType('news', News::class);
        $featured = $this->content($news, 'featured!', 30);
        $featured->setFeatured(true);
        $this->repository->add($featured);
        $featured = $this->content($news, 'featured 2', 29);
        $featured->setFeatured(true);
        $this->repository->add($featured);

        $combined = $this->combinator->combine([$news, $news, $news, $news]);

        self::assertCount(4, $combined->getContent());
        self::assertEquals('featured 2', $combined->getContent()[0]->getTitle());
        self::assertEquals('featured!', $combined->getContent()[1]->getTitle());
        self::assertEquals('news item was written', $combined->getContent()[2]->getTitle());
        self::assertEquals('something happened', $combined->getContent()[3]->getTitle());
    }

    // helpers

    private function contentType(string $type, string $class): ContentType
    {
        $contentType = new ContentType();
        $contentType->setId($type);
        $contentType->setName(ucfirst($type));
        $contentType->setClass($class);

        return $contentType;
    }

    private function content(ContentType $type, string $title, ?int $hoursAgo, Channel ...$channels): Content
    {
        /** @var Content $content */
        $content = $type->create();
        if (null !== $hoursAgo) {
            $t = new PublishTime();
            $t->setStartDate(new \DateTime("now - $hoursAgo hours"));
            $t->setEndDate(new \DateTime('next week'));
            $content->setPublishTime($t);
        }

        foreach ($channels as $channel) {
            $content->addChannel($channel);
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

    private function channel(string $id): Channel
    {
        $channel = new Channel();
        $channel->setId($id);
        return $channel;
    }
}
