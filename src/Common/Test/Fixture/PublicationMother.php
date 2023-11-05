<?php

namespace Integrated\Common\Test\Fixture;

use Integrated\Bundle\ChannelBundle\Tests\Mock\MemoryPublicationRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Common\Content\Channel\ChannelInterface;

/**
 * An object mother is a kind of class used in testing to help create example objects that you use for testing.
 *
 * @see https://martinfowler.com/bliki/ObjectMother.html
 */
final class PublicationMother
{
    public function __construct(
        private ?ArticleMother $articleMother = null,
    ) {
        if (!$this->articleMother) {
            $this->articleMother = new ArticleMother(new MemoryPublicationRepository());
        }
    }

    public function oldArticle(): Publication
    {
        return $this->article(
            'Old Article',
            'This article is considered old. They say it was written in 1634.',
            ChannelMother::withId('library'),
            PublishTime::withStartDate(new \DateTimeImmutable('01-01-1634')),
            'William Shakespeare',
            "Gandalf the Gray",
        );
    }

    public function recentArticle(): Publication
    {
        return $this->article(
            'Recent Article',
            'This article is considered recent. Newspaper-worthy!',
            ChannelMother::withId('newspaper'),
            PublishTime::withStartDate(new \DateTimeImmutable('01-01-2024')),
            'Chuck Norris',
            "Gandalf the White",
        );
    }

    public function oldButNew(): Publication
    {
        $article = $this->article(
            'Old but recently published',
            'Stuff from way back when',
            ChannelMother::withId('newspaper'),
            PublishTime::withStartDate(new \DateTimeImmutable('01-01-2024')),
            'Chuck Norris',
        );
        $article->getContent()->setCreatedAt(new \DateTimeImmutable('01-01-1634'));
        return $article;
    }

    public function blogByBilbo(): Publication
    {
        return $this->blog(
            'There and back again',
            'So I walked up to this mountain and there was a dragon there.',
            ChannelMother::withId('red_book', 'Red Book'),
            PublishTime::withStartDate(new \DateTimeImmutable('01-05-1937')),
            'Bilbo Baggins',
            'Frodo Baggins',
        );
    }

    public function blogByFrodo(): Publication
    {
        return $this->blog(
            'Rings are tricky',
            'I had this ring, but I dropped it.',
            ChannelMother::withId('lotr'),
            PublishTime::withStartDate(new \DateTimeImmutable('25-03-3019')),
            'Frodo Baggins',
            "Gandalf the Gray",
        );
    }

    public function article(
        string $title,
        string $content,
        ChannelInterface $channel,
        PublishTime $when,
        string ...$authors
    ): Publication {
        $article = $this->articleMother->withChannel($channel);
        $article->setTitle($title);
        $article->setContent($content);
        $article->setCreatedAt($when->getStartDate());
        $article->setPublishTime($when);
        foreach ($authors as $name) {
            // @todo move to AuthorMother?
            $person = new Person();
            if (str_contains($name, ' ')) {
                $person->setFirstName(substr($name, 0, strpos($name, ' ')));
                $person->setLastName(substr($name, strpos($name, ' ')));
            } else {
                $person->setFirstName($name);
                $person->setLastName('');
            }
            $author = new Author();
            $author->setPerson($person);
            $article->addAuthor($author);
        }
        return new Publication($article, $channel, $when);
    }

    public function blog(
        string $title,
        string $content,
        ChannelInterface $channel,
        PublishTime $when,
        string ...$authors
    ): Publication {
        $blog = $this->article($title, $content, $channel, $when, ...$authors);
        $blog->getContent()->setContentType('blog');
        return $blog;
    }
}
