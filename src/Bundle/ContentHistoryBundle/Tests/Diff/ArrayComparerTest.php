<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentHistoryBundle\Tests\Diff;

use Integrated\Bundle\ContentHistoryBundle\Diff\ArrayComparer;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class ArrayComparerTest extends \PHPUnit\Framework\TestCase
{
    public function testAddField()
    {
        $this->assertDiff(
            [], // old
            ['key' => 'value'], // new
            ['key' => [null, 'value']] // expected
        );

        // Check multidimensional
        $this->assertDiff(
            ['address' => []], // old
            ['address' => ['key' => 'value']], // new
            ['address' => ['key' => [null, 'value']]] // expected
        );
    }

    public function testRemoveField()
    {
        $this->assertDiff(
            ['key2' => 'value2'], // old
            [], // new
            ['key2' => ['value2', null]] // expected
        );

        // Check multidimensional
        $this->assertDiff(
            ['address' => ['key2' => 'value2']], // old
            ['address' => []], // new
            ['address' => ['key2' => ['value2', null]]] // expected
        );

        $this->assertDiff(
            ['address' => ['key2' => 'value2']], // old
            ['address' => ['key2' => null]], // new
            ['address' => ['key2' => ['value2', null]]] // expected
        );
    }

    public function testUpdateValue()
    {
        $this->assertDiff(
            ['key3' => 'value3', 'key4' => 'unchanged'], // old
            ['key3' => 'value4', 'key4' => 'unchanged'], // new
            ['key3' => ['value3', 'value4']] // expected
        );

        // Check multidimensional
        $this->assertDiff(
            ['address' => ['key3' => 'value3', 'key4' => 'unchanged']], // old
            ['address' => ['key3' => 'value4', 'key4' => 'unchanged']], // new
            ['address' => ['key3' => ['value3', 'value4']]] // expected
        );
    }

    public function testEmptyValue()
    {
        $this->assertDiff(
            [], // old
            ['key5' => []], // new
            [] // expected
        );

        $this->assertDiff(
            [], // old
            ['key6' => null], // new
            [] // expected
        );

        $this->assertDiff(
            ['key7' => null], // old
            [], // new
            [] // expected
        );

        // Check multidimensional
        $this->assertDiff(
            [], // old
            ['address' => ['key5' => []]], // new
            [] // expected
        );

        $this->assertDiff(
            [], // old
            ['address' => ['key6' => null]], // new
            [] // expected
        );

        $this->assertDiff(
            ['address' => ['key7' => null]], // old
            [], // new
            [] // expected
        );

        $this->assertDiff(
            ['address' => ['name' => 'Name1']], // old
            ['address' => ['key7' => null, 'name' => 'Name1']], // new
            [] // expected
        );

        $this->assertDiff(
            ['address' => ['key7' => null, 'name' => 'Name1']], // old
            ['address' => ['name' => 'Name2']], // new
            ['address' => ['name' => ['Name1', 'Name2']]] // expected
        );
    }

    public function testNormalize()
    {
        $this->assertNormalize(
            ['title' => 'name', 'address' => 'key3', 'key4' => 'unchanged'], // old
            ['type' => 'key3'], // new
            ['type' => 'key3', 'title' => null, 'address' => null, 'key4' => null] // expected
        );
    }

    public function testRelationsAreComparedByStableRelationAndReferenceKeys(): void
    {
        $old = [
            'relations' => [
                2 => [
                    'relationId' => '__featured_image',
                    'relationType' => 'embedded',
                    'references' => [
                        ['class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image', '_$id' => 'img-old'],
                    ],
                ],
                3 => [
                    'relationId' => '__authors',
                    'relationType' => 'author',
                    'references' => [
                        ['class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person', '_$id' => 'person-1'],
                    ],
                ],
            ],
        ];

        $new = [
            'relations' => [
                1 => [
                    'relationId' => 'file',
                    'relationType' => 'embedded',
                    'references' => [
                        ['class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image', '_$id' => 'img-new'],
                    ],
                ],
                2 => [
                    'relationId' => '__featured_image',
                    'relationType' => 'embedded',
                    'references' => [
                        ['class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image', '_$id' => 'img-new'],
                    ],
                ],
                3 => [
                    'relationId' => '__authors',
                    'relationType' => null,
                    'references' => [],
                ],
            ],
        ];

        $diff = ArrayComparer::diff($old, $new);

        self::assertArrayHasKey('relations', $diff);
        self::assertArrayHasKey('__featured_image', $diff['relations']);
        self::assertArrayHasKey('file', $diff['relations']);
        self::assertArrayHasKey('__authors', $diff['relations']);
        self::assertArrayHasKey(
            'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image#img-new',
            $diff['relations']['__featured_image']['references']
        );
        self::assertArrayNotHasKey(
            'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person#person-1',
            $diff['relations']['__featured_image']['references']
        );
    }

    protected function assertDiff(array $old = [], array $new = [], array $expected = [])
    {
        $this->assertEquals($expected, ArrayComparer::diff($old, $new));
    }

    protected function assertNormalize(array $old = [], array $new = [], array $expected = [])
    {
        $this->assertEquals($expected, ArrayComparer::normalizeArrays($old, $new));
    }
}
