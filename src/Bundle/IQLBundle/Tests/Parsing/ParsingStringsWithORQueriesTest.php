<?php

namespace Integrated\Bundle\IQLBundle\Tests\Parsing;

use Integrated\Bundle\IQLBundle\Parser\IQLParser;
use Integrated\Bundle\IQLBundle\Specification\PublishedAfter;
use Integrated\Bundle\IQLBundle\Specification\PublishedBefore;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Specification\WrittenBy;
use PHPUnit\Framework\TestCase;
use Stratadox\Parser\Parser;

final class ParsingStringsWithORQueriesTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = IQLParser::create();
    }

    public function testFindByMultipleContentTypes()
    {
        self::assertEquals(
            WithContentType::of('blog')->or(WithContentType::of('comment')),
            $this->parser->parse('blogs or comments')->data(),
        );
    }

    public function testFindByMultipleContentTypesWrittenAsAnd()
    {
        self::assertEquals(
            WithContentType::of('blog')->or(WithContentType::of('comment')),
            $this->parser->parse('blogs and comments')->data(),
        );
    }

    public function testFindByOneOfMultipleAuthors()
    {
        self::assertEquals(
            WrittenBy::author('Bilbo')->or(WrittenBy::author('Frodo')),
            $this->parser->parse('written by Bilbo or written by Frodo')->data(),
        );
    }

    public function testFindByMultipleDates()
    {
        self::assertEquals(
            PublishedBefore::date('01-01-2023')->or(WrittenBefore::date('01-01-2023')),
            $this->parser->parse('published before 01-01-2023 or written before 01-01-2023')->data(),
        );
    }

    public function testFindByMultipleDateRanges()
    {
        self::assertEquals(
            PublishedAfter::date('01-06-2023')->and(PublishedBefore::date('01-07-2023'))->or(
                PublishedAfter::date('01-12-2023')->and(PublishedBefore::date('01-01-2024'))
            ),
            $this->parser->parse('published between 01-06-2023 and 01-07-2023 or published between 01-12-2023 and 01-01-2024')->data(),
        );
    }

    public function testFindByOneOfMultipleAuthorsShortcut()
    {
        self::markTestSkipped('@todo: shortcuts?');
        self::assertEquals(
            WrittenBy::author('Bilbo')->or(WrittenBy::author('Frodo')),
            $this->parser->parse('written by Bilbo or Frodo')->data(),
        );
    }

    public function testFindByMultipleDateRangesShortcut()
    {
        self::markTestSkipped('@todo: shortcuts?');
        self::assertEquals(
            PublishedAfter::date('01-06-2023')->and(PublishedBefore::date('01-07-2023'))->or(
                PublishedAfter::date('01-12-2023')->and(PublishedBefore::date('01-01-2024'))
            ),
            $this->parser->parse('published between 01-06-2023 and 01-07-2023 or between 01-12-2023 and 01-01-2024')->data(),
        );
    }
}
