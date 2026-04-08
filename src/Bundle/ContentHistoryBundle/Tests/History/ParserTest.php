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

    public function testItConsolidatesRelationListWithoutRepeatingPrefixOrDuplicates(): void
    {
        $parser = new Parser();

        $rows = $parser->getReadableChangesetFromArray([
            'relations' => [
                [
                    'relationId' => '__authors',
                    'references' => [[
                        'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person',
                        '_$ref' => 'content',
                        '_$id' => 'c6aef12638e33663848e6a7c5e92e0bb',
                    ]],
                ],
                [
                    'relationId' => '__authors',
                    'references' => [[
                        'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person',
                        '_$ref' => 'content',
                        '_$id' => 'c6aef12638e33663848e6a7c5e92e0bb',
                    ]],
                ],
            ],
        ]);

        self::assertCount(1, $rows);
        self::assertSame('relations', $rows[0]['name']);
        self::assertSame('', $rows[0]['old']);
        self::assertSame(
            'Authors: Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person #c6aef12638e33663848e6a7c5e92e0bb',
            $rows[0]['new']
        );
    }

    public function testItUsesOldOrNewReferenceIdBasedOnTupleSide(): void
    {
        $parser = new Parser();
        $method = new \ReflectionMethod(Parser::class, 'formatReferenceArray');
        $method->setAccessible(true);

        $reference = [
            '$id' => ['old-image-id', 'new-image-id'],
            'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image',
            '$ref' => 'content',
        ];

        $oldFormatted = $method->invoke($parser, $reference, false);
        $newFormatted = $method->invoke($parser, $reference, true);

        self::assertStringContainsString('#old-image-id', $oldFormatted);
        self::assertStringContainsString('#new-image-id', $newFormatted);
    }

    public function testItFormatsMixedRelationListInSingleReadableRow(): void
    {
        $parser = new Parser();

        $rows = $parser->getReadableChangesetFromArray([
            'relations' => [
                [
                    'relationId' => '__featured_image',
                    'references' => [[
                        'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image',
                        '_$ref' => 'content',
                        '_$id' => '735e8b13a39f33c7af4acf2ecaa001e4',
                    ]],
                ],
                [
                    'relationId' => '__authors',
                    'references' => [[
                        'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person',
                        '_$ref' => 'content',
                        '_$id' => 'c6aef12638e33663848e6a7c5e92e0bb',
                    ]],
                ],
            ],
        ]);

        self::assertCount(1, $rows);
        self::assertSame('relations', $rows[0]['name']);
        self::assertStringContainsString('Featured image: Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image #735e8b13a39f33c7af4acf2ecaa001e4', $rows[0]['new']);
        self::assertStringContainsString('Authors: Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person #c6aef12638e33663848e6a7c5e92e0bb', $rows[0]['new']);
    }

    public function testItKeepsOldAndNewForRelationReferenceUpdates(): void
    {
        $parser = new Parser();

        $rows = $parser->getReadableChangesetFromArray([
            'relations' => [
                [
                    'relationId' => ['__featured_image', '__featured_image'],
                    'references' => [
                        [
                            'class' => ['Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image', 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image'],
                            '_$ref' => ['content', 'content'],
                            '_$id' => ['old-image-id', 'new-image-id'],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertCount(1, $rows);
        self::assertSame('relations', $rows[0]['name']);
        self::assertStringContainsString('Featured image:', $rows[0]['old']);
        self::assertStringContainsString('#old-image-id', $rows[0]['old']);
        self::assertStringContainsString('Featured image:', $rows[0]['new']);
        self::assertStringContainsString('#new-image-id', $rows[0]['new']);
    }

    public function testItDoesNotUnserializeObjectPayloads(): void
    {
        $parser = new Parser();

        $rows = $parser->getReadableChangesetFromArray([
            'payload' => 'O:8:"stdClass":0:{}',
            'nested' => 'a:1:{i:0;O:8:"stdClass":0:{}}',
        ]);

        self::assertCount(2, $rows);
        self::assertSame('O:8:"stdClass":0:{}', $rows[0]['new']);
        self::assertSame('a:1:{i:0;O:8:"stdClass":0:{}}', $rows[1]['new']);
    }
}
