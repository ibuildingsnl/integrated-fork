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
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\CustomFields;
use Integrated\Bundle\ContentBundle\Solr\Type\CustomFieldsType;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomFieldsTypeTest extends TestCase
{
    public function testInterface(): void
    {
        self::assertInstanceOf(TypeInterface::class, $this->getInstance());
    }

    public function testBuild(): void
    {
        $container = new Container();
        $content = $this->getContent(
            new CustomFields([
                'author' => 'John Doe',
                'company' => 'e-Active',
            ])
        );

        $this->getInstance()->build($container, $content);

        self::assertSame(
            [
                'author' => ['John Doe'],
                'company' => ['e-Active'],
            ],
            $container->toArray()
        );
    }

    public function testBuildSkipsExistingCoreField(): void
    {
        $container = new Container();
        $container->set('published', false);

        $content = $this->getContent(
            new CustomFields([
                'published' => true,
                'author' => 'John Doe',
            ])
        );

        $this->getInstance()->build($container, $content);

        self::assertSame(
            [
                'published' => [false],
                'author' => ['John Doe'],
            ],
            $container->toArray()
        );
    }

    public function testBuildNoContent(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->build($container, new \stdClass());
    }

    public function testGetName(): void
    {
        self::assertSame('integrated.customFields', $this->getInstance()->getName());
    }

    private function getInstance(): CustomFieldsType
    {
        return new CustomFieldsType();
    }

    private function getContent(CustomFields $customFields): Content|MockObject
    {
        $content = $this->createMock(Content::class);
        $content->expects($this->any())
            ->method('getCustomFields')
            ->willReturn($customFields);

        return $content;
    }
}
