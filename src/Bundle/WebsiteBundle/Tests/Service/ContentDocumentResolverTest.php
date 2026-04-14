<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Service;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\WebsiteBundle\Service\ContentDocumentResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContentDocumentResolverTest extends TestCase
{
    public function testResolveReturnsMatchingDocumentForSlug(): void
    {
        $article = new Article();
        $repository = $this->createMock(ContentRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['slug' => 'my-article'])
            ->willReturn($article);

        $resolver = new ContentDocumentResolver($repository);
        $request = new Request();
        $request->attributes->set('slug', 'my-article');

        self::assertSame($article, $resolver->resolve($request, Article::class));
    }

    public function testResolveThrowsNotFoundForMissingSlug(): void
    {
        $repository = $this->createMock(ContentRepository::class);
        $repository
            ->expects(self::never())
            ->method('findOneBy');

        $resolver = new ContentDocumentResolver($repository);

        $this->expectException(NotFoundHttpException::class);

        $resolver->resolve(new Request(), Article::class);
    }

    public function testResolveThrowsNotFoundForUnexpectedDocumentType(): void
    {
        $repository = $this->createMock(ContentRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['slug' => 'my-article'])
            ->willReturn(new Taxonomy());

        $resolver = new ContentDocumentResolver($repository);
        $request = new Request();
        $request->attributes->set('slug', 'my-article');

        $this->expectException(NotFoundHttpException::class);

        $resolver->resolve($request, Article::class);
    }
}
