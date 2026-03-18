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
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Integrated\Bundle\ContentBundle\Solr\Extension\SeoMetaExtension;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SeoMetaExtensionTest extends TestCase
{
    public function testInterface(): void
    {
        self::assertInstanceOf(TypeExtensionInterface::class, $this->getInstance($this->getResolver()));
    }

    #[DataProvider('buildProvider')]
    public function testBuild(
        string $contentTypeId,
        bool $hasSeoField,
        ?string $seoScore,
        ?string $readabilityScore,
        array $expected,
    ): void {
        $content = $this->createContentMock($contentTypeId, $seoScore, $readabilityScore);
        $resolver = $this->createResolverMock($contentTypeId, $hasSeoField);

        $extension = $this->getInstance($resolver);
        $extension->build($container = new Container(), $content);

        self::assertEquals($expected, $container->toArray());

        $extension->build($container, $content);

        self::assertEquals($expected, $container->toArray());
    }

    public static function buildProvider(): array
    {
        return [
            'seo enabled content type with score fields' => [
                'news',
                true,
                'Good',
                'BAD',
                [
                    'has_seo_metadata' => [true],
                    'seo_score' => ['good'],
                    'readability_score' => ['bad'],
                ],
            ],
            'seo enabled content type without score fields' => [
                'news',
                true,
                null,
                null,
                [
                    'has_seo_metadata' => [true],
                    'seo_score' => ['feedback'],
                    'readability_score' => ['feedback'],
                ],
            ],
            'content type without seo metadata field' => [
                'news',
                false,
                null,
                null,
                [
                    'has_seo_metadata' => [false],
                ],
            ],
        ];
    }

    public function testBuildNoContent(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())
            ->method($this->anything());

        $this->getInstance($this->getResolver())->build($container, new \stdClass());
    }

    public function testGetName(): void
    {
        self::assertEquals('integrated.content', $this->getInstance($this->getResolver())->getName());
    }

    protected function getInstance(ResolverInterface $resolver): SeoMetaExtension
    {
        return new SeoMetaExtension($resolver);
    }

    protected function getResolver(): ResolverInterface|MockObject
    {
        return $this->createMock(ResolverInterface::class);
    }

    private function createContentMock(string $contentTypeId, ?string $seoScore, ?string $readabilityScore): Content
    {
        $seoMeta = null;
        if (null !== $seoScore || null !== $readabilityScore) {
            $seoMeta = new SeoMeta();
            if (null !== $seoScore) {
                $seoMeta->setSeoScore($seoScore);
            }
            if (null !== $readabilityScore) {
                $seoMeta->setReadabilityScore($readabilityScore);
            }
        }

        $content = new class($seoMeta) extends Content {
            public function __construct(private readonly ?SeoMeta $seoMeta)
            {
                parent::__construct();
            }

            public function getSeoMetadata(): ?SeoMeta
            {
                return $this->seoMeta;
            }

            public function __toString(): string
            {
                return 'seo-meta-test-content';
            }
        };

        $content->setContentType($contentTypeId);

        return $content;
    }

    private function createResolverMock(string $contentTypeId, bool $hasSeoField): ResolverInterface|MockObject
    {
        $contentType = $this->createMock(ContentTypeInterface::class);
        $contentType->expects($this->atLeastOnce())
            ->method('hasField')
            ->with('seoMetadata')
            ->willReturn($hasSeoField);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->expects($this->atLeastOnce())
            ->method('getType')
            ->with($contentTypeId)
            ->willReturn($contentType);

        return $resolver;
    }
}
