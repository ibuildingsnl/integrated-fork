<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Solr\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Solr\Type\FeaturedType;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

final class FeaturedTypeTest extends TestCase
{
    public function testImplementsTypeInterface(): void
    {
        self::assertInstanceOf(TypeInterface::class, new FeaturedType($this->createResolver()));
    }

    public function testBuildKeepsFeaturedContentFeaturedWhenExpirationIsDisabled(): void
    {
        $content = new Article();
        $content->setContentType('article');
        $content->setFeatured(true);
        $content->setFeaturedExpiration(1);

        $container = new Container();

        (new FeaturedType($this->createResolver(false)))->build($container, $content);

        self::assertSame(
            [
                'facet_properties' => ['Featured'],
                'featured' => [true],
            ],
            $container->toArray()
        );
    }

    public function testBuildMarksExpiredFeaturedContentAsNotFeaturedWhenExpirationIsEnabled(): void
    {
        $content = new Article();
        $content->setContentType('article');
        $content->setFeatured(true);
        $content->setFeaturedExpiration(1);

        $publishTime = new PublishTime();
        $publishTime->setStartDate(new \DateTime('2000-01-01 00:00:00 UTC'));
        $content->setPublishTime($publishTime);

        $container = new Container();

        (new FeaturedType($this->createResolver(true)))->build($container, $content);

        self::assertSame(
            [
                'facet_properties' => ['Not Featured'],
                'featured' => [false],
            ],
            $container->toArray()
        );
    }

    public function testBuildKeepsUnexpiredFeaturedContentFeaturedWhenExpirationIsEnabled(): void
    {
        $content = new Article();
        $content->setContentType('article');
        $content->setFeatured(true);
        $content->setFeaturedExpiration(30);

        $publishTime = new PublishTime();
        $publishTime->setStartDate(new \DateTime('tomorrow'));
        $content->setPublishTime($publishTime);

        $container = new Container();

        (new FeaturedType($this->createResolver(true)))->build($container, $content);

        self::assertSame(
            [
                'facet_properties' => ['Featured'],
                'featured' => [true],
            ],
            $container->toArray()
        );
    }

    private function createResolver(bool $featuredExpirationEnabled = false): ResolverInterface
    {
        $contentType = new ContentType();
        $contentType->setClass(Article::class);

        if ($featuredExpirationEnabled) {
            $contentType->setOption('featured_expiration', true);
        }

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->method('getType')->with('article')->willReturn($contentType);

        return $resolver;
    }
}
