<?php

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Services\ArticleSearchService;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Common\Solr\Search\Query;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Client\ClientInterface;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Select\Result\Document;
use Solarium\QueryType\Select\Result\Result as SelectResult;

class ArticleSearchServiceTest extends TestCase
{
    public function testGetAvailableContentTypesFiltersWhitelist(): void
    {
        $allowed = (new ContentType())
            ->setId('article')
            ->setName('Article')
            ->setClass('App\\Content\\Article');

        $blocked = (new ContentType())
            ->setId('other')
            ->setName('Other')
            ->setClass('App\\Content\\Other');

        $contentTypeRepository = $this->createMock(DocumentRepository::class);
        $contentTypeRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([$allowed, $blocked]);

        $service = $this->createService(
            $this->createMock(DocumentRepository::class),
            $contentTypeRepository,
            $this->createStub(QueryFactoryInterface::class),
            $this->createStub(ClientInterface::class),
            ['App\\Content\\Article']
        );

        self::assertSame([
            ['key' => 'article', 'label' => 'Article'],
        ], $service->getAvailableContentTypes());
    }

    public function testSearchInChannelBuildsExpectedResultPayload(): void
    {
        $channel = new Channel();
        $channel->setId('main');
        $channel->setPrimaryDomain('example.test');

        $queryFactory = $this->createMock(QueryFactoryInterface::class);
        $solrClient = $this->createMock(ClientInterface::class);

        $solrQuery = new SelectQuery();
        $queryFactory->expects(self::once())
            ->method('createQuery')
            ->with(
                IntegratedContent::class,
                self::callback(static function (array $criteria): bool {
                    self::assertSame(['article'], $criteria['contenttypes']);
                    self::assertSame(['main'], $criteria['channels']);
                    self::assertSame('rel', $criteria['sort']);
                    self::assertSame('hello', $criteria['q']);

                    return true;
                })
            )
            ->willReturn(new Query($solrQuery, []));

        $result = $this->createMock(SelectResult::class);
        $result->expects(self::once())
            ->method('getDocuments')
            ->willReturn([
                new Document([
                    'type_id' => '1',
                    'title' => 'Regular result',
                    'type_name' => 'article',
                    'pub_time' => '2026-03-02 11:00:00',
                    'content' => '<p>Hello <strong>world</strong></p>',
                    'url_main' => '/news/regular',
                ]),
                new Document([
                    'type_id' => '2',
                    'title' => 'File result',
                    'type_name' => 'file',
                    'pub_time' => '2026-03-02 12:00:00',
                    'content' => '<p>PDF</p>',
                    'file' => '{"pathname":"/media/file.pdf"}',
                ]),
            ]);

        $solrClient->expects(self::once())
            ->method('select')
            ->with($solrQuery)
            ->willReturn($result);

        $service = $this->createService(
            $this->createMock(DocumentRepository::class),
            $this->createMock(DocumentRepository::class),
            $queryFactory,
            $solrClient,
            ['App\\Content\\Article']
        );

        $payload = $service->searchInChannel($channel, 'hello', ['article']);

        self::assertCount(2, $payload);
        self::assertSame('https://example.test/news/regular', $payload[0]['url']);
        self::assertSame('Hello world', $payload[0]['text']);
        self::assertSame('Article | 02-03-2026', $payload[0]['subtitle']);

        self::assertSame('https://example.test/media/file.pdf', $payload[1]['url']);
        self::assertSame('File | 02-03-2026', $payload[1]['subtitle']);
    }

    public function testSearchInChannelDropsChannelFilterForMediaTypes(): void
    {
        $channel = new Channel();
        $channel->setId('main');
        $channel->setPrimaryDomain('example.test');

        $queryFactory = $this->createMock(QueryFactoryInterface::class);
        $solrClient = $this->createMock(ClientInterface::class);

        $solrQuery = new SelectQuery();
        $queryFactory->expects(self::once())
            ->method('createQuery')
            ->with(
                IntegratedContent::class,
                self::callback(static function (array $criteria): bool {
                    self::assertSame([], $criteria['channels']);
                    self::assertSame(['image'], $criteria['contenttypes']);

                    return true;
                })
            )
            ->willReturn(new Query($solrQuery, []));

        $result = $this->createMock(SelectResult::class);
        $result->method('getDocuments')->willReturn([]);

        $solrClient->expects(self::once())
            ->method('select')
            ->with($solrQuery)
            ->willReturn($result);

        $service = $this->createService(
            $this->createMock(DocumentRepository::class),
            $this->createMock(DocumentRepository::class),
            $queryFactory,
            $solrClient,
            ['App\\Content\\Article']
        );

        self::assertSame([], $service->searchInChannel($channel, 'hello', ['image']));
    }

    /**
     * @param array<int, string> $allowedContentTypes
     */
    private function createService(
        DocumentRepository $channelRepository,
        DocumentRepository $contentTypeRepository,
        QueryFactoryInterface $queryFactory,
        ClientInterface $solrClient,
        array $allowedContentTypes,
    ): ArticleSearchService {
        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->method('getRepository')
            ->willReturnCallback(static function (string $class) use ($channelRepository, $contentTypeRepository): DocumentRepository {
                return match ($class) {
                    Channel::class => $channelRepository,
                    ContentType::class => $contentTypeRepository,
                    default => throw new \RuntimeException('Unexpected repository lookup: '.$class),
                };
            });

        return new ArticleSearchService(
            $documentManager,
            $queryFactory,
            $solrClient,
            $allowedContentTypes,
        );
    }
}
