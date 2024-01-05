<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Solr\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Solr\Type\RelationJsonType;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\ObjectWithRelations;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\Type\TypeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RelationJsonTypeTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(TypeInterface::class, $this->getInstance());
    }

    #[DataProvider('buildProvider')]
    public function testBuild(Content $content, array $options, $expected)
    {
        $this->getInstance()->build($container = new Container(), $content, $options);

        self::assertEquals($expected, $container->toArray());
    }

    public static function buildProvider(): array
    {
        return [
            [
                new ObjectWithRelations(),
                [
                    'relation_id' => 'dummy',
                    'properties' => ['key' => 'id', 'type' => 'contentType'],
                    'alias' => 'field',
                ],
                [
                    'field' => [
                        json_encode([
                            ['key' => 'id1', 'type' => 'type1'],
                            ['key' => 'id2', 'type' => 'type2'],
                        ]),
                    ],
                ],
            ],
        ];
    }

    protected function getInstance(): RelationJsonType
    {
        return new RelationJsonType();
    }
}
