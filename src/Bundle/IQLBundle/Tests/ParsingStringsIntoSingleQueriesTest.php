<?php

namespace Integrated\Bundle\IQLBundle\Tests;

use Integrated\Bundle\IQLBundle\Parser\IQLParser;
use Integrated\Bundle\IQLBundle\Specification\PublishedAfter;
use Integrated\Bundle\IQLBundle\Specification\PublishedBefore;
use Integrated\Bundle\IQLBundle\Specification\PublishedOn;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenAfter;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Specification\WrittenBy;
use Integrated\Bundle\IQLBundle\Specification\WrittenOn;
use PHPUnit\Framework\TestCase;
use Stratadox\Parser\Parser;

final class ParsingStringsIntoSingleQueriesTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = IQLParser::create();
    }

    public function testFindByAuthor()
    {
        self::assertEquals(
            WrittenBy::author('John Doe'),
            $this->parser->parse('written by John Doe'),
        );
    }

    public function testFindByDate()
    {
        self::assertEquals(
            WrittenOn::date('01-01-2023'),
            $this->parser->parse('written on 01-01-2023'),
        );
    }

    public function testFindBeforeDate()
    {
        self::assertEquals(
            WrittenBefore::date('01-01-2023'),
            $this->parser->parse('published before 01-01-2023'),
        );
    }

    public function testFindAfterDate()
    {
        self::assertEquals(
            WrittenAfter::date('01-01-2023'),
            $this->parser->parse('published after 01-01-2023'),
        );
    }

    public function testFindByPublicationDate()
    {
        self::assertEquals(
            PublishedOn::date('01-01-2023'),
            $this->parser->parse('published on 01-01-2023'),
        );
    }

    public function testFindBeforePublicationDate()
    {
        self::assertEquals(
            PublishedBefore::date('01-01-2023'),
            $this->parser->parse('published before 01-01-2023'),
        );
    }

    public function testFindAfterPublicationDate()
    {
        self::assertEquals(
            PublishedAfter::date('01-01-2023'),
            $this->parser->parse('published after 01-01-2023'),
        );
    }

    public function testFindByContentType()
    {
        self::assertEquals(
            WithContentType::of('article'),
            $this->parser->parse('articles'),
        );
    }

    // @todo find by:
    // - channel
    // - properties?
    // - relations
    // - text in title/content
}
