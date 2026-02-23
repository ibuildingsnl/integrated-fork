<?php

namespace Integrated\Bundle\ContentHistoryBundle\Tests\History;

use Integrated\Bundle\ContentHistoryBundle\History\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public function testItFormatsRelationPayloadWithoutRawJsonNoise(): void
    {
        $parser = new Parser();

        $rows = $parser->getReadableChangesetFromArray([
            'relations' => [
                0 => [
                    'relationId' => ['__featured_image', '__featured_image'],
                    'relationType' => [null, 'embedded'],
                    'references' => [
                        [
                            'class' => [null, 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image'],
                            '_$ref' => [null, 'content'],
                            '_$id' => [null, '735e8b13a39f33c7af4acf2ecaa001e4'],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertNotEmpty($rows);
        self::assertSame('relations', $rows[0]['name']);
        self::assertStringContainsString('Featured image:', $rows[0]['new']);
        self::assertStringContainsString('Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image #735e8b13a39f33c7af4acf2ecaa001e4', $rows[0]['new']);
        self::assertStringNotContainsString('{"relationId"', $rows[0]['new']);
    }

    public function testItHumanizesRelationIdFieldValues(): void
    {
        $parser = new Parser();

        $rows = $parser->getReadableChangesetFromArray([
            'relationId' => ['__featured_image', '__featured_image'],
        ]);

        self::assertCount(1, $rows);
        self::assertSame('relationId', $rows[0]['name']);
        self::assertSame('Featured image', $rows[0]['old']);
        self::assertSame('Featured image', $rows[0]['new']);
    }
}
