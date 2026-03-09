<?php

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\ArticleSearchController;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Services\ArticleSearchServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleSearchControllerTest extends TestCase
{
    public function testSearchContentByChannelReturnsBadRequestWhenChannelIdIsMissing(): void
    {
        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::never())->method('findChannel');

        $controller = new ArticleSearchController($service);
        $response = $controller->searchContentByChannel(new Request(['term' => 'hello']), null);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(['msg' => 'No channel id specified'], $this->decodeJson($response));
    }

    public function testSearchContentByChannelReturnsBadRequestWhenSearchTermIsMissing(): void
    {
        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::never())->method('findChannel');

        $controller = new ArticleSearchController($service);
        $response = $controller->searchContentByChannel(new Request(), 'channel-1');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(['msg' => 'No search term specified'], $this->decodeJson($response));
    }

    public function testSearchContentByChannelReturnsNotFoundWhenChannelDoesNotExist(): void
    {
        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::once())
            ->method('findChannel')
            ->with('missing-channel')
            ->willReturn(null);

        $controller = new ArticleSearchController($service);
        $response = $controller->searchContentByChannel(new Request(['term' => 'hello']), 'missing-channel');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame(['msg' => 'Channel not found'], $this->decodeJson($response));
    }

    public function testSearchContentByChannelReturnsResultPayload(): void
    {
        $channel = new Channel();
        $channel->setId('channel-1');

        $payload = [[
            'id' => 'id-1',
            'title' => 'Title',
            'subtitle' => 'Article | 02-03-2026',
            'text' => 'Body',
            'url' => 'https://example.test/news/item',
        ]];

        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::once())->method('findChannel')->with('channel-1')->willReturn($channel);
        $service->expects(self::once())
            ->method('canAccessChannelForCurrentUser')
            ->with($channel)
            ->willReturn(true);
        $service->expects(self::once())
            ->method('searchInChannel')
            ->with($channel, 'hello', ['article', 'news'])
            ->willReturn($payload);

        $controller = new ArticleSearchController($service);
        $response = $controller->searchContentByChannel(
            new Request(['term' => 'hello', 'contentTypeIds' => 'article,news']),
            'channel-1'
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame($payload, $this->decodeJson($response));
    }

    public function testSearchContentByChannelReturnsNotFoundPayloadWhenNoResultsWereFound(): void
    {
        $channel = new Channel();
        $channel->setId('channel-1');

        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::once())->method('findChannel')->with('channel-1')->willReturn($channel);
        $service->expects(self::once())
            ->method('canAccessChannelForCurrentUser')
            ->with($channel)
            ->willReturn(true);
        $service->expects(self::once())
            ->method('searchInChannel')
            ->with($channel, 'hello', [''])
            ->willReturn([]);

        $controller = new ArticleSearchController($service);
        $response = $controller->searchContentByChannel(
            new Request(['term' => 'hello']),
            'channel-1'
        );

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame([
            'term' => 'hello',
            'channelId' => 'channel-1',
            'contentTypeId' => '',
        ], $this->decodeJson($response));
    }

    public function testSearchContentByChannelReturnsForbiddenWhenChannelIsNotAllowed(): void
    {
        $channel = new Channel();
        $channel->setId('channel-1');

        $service = $this->createMock(ArticleSearchServiceInterface::class);
        $service->expects(self::once())->method('findChannel')->with('channel-1')->willReturn($channel);
        $service->expects(self::once())
            ->method('canAccessChannelForCurrentUser')
            ->with($channel)
            ->willReturn(false);
        $service->expects(self::never())->method('searchInChannel');

        $controller = new ArticleSearchController($service);
        $response = $controller->searchContentByChannel(
            new Request(['term' => 'hello']),
            'channel-1'
        );

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertSame(['msg' => 'Channel access denied'], $this->decodeJson($response));
    }

    /** @return array<string, mixed>|array<int, mixed> */
    private function decodeJson(Response $response): array
    {
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
