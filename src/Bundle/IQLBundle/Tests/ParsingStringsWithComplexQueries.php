<?php

namespace Integrated\Bundle\IQLBundle\Tests;

use Integrated\Bundle\IQLBundle\Parser\IQLParser;
use Integrated\Bundle\IQLBundle\WithContentType;
use PHPUnit\Framework\TestCase;
use Stratadox\Parser\Parser;

final class ParsingStringsWithComplexQueries extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
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
            WithContentType::of('article')->and(
                WrittenBy::author('John Doe'),
                WrittenAfter::date('01-01-2023'),
                WrittenBefore::date('31-12-2023'),
            ),
            $this->parser->parse('blogs written by John Doe between 01-01-2023 and 31-12-2023'),
        );
    }
}
