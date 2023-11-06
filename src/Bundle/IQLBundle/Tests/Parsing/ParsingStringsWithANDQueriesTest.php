<?php

namespace Integrated\Bundle\IQLBundle\Tests\Parsing;

use Integrated\Bundle\IQLBundle\Parser\IQLParser;
use Integrated\Bundle\IQLBundle\Specification\PublishedAfter;
use Integrated\Bundle\IQLBundle\Specification\PublishedBefore;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenAfter;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Specification\WrittenBy;
use Integrated\Bundle\IQLBundle\Specification\WrittenOn;
use PHPUnit\Framework\TestCase;
use Stratadox\Parser\Parser;

final class ParsingStringsWithANDQueriesTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = IQLParser::create();
    }

    public function testFindByWrittenDateRange()
    {
        self::assertEquals(
            WrittenAfter::date('01-01-2023')->and(WrittenBefore::date('31-12-2023')),
            $this->parser->parse('written between 01-01-2023 and 31-12-2023')->data(),
        );
    }

    public function testFindByPublishDateRange()
    {
        self::assertEquals(
            PublishedAfter::date('01-01-2023')->and(PublishedBefore::date('31-12-2023')),
            $this->parser->parse('published between 01-01-2023 and 31-12-2023')->data(),
        );
    }

    public function testFindByAuthorOnDate()
    {
        self::assertEquals(
            WrittenBy::author('John Doe')->and(WrittenOn::date('01-01-2023')),
            $this->parser->parse('written by John Doe, on 01-01-2023')->data(),
        );
    }

    public function testFindByTypeAuthorAndDate()
    {
        self::assertEquals(
            WithContentType::of('blog')->and(WrittenBy::author('John Doe'))->and(WrittenOn::date('01-01-2023')),
            $this->parser->parse('articles written by John Doe, on 01-01-2023')->data(),
        );
    }

    public function testFindByTypeAuthorAndDateRange()
    {
        self::assertEquals(
            WithContentType::of('blog')
                ->and(WrittenBy::author('John Doe'))
                ->and(WrittenAfter::date('01-01-2023'))
                ->and(WrittenBefore::date('31-12-2023'))
            ,
            $this->parser->parse('blogs written by John Doe, written between 01-01-2023 and 31-12-2023')->data(),
        );
    }
}
