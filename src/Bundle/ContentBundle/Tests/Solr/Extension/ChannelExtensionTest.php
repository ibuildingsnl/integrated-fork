<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Solr\Extension;

use Integrated\Bundle\ContentBundle\Solr\Extension\ChannelExtension;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\ChannelObject;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\ObjectWithChannels;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChannelExtensionTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(TypeExtensionInterface::class, $this->getInstance($this->getResolver()));
    }

    #[DataProvider('buildProvider')]
    public function testBuild(ChannelableInterface $content, array $expected)
    {
        $extension = $this->getInstance($this->getResolver());
        $extension->build($container = new Container(), $content);

        self::assertEquals($expected, $container->toArray());

        $extension->build($container, $content); // should clear previous build and not add to it

        self::assertEquals($expected, $container->toArray());
    }

    public static function buildProvider(): array
    {
        return [
            [
                new ObjectWithChannels(),
                [],
            ],
            [
                new ObjectWithChannels([new ChannelObject('id1'), new ChannelObject('id2')]),
                ['facet_channels' => ['id1', 'id2']],
            ],
            [
                new ObjectWithChannels([new ChannelObject('id1'), new \stdClass(), new ChannelObject('id2')]),
                ['facet_channels' => ['id1', 'id2']],
            ],
            [
                new ObjectWithChannels([new \stdClass(), new \stdClass()]),
                [],
            ],
        ];
    }

    public function testBuildNotChannelable()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())
            ->method($this->anything());

        $this->getInstance($this->getResolver())->build($container, new \stdClass());
    }

    public function testGetName()
    {
        self::assertEquals('integrated.content', $this->getInstance($this->getResolver())->getName());
    }

    /**
     * @return ChannelExtension
     */
    protected function getInstance(ResolverInterface $resolver)
    {
        return new ChannelExtension($resolver);
    }

    /**
     * @return ResolverInterface|MockObject
     */
    protected function getResolver(?string $type = null, ?ContentTypeInterface $contentType = null)
    {
        $mock = $this->createMock(ResolverInterface::class);

        if (null !== $type) {
            $mock->expects($this->any())
                ->method('getType')
                ->with($type)
                ->willReturn($contentType);
        }

        return $mock;
    }
}
