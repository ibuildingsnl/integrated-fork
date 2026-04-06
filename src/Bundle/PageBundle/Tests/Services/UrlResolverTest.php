<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Services\ContentTypeControllerManager;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\ContentInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

final class UrlResolverTest extends TestCase
{
    public function testGenerateUrlCachesResolvedRoutePerDocumentAndChannel(): void
    {
        $channel = new Channel();
        $channel->setId('bib');

        $contentType = new ContentType();
        $contentType->setId('article');

        $page = new ContentTypePage($contentType, $channel);
        $this->setDocumentId($page, 'page-1');

        $document = $this->createMock(ContentInterface::class);
        $document->method('getId')->willReturn('doc-1');
        $document->method('getSlug')->willReturn('example');
        $document->method('getContentType')->willReturn('article');

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'channel.$id' => 'bib',
                'contentType.$id' => 'article',
            ])
            ->willReturn($page);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(ContentTypePage::class)
            ->willReturn($repository);

        $router = $this->createMock(RouterInterface::class);
        $router->method('getContext')->willReturn(new RequestContext(''));
        $router
            ->expects(self::once())
            ->method('generate')
            ->with(
                'integrated_website_content_type_page_page-1',
                ['slug' => 'example']
            )
            ->willReturn('/artikelen/example');

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        $resolver = new UrlResolver(
            $this->createMock(ContentTypeControllerManager::class),
            $channelContext,
            $router,
            $documentManager,
        );

        self::assertSame('/artikelen/example', $resolver->generateUrl($document));
        self::assertSame('/artikelen/example', $resolver->generateUrl($document));
    }

    public function testGenerateUrlCachesMissingPageLookupButKeepsFallbackBehaviorSeparate(): void
    {
        $channel = new Channel();
        $channel->setId('bib');

        $document = $this->createMock(ContentInterface::class);
        $document->method('getId')->willReturn('doc-2');
        $document->method('getSlug')->willReturn('example');
        $document->method('getContentType')->willReturn('article');

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'channel.$id' => 'bib',
                'contentType.$id' => 'article',
            ])
            ->willReturn(null);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(ContentTypePage::class)
            ->willReturn($repository);

        $router = $this->createMock(RouterInterface::class);
        $router->method('getContext')->willReturn(new RequestContext('/base'));
        $router->expects(self::never())->method('generate');

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        $resolver = new UrlResolver(
            $this->createMock(ContentTypeControllerManager::class),
            $channelContext,
            $router,
            $documentManager,
        );

        self::assertNull($resolver->generateUrl($document, null, false));
        self::assertSame('/base/content/article/example', $resolver->generateUrl($document, null, true));
    }

    private function setDocumentId(ContentTypePage $page, string $id): void
    {
        $reflection = new \ReflectionProperty($page, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($page, $id);
    }
}
