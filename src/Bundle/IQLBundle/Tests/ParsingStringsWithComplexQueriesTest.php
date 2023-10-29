<?php

namespace Integrated\Bundle\IQLBundle\Tests;

use Integrated\Bundle\IQLBundle\Parser\IQLParser;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenAfter;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Specification\WrittenBy;
use Integrated\Bundle\IQLBundle\Specification\WrittenOn;
use PHPUnit\Framework\TestCase;
use Stratadox\Parser\Parser;

final class ParsingStringsWithComplexQueriesTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->markTestSkipped('@todo');
        $this->parser = IQLParser::create();
    }

    public function testFindByAuthorOnDate()
    {
        self::assertEquals(
            WrittenBy::author('John Doe')->and(WrittenOn::date('01-01-2023')),
            $this->parser->parse('written by John Doe on 01-01-2023'),
        );
    }

    public function testFindByTypeAuthorAndDateRange()
    {
        self::assertEquals(
            WithContentType::of('article')
                ->and(WrittenBy::author('John Doe'))
                ->and(WrittenAfter::date('01-01-2023'))
                ->and(WrittenBefore::date('31-12-2023'))
            ,
            $this->parser->parse('blogs written by John Doe between 01-01-2023 and 31-12-2023'),
        );
    }
}
