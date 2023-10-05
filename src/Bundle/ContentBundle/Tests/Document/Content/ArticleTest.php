<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Location;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ArticleTest extends ContentTest
{
    /**
     * @var Article
     */
    private $article;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->article = new Article();
    }

    /**
     * Test get- and setTitle function.
     */
    public function testGetAndSetTitleFunction()
    {
        $title = 'title';
        $this->article->setTitle($title);
        $this->assertSame($title, $this->article->getTitle());
    }


    /**
     * Test get- and setSubtitle function.
     */
    public function testGetAndSetSubtitleFunction()
    {
        $subtitle = 'subtitle';
        $this->article->setSubtitle($subtitle);
        $this->assertEquals($subtitle, $this->article->getSubtitle());
    }

    /**
     * Test get- and setAuthors function.
     */
    public function testGetAndSetAuthorsFunction()
    {
        $authors = new ArrayCollection(['key' => 'value']);
        $this->article->setAuthors($authors);
        $this->assertSame($authors, $this->article->getAuthors());
    }

    /**
     * Test addAuthor function.
     */
    public function testAddAuthorFunction()
    {
        /* @var $author \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author | MockObject */
        $author = $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author');

        // Action
        $this->article->addAuthor($author);

        // Asserts
        $this->assertCount(1, $this->article->getAuthors());
    }

    /**
     * Test addAuthor function with duplicate author.
     */
    public function testAddAuthorFunctionWithSameAuthor()
    {
        /* @var $author \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author | MockObject */
        $author = $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author');

        // Add author two times
        $this->article->addAuthor($author);
        $this->article->addAuthor($author);

        // Asserts
        $this->assertCount(1, $this->article->getAuthors());
    }

    /**
     * Test removeAuthor function.
     */
    public function testRemoveAuthorFunction()
    {
        /* @var $author \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author | MockObject */
        $author = $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author');

        // Add author
        $this->article->addAuthor($author);

        // Assert
        $this->assertTrue($this->article->removeAuthor($author));
    }

    /**
     * Test removeAuthor function with unknown author.
     */
    public function testRemoveAuthorFunctionWithUnknownAuthor()
    {
        /* @var $author \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author | MockObject */
        $author = $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author');

        // Assert
        $this->assertFalse($this->article->removeAuthor($author));
    }

    /**
     * Test get- and setSource function.
     */
    public function testGetAndSetSourceFunction()
    {
        $source = 'source';
        $this->article->setSource($source);
        $this->assertEquals($source, $this->article->getSource());
    }

    /**
     * Test get- and setSourceUrl function.
     */
    public function testGetAndSetSourceUrlFunction()
    {
        $sourceUrl = 'sourceUrl';
        $this->article->setSourceUrl($sourceUrl);
        $this->assertEquals($sourceUrl, $this->article->getSourceUrl());
    }

    /**
     * Test get- and setLocale function.
     */
    public function testGetAndSetLocaleFunction()
    {
        $locale = 'locale';
        $this->article->setLocale($locale);
        $this->assertEquals($locale, $this->article->getLocale());
    }

    /**
     * Test get- and setIntro function.
     */
    public function testGetAndSetIntroFunction()
    {
        $intro = 'intro';
        $this->article->setIntro($intro);
        $this->assertEquals($intro, $this->article->getIntro());
    }

    /**
     * Test get- and setContent function.
     */
    public function testGetAndSetContentFunction()
    {
        $content = 'content';
        $this->article->setContent($content);
        $this->assertEquals($content, $this->article->getContent());
    }

    /**
     * Test address get- and setLocation function.
     */
    public function testGetAndSetAddressLocationFunction()
    {
        $this->article->setAddress(new Address());

        $location = new Location();

        $this->article->getAddress()->setLocation($location);
        $this->assertSame($location, $this->article->getAddress()->getLocation());
    }

    /**
     * Test toString function.
     */
    public function testToStringFunction()
    {
        $title = 'Title';
        $this->article->setTitle($title);
        $this->assertEquals($title, (string) $this->article);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContent()
    {
        return $this->article;
    }
}
