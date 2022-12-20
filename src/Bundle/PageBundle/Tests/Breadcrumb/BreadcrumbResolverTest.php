<?php
/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Tests\Breadcrumb;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\PageBundle\Breadcrumb\BreadcrumbItem;
use Integrated\Bundle\PageBundle\Breadcrumb\BreadcrumbResolver;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Common\Content\Channel\ChannelContext;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BreadcrumbResolverTest extends TestCase
{
    public const TEMPLATE = 'default';

    /**
     * @var DocumentManager|MockObject
     */
    protected $documentManager;

    /**
     * @var urlResolver|MockObject
     */
    protected $urlResolver;

    /**
     * @var ChannelContextInterface|MockObject
     */
    protected $channelContext;

    /**
     * @var RequestStack|MockObject
     */
    protected $requestStack;

    /**
     * @var Request|MockObject
     */
    protected $request;

    /**
     * @var BreadcrumbResolver
     */
    protected $breadcrumbResolver;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->urlResolver = $this->createMock(UrlResolver::class);
        $this->channelContext = $this->createMock(ChannelContext::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->request = $this->createMock(Request::class);
        $this->requestStack->method('getMainRequest')->willReturn($this->request);

        $this->breadcrumbResolver = new BreadcrumbResolver(
            $this->documentManager,
            $this->urlResolver,
            $this->channelContext,
            $this->requestStack
        );
    }

    public function testGetBreadcrumb()
    {
        $channel = new Channel();
        $channel->setId('my_channel');

        $this->request->method('getPathInfo')->willReturn('/my/page/my-article');
        $this->channelContext->method('getChannel')->willReturn($channel);

        $pageRepository = $this->createMock(ObjectRepository::class);
        $this->documentManager
            ->method('getRepository')
            ->with(Page::class)->willReturn($pageRepository);

        $contentRepository = $this->createMock(ObjectRepository::class);
        $this->documentManager
            ->method('getRepository')
            ->with(Content::class)
            ->willReturn($contentRepository);

        $this->urlResolver
            ->expects($this->once())
            ->method('generateUrl')
            ->willReturn('/my');

        $page = new Page();
        $page->setTitle('My page');
        $page->setPath('/my/page');

        $article = new Article();
        $article->setTitle('My article');
        $article->setSlug('my');
        $article->addChannel($channel);
        $article->getPublishTime()->setStartDate(new \DateTime());
        $article->getPublishTime()->setEndDate(new \DateTime('next week'));

        $pageRepository
            ->method('findOneBy')
            ->with(['path' => '/', 'channel.$id' => 'my_channel'])
            ->willReturn(null);

        $contentRepository
            ->method('findOneBy')
            ->with(['slug' => 'my', 'channels.$id' => 'my_channel'])
            ->willReturn($article);

        $pageRepository
            ->method('findOneBy')
            ->with(['path' => '/my', 'channel.$id' => 'my_channel'])
            ->willReturn(null);

        $contentRepository
            ->method('findOneBy')
            ->with(['slug' => 'my-article', 'channels.$id' => 'my_channel'])
            ->willReturn(null);

        $pageRepository
            ->method('findOneBy')
            ->with(['path' => '/my/page', 'channel.$id' => 'my_channel'])
            ->willReturn($page);

        $expectedResult = [
            new BreadcrumbItem('My article', '/my'),
            new BreadcrumbItem('My page', '/my/page'),
        ];
        self::assertEquals($expectedResult, $this->breadcrumbResolver->getBreadcrumb());
    }
}
