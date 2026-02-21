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

use Integrated\Bundle\ContentBundle\Solr\Type\PropertyType;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\Object1;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\Type\TypeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PropertyTypeTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(TypeInterface::class, $this->getInstance());
    }

    #[DataProvider('buildProvider')]
    public function testBuild(ContentInterface $content, array $options, array $expected)
    {
        $this->getInstance()->build($container = new Container(), $content, $options);

        self::assertEquals($expected, $container->toArray());
    }

    public static function buildProvider(): array
    {
        return [
            [new Object1(), [['field' => 'contentType', 'fieldValue' => 'type1', 'label' => 'Test 1']], ['facet_properties' => ['Test 1']]],
            [new Object1(), [['field' => 'contentType', 'fieldValueNot' => 'type2', 'label' => 'Test 2']], ['facet_properties' => ['Test 2']]],
            [new Object1(), [['field' => 'contentType', 'fieldValue' => 'type2', 'label' => 'Test 2']], []],
        ];
    }

    protected function getInstance(): PropertyType
    {
        return new PropertyType();
    }
}
