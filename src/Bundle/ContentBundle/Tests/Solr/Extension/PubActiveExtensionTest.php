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

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Solr\Extension\PubActiveExtension;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Integrated\Bundle\ContentBundle\Solr\Extension\PubActiveExtension
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class PubActiveExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(TypeExtensionInterface::class, $this->getInstance($this->getResolver()));
    }

    /**
     * @dataProvider buildProvider
     */
    public function testBuild(ContentInterface $content, string $contentType, string $contentTypeName, array $expected)
    {
        $container = $this->getContainer();

        $this->getInstance($this->getResolver($contentType, $this->getContentType($contentTypeName)))->build($container, $content);
        $this->getInstance($this->getResolver($contentType, $this->getContentType($contentTypeName)))->build($container, $content); // should clear previous build

        self::assertEquals($expected, $container->toArray());
    }

    public function buildProvider()
    {
        return [
            [
                $this->getContent(false, 'article'),
                'article',
                'Article',
                ['pub_active' => [false]],
                ['published' => false],
            ],
            [
                $this->getContent(true, 'news'),
                'news',
                'News',
                ['pub_active' => [true]],
                ['published' => true],
            ],
        ];
    }

    public function testBuildNoContent()
    {
        /* @var ContainerInterface | MockObject $container */
        $container = $this->createMock('Integrated\\Common\\Converter\\ContainerInterface');
        $container->expects($this->never())
            ->method($this->anything());

        $this->getInstance($this->getResolver())->build($container, new \stdClass());
    }

    public function testGetName()
    {
        self::assertEquals('integrated.content', $this->getInstance($this->getResolver())->getName());
    }

    /**
     * @return PubActiveExtension
     */
    protected function getInstance(ResolverInterface $resolver)
    {
        return new PubActiveExtension($resolver);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        // Easier to check end result when using an actual container instead of mocking it away. Also
        // the code coverage for the container class is ignored for these tests.

        return new Container();
    }

    /**
     * @return Content|MockObject
     */
    protected function getContent(bool $published, string $contentType)
    {
        $mock = $this->createMock(Content::class);
        $mock->expects($this->any())
            ->method('getContentType')
            ->willReturn($contentType);
        $mock->expects($this->atLeastOnce())
            ->method('isPublished')
            ->willReturn($published);

        return $mock;
    }

    /**
     * @return ContentTypeInterface|MockObject
     */
    protected function getContentType(string $name)
    {
        $mock = $this->createMock(ContentTypeInterface::class);

        return $mock;
    }

    /**
     * @return ResolverInterface|MockObject
     */
    protected function getResolver(string $type = null, ContentTypeInterface $contentType = null)
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
