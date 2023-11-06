<?php

namespace Integrated\Bundle\IQLBundle\Tests\Parsing;

use Integrated\Bundle\IQLBundle\Parser\IQLParser;
use Integrated\Bundle\IQLBundle\Specification\PublishedAfter;
use Integrated\Bundle\IQLBundle\Specification\PublishedBefore;
use Integrated\Bundle\IQLBundle\Specification\PublishedOn;
use Integrated\Bundle\IQLBundle\Specification\PublishedTo;
use Integrated\Bundle\IQLBundle\Specification\WithContent;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenAfter;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Specification\WrittenBy;
use Integrated\Bundle\IQLBundle\Specification\WrittenOn;
use PHPUnit\Framework\TestCase;
use Stratadox\Parser\Parser;
use Stratadox\Specification\Contract\Specifies;

final class ParsingStringsIntoSingleQueriesTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = IQLParser::create();
    }

    /**
     * @dataProvider parsingProvider
     */
    public function testParse(Specifies $expected, string $iql)
    {
        $result = $this->parser->parse($iql);
        self::assertTrue($result->ok(), "Failed to parse `$iql`: ".($result->ok() ? '' : $result->data()));
        self::assertEqualsWithDelta($expected, $result->data(), 1);
    }

    public static function parsingProvider(): array
    {
        return [
            $iql = 'written by Gandalf' => [WrittenBy::author('Gandalf'), $iql],
            $iql = 'written by John Doe' => [WrittenBy::author('John Doe'), $iql],
            $iql = 'written on 01-01-2023' => [WrittenOn::date('01-01-2023'), $iql],
            $iql = 'written before 01-01-2023' => [WrittenBefore::date('01-01-2023'), $iql],
            $iql = 'written after 01-01-2023' => [WrittenAfter::date('01-01-2023'), $iql],
            $iql = 'written on 04-03-2011' => [WrittenOn::date('04-03-2011'), $iql],
            $iql = 'written before 04-03-2011' => [WrittenBefore::date('04-03-2011'), $iql],
            $iql = 'written after 04-03-2011' => [WrittenAfter::date('04-03-2011'), $iql],
            $iql = 'published on 01-01-2023' => [PublishedOn::date('01-01-2023'), $iql],
            $iql = 'published before 01-01-2023' => [PublishedBefore::date('01-01-2023'), $iql],
            $iql = 'published after 01-01-2023' => [PublishedAfter::date('01-01-2023'), $iql],
            $iql = 'published on 04-03-2011' => [PublishedOn::date('04-03-2011'), $iql],
            $iql = 'published before 04-03-2011' => [PublishedBefore::date('04-03-2011'), $iql],
            $iql = 'published after 04-03-2011' => [PublishedAfter::date('04-03-2011'), $iql],
            $iql = 'articles' => [WithContentType::of('article'), $iql],
            $iql = 'blogs' => [WithContentType::of('blog'), $iql],
            $iql = 'taxonomy items' => [WithContentType::of('taxonomy'), $iql],
            $iql = 'published to channel1' => [PublishedTo::channel('channel1'), $iql],
            $iql = 'published to Channel 2' => [PublishedTo::channel('Channel 2'), $iql],
            $iql = 'containing foo' => [WithContent::containing('foo'), $iql],
            $iql = 'containing "foo bar"' => [WithContent::containing('foo bar'), $iql],
        ];
        // @todo find by:
        // - properties?
        // - relations
    }
}
