<?php

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Services\ArticleSearchServiceInterface;
use Integrated\Bundle\ContentBundle\Twig\Component\ArticleSearchLiveComponent;
use PHPUnit\Framework\TestCase;

class ArticleSearchLiveComponentTest extends TestCase
{
    public function testMountPrefillsInitialValuesAndAutoSelectsSingleChannel(): void
    {
        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $component = new ArticleSearchLiveComponent($service);

        $component->mount(
            [['key' => 'main', 'label' => 'Main']],
            [['key' => 'article', 'label' => 'Article']],
            ['link_text' => 'Link text'],
            [
                'selectionText' => 'Selected text',
                'title' => 'Initial title',
                'url' => 'www.example.test',
                'openInNewTab' => true,
                'existing' => true,
            ]
        );

        self::assertSame('main', $component->channelId);
        self::assertSame('Selected text', $component->linkText);
        self::assertSame('Initial title', $component->linkTitle);
        self::assertSame('www.example.test', $component->searchTerm);
        self::assertTrue($component->openInNewTab);
        self::assertTrue($component->existing);
    }

    public function testGetResultsReturnsEmptyArrayWhenCurrentSearchTermIsAUrl(): void
    {
        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::never())->method('findChannel');
        $service->expects(self::never())->method('searchInChannel');

        $component = new ArticleSearchLiveComponent($service);
        $component->mount(
            [['key' => 'main', 'label' => 'Main']],
            [['key' => 'article', 'label' => 'Article']],
            []
        );

        $component->searchTerm = 'https://example.test/page';
        $component->channelId = 'main';

        self::assertSame([], $component->getResults());
    }

    public function testGetResultsUsesServiceWithFilteredContentTypes(): void
    {
        $channel = new Channel();
        $channel->setId('main');

        $payload = [[
            'id' => '1',
            'title' => 'Article',
            'subtitle' => 'Article | 02-03-2026',
            'text' => 'Body',
            'url' => 'https://example.test/article',
        ]];

        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::once())->method('findChannel')->with('main')->willReturn($channel);
        $service->expects(self::once())
            ->method('searchInChannel')
            ->with($channel, 'hello', ['article'])
            ->willReturn($payload);

        $component = new ArticleSearchLiveComponent($service);
        $component->mount(
            [['key' => 'main', 'label' => 'Main']],
            [
                ['key' => 'article', 'label' => 'Article'],
                ['key' => 'news', 'label' => 'News'],
            ],
            []
        );

        $component->channelId = 'main';
        $component->searchTerm = 'hello';
        $component->contentTypeIds = ['article', 'invalid'];

        self::assertSame($payload, $component->getResults());
    }
}
