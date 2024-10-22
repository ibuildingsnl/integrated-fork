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
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PubActiveExtensionTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(TypeExtensionInterface::class, $this->getInstance($this->getResolver()));
    }

    #[DataProvider('buildProvider')]
    public function testBuild(array $content, string $contentType, string $contentTypeName, array $expected)
    {
        $extension = $this->getInstance($this->getResolver($contentType, $this->getContentType($contentTypeName)));
        $extension->build($container = new Container(), $this->getContent(...$content));

        self::assertEquals($expected, $container->toArray());

        $extension->build($container, $this->getContent(...$content)); // should clear previous build and not add to it

        self::assertEquals($expected, $container->toArray());
    }

    public static function buildProvider(): array
    {
        return [
            [
                [false, 'article'],
                'article',
                'Article',
                ['pub_active' => [false], 'published' => [false]],
            ],
            [
                [true, 'news'],
                'news',
                'News',
                ['pub_active' => [true], 'published' => [true]],
            ],
        ];
    }

    public function testBuildNoContent()
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
     * @return PubActiveExtension
     */
    protected function getInstance(ResolverInterface $resolver)
    {
        return new PubActiveExtension($resolver);
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
