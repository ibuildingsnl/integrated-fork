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

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Solr\Type\ContentType;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\__CG__\ProxyObject;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\Object1;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\Object2;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{
    private ObjectManager|MockObject $manager;

    protected function setUp(): void
    {
        $this->manager = $this->createMock('Doctrine\\Persistence\\ObjectManager');
    }

    public function testInterface()
    {
        self::assertInstanceOf(TypeInterface::class, $this->getInstance());
    }

    #[DataProvider('buildProvider')]
    public function testBuild(ContentInterface $content, array $expected)
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($expected['type_class'][0]);

        $this->manager->expects($this->exactly(2))
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $container = new Container();

        $this->getInstance()->build($container, $content);
        $this->getInstance()->build($container, $content); // should clear previous build

        self::assertEquals($expected, $container->toArray());
    }

    public static function buildProvider(): array
    {
        return [
            [
                new Object1(),
                [
                    'id' => ['type1-id1'],
                    'type_name' => ['type1'],
                    'type_class' => [Object1::class],
                    'type_id' => ['id1'],
                ],
            ],
            [
                new Object2(),
                [
                    'id' => ['type2-id2'],
                    'type_name' => ['type2'],
                    'type_class' => [Object2::class],
                    'type_id' => ['id2'],
                ],
            ],
            [
                new ProxyObject(),
                [
                    'id' => ['proxy-type-proxy-id'],
                    'type_name' => ['proxy-type'],
                    'type_class' => ['ProxyObject'],
                    'type_id' => ['proxy-id'],
                ],
            ],
        ];
    }

    public function testBuildNoContent()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->build($container, new \stdClass());
    }

    public function testGetName()
    {
        self::assertEquals('integrated.content', $this->getInstance()->getName());
    }

    protected function getInstance(): ContentType
    {
        return new ContentType($this->manager);
    }
}
