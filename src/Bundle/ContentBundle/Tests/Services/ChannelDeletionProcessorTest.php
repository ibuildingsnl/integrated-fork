<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\CommentBundle\Document\Comment;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionProcessor;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionReport;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionSelfHealer;
use Integrated\Bundle\ContentBundle\Services\ContentReverseReferenceCleaner;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ChannelDeletionProcessorTest extends TestCase
{
    public function testProcessDetachesMultiChannelContentAndReturnsReport(): void
    {
        $channel = $this->createChannel('channel-a');
        $otherChannel = $this->createChannel('channel-b');

        $content = new Article();
        $content->setId('content-1');
        $content->addChannel($channel);
        $content->addChannel($otherChannel);
        $content->setPrimaryChannel($channel);

        $documentManager = $this->createDocumentManager([], []);
        $documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($content));

        $documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($channel));

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$content]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->never())
            ->method('getReferencedDocuments');
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            new EventDispatcher()
        );

        $report = $processor->process($channel, true);

        self::assertInstanceOf(ChannelDeletionReport::class, $report);
        self::assertSame(1, $report->getDetachedContent());
        self::assertSame(0, $report->getRemovedContent());
        self::assertSame(0, $report->getWarningCount());
        self::assertTrue($report->isRemovedChannel());
        self::assertFalse($content->hasChannel($channel));
        self::assertTrue($content->hasChannel($otherChannel));
        self::assertSame($otherChannel, $content->getPrimaryChannel());
    }

    public function testProcessTurnsSingleChannelContentDeleteFailureIntoWarningAndSkip(): void
    {
        $channel = $this->createChannel('channel-a');

        $content = new Article();
        $content->setId('content-1');
        $content->addChannel($channel);

        $documentManager = $this->createDocumentManager([], []);
        $documentManager
            ->expects($this->never())
            ->method('persist');

        $documentManager
            ->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function (object $document) use ($content, $channel): void {
                if ($document === $content) {
                    throw new \RuntimeException('delete failed');
                }

                self::assertSame($channel, $document);
            });

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$content]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($content)
            ->willReturn([]);
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $dispatcher = $this->createDispatcherWithoutContentDeleteListeners();

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            $dispatcher
        );

        $report = $processor->process($channel, true);

        self::assertSame(0, $report->getRemovedContent());
        self::assertSame(1, $report->getWarningCount());
        self::assertTrue($report->isRemovedChannel());
        self::assertSame('success_with_warnings', $report->getStatus());
        self::assertSame([
            [
                'class' => Article::class,
                'id' => 'content-1',
            ],
        ], $report->getSkippedDocuments());
        self::assertSame(Article::class, $report->getWarnings()[0]->getDocumentClass());
        self::assertSame('content-1', $report->getWarnings()[0]->getDocumentId());
        self::assertSame('delete', $report->getWarnings()[0]->getStep());
    }

    public function testProcessTurnsReferencedImageDeleteAbortIntoWarningAndMarksChannelRemoved(): void
    {
        $channel = $this->createChannel('channel-a');

        $image = new Image();
        $image->setId('image-1');
        $image->addChannel($channel);

        $message = \sprintf(
            'Cannot remove referenced document %s (image-1). Blocked by: Homepage teaser (page-1).',
            Image::class
        );

        $documentManager = $this->createDocumentManager([], []);
        $documentManager
            ->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function (object $document) use ($image, $channel, $message): void {
                if ($document === $image) {
                    throw new AccessDeniedException($message);
                }

                self::assertSame($channel, $document);
            });

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$image]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($image)
            ->willReturn([]);
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            $this->createDispatcherWithoutContentDeleteListeners()
        );

        $report = $processor->process($channel, true);

        self::assertTrue($report->isRemovedChannel());
        self::assertSame('success_with_warnings', $report->getStatus());
        self::assertSame(1, $report->getWarningCount());
        self::assertSame(0, $report->getRemovedContent());
        self::assertSame([
            [
                'class' => Image::class,
                'id' => 'image-1',
            ],
        ], $report->getSkippedDocuments());
        self::assertSame('delete', $report->getWarnings()[0]->getStep());
        self::assertSame(Image::class, $report->getWarnings()[0]->getDocumentClass());
        self::assertSame('image-1', $report->getWarnings()[0]->getDocumentId());
        self::assertSame(AccessDeniedException::class, $report->getWarnings()[0]->getExceptionClass());
        self::assertSame($message, $report->getWarnings()[0]->getMessage());
    }

    public function testProcessRestoresDeferredReverseReferenceCleanupWhenSingleChannelDeleteFails(): void
    {
        $channel = $this->createChannel('channel-a');

        $content = new Article();
        $content->setId('content-1');
        $content->addChannel($channel);

        $contentReferrer = new Article();
        $contentReferrer->setId('article-2');
        $relation = (new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation())
            ->setRelationId('rel-1')
            ->setRelationType('related')
            ->addReference($content);
        $contentReferrer->addRelation($relation);

        $comment = new Comment();
        $comment->setId('comment-1');
        $comment->setContent($content);

        $documentManager = $this->createDocumentManager([], []);
        $persisted = [];
        $documentManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function (object $document) use (&$persisted): void {
                $persisted[] = $document;
            });

        $documentManager
            ->expects($this->exactly(3))
            ->method('remove')
            ->willReturnCallback(function (object $document) use ($content, $comment, $channel): void {
                if ($document === $comment) {
                    return;
                }

                if ($document === $content) {
                    throw new \RuntimeException('delete failed');
                }

                self::assertSame($channel, $document);
            });

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$content]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($content)
            ->willReturn([$contentReferrer, $comment]);
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $dispatcher = $this->createDispatcherWithoutContentDeleteListeners();

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            $dispatcher
        );

        $report = $processor->process($channel, true);

        self::assertSame(0, $report->getRemovedContent());
        self::assertSame(1, $report->getWarningCount());
        self::assertTrue($report->isRemovedChannel());
        self::assertCount(1, $contentReferrer->getReferencesByRelationType('related'));
        self::assertTrue(\in_array($contentReferrer, $persisted, true));
        self::assertTrue(\in_array($comment, $persisted, true));
    }

    public function testProcessRestoresMultiChannelContentStateWhenDetachPersistFails(): void
    {
        $channel = $this->createChannel('channel-a');
        $otherChannel = $this->createChannel('channel-b');

        $content = new Article();
        $content->setId('content-1');
        $content->addChannel($channel);
        $content->addChannel($otherChannel);
        $content->setPrimaryChannel($channel);

        $documentManager = $this->createDocumentManager([], []);
        $documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($content))
            ->willThrowException(new \RuntimeException('persist failed'));

        $documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($channel));

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$content]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->never())
            ->method('getReferencedDocuments');
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            new EventDispatcher()
        );

        $report = $processor->process($channel, true);

        self::assertTrue($report->isRemovedChannel());
        self::assertSame(1, $report->getWarningCount());
        self::assertTrue($content->hasChannel($channel));
        self::assertTrue($content->hasChannel($otherChannel));
        self::assertSame($channel, $content->getPrimaryChannel());
        self::assertSame([
            ['class' => Article::class, 'id' => 'content-1'],
        ], $report->getSkippedDocuments());
    }

    public function testProcessOnlyFailsWhenChannelRemovalFails(): void
    {
        $channel = $this->createChannel('channel-a');

        $documentManager = $this->createDocumentManager([], []);
        $documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($channel))
            ->willThrowException(new \RuntimeException('channel remove failed'));

        $documentManager
            ->expects($this->never())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->never())
            ->method('getReferencedDocuments');
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            new EventDispatcher()
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('channel remove failed');

        $processor->process($channel, true);
    }

    public function testProcessCollectsWarningsForPagePublicationAndBrandFailures(): void
    {
        $channel = $this->createChannel('channel-a');

        $page = new TestPage();
        $page->setId('page-1');

        $content = new Article();
        $content->setId('content-1');

        $publication = new Publication($content, $channel, new PublishTime());
        $publication->setId('publication-1');

        $brand = new Brand();
        $brand->setId('brand-1');
        $brand->addChannelLink(new ChannelLink(new ChannelType('website', 'Website'), $channel, true));

        $documentManager = $this->createDocumentManager([$publication], [$brand]);
        $documentManager
            ->expects($this->exactly(3))
            ->method('remove')
            ->willReturnCallback(function (object $document) use ($page, $publication, $channel): void {
                if ($document === $page) {
                    throw new \RuntimeException('page delete failed');
                }

                if ($document === $publication) {
                    throw new \RuntimeException('publication delete failed');
                }

                self::assertSame($channel, $document);
            });

        $documentManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($brand))
            ->willThrowException(new \RuntimeException('brand persist failed'));

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$page]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->never())
            ->method('getReferencedDocuments');
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            new EventDispatcher()
        );

        $report = $processor->process($channel, true);

        self::assertTrue($report->isRemovedChannel());
        self::assertSame(3, $report->getWarningCount());
        self::assertSame([
            ['class' => TestPage::class, 'id' => 'page-1'],
            ['class' => Publication::class, 'id' => 'publication-1'],
            ['class' => Brand::class, 'id' => 'brand-1'],
        ], $report->getSkippedDocuments());
    }

    public function testProcessTurnsBrandIterationFailureIntoWarningAndSkip(): void
    {
        $channel = $this->createChannel('channel-a');
        $brand = new ThrowingBrand('brand-throw', 'getChannelLinks failed');

        $documentManager = $this->createDocumentManager([], [$brand]);
        $documentManager
            ->expects($this->never())
            ->method('persist');

        $documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($channel));

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->never())
            ->method('getReferencedDocuments');
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            new EventDispatcher()
        );

        $report = $processor->process($channel, true);

        self::assertTrue($report->isRemovedChannel());
        self::assertSame(1, $report->getWarningCount());
        self::assertSame([
            ['class' => ThrowingBrand::class, 'id' => 'brand-throw'],
        ], $report->getSkippedDocuments());
        self::assertSame('update', $report->getWarnings()[0]->getStep());
        self::assertSame(ThrowingBrand::class, $report->getWarnings()[0]->getDocumentClass());
    }

    public function testProcessRestoresBrandLinksWhenRemovalFailsMidUpdate(): void
    {
        $channel = $this->createChannel('channel-a');
        $link = new ChannelLink(new ChannelType('website', 'Website'), $channel, true);
        $brand = new PartiallyFailingBrand('brand-rollback');
        $brand->addChannelLink($link);

        $documentManager = $this->createDocumentManager([], [$brand]);
        $documentManager
            ->expects($this->never())
            ->method('persist');

        $documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($channel));

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->never())
            ->method('getReferencedDocuments');
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            new EventDispatcher()
        );

        $report = $processor->process($channel, true);

        self::assertTrue($report->isRemovedChannel());
        self::assertSame(1, $report->getWarningCount());
        self::assertTrue($brand->hasChannelLink($link));
        self::assertCount(1, $brand->getChannelLinks());
        self::assertSame([
            ['class' => PartiallyFailingBrand::class, 'id' => 'brand-rollback'],
        ], $report->getSkippedDocuments());
    }

    public function testProcessTurnsChannelDeletedListenerFailureIntoWarning(): void
    {
        $channel = $this->createChannel('channel-a');

        $documentManager = $this->createDocumentManager([], []);
        $documentManager
            ->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($channel));

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->never())
            ->method('getReferencedDocuments');
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $dispatcher = $this->createDispatcherThatThrowsOnChannelDeleted('listener failed');

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            $dispatcher
        );

        $report = $processor->process($channel, true);

        self::assertTrue($report->isRemovedChannel());
        self::assertSame('success_with_warnings', $report->getStatus());
        self::assertSame(1, $report->getWarningCount());
        self::assertSame('dispatch', $report->getWarnings()[0]->getStep());
        self::assertSame(Channel::class, $report->getWarnings()[0]->getDocumentClass());
        self::assertSame('channel-a', $report->getWarnings()[0]->getDocumentId());
        self::assertSame([], $report->getSkippedDocuments());
    }

    public function testProcessInvokesSelfHealerForSingleChannelArticleDeletion(): void
    {
        $channel = $this->createChannel('channel-a');

        $content = new Article();
        $content->setId('content-1');
        $content->addChannel($channel);

        $authorsProperty = new \ReflectionProperty(Article::class, 'authors');
        $authorsProperty->setAccessible(true);
        $authorsProperty->setValue($content, null);

        $documentManager = $this->createDocumentManager([], []);
        $documentManager
            ->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function (object $document) use ($content, $channel): void {
                if ($document === $content) {
                    throw new \RuntimeException('delete failed');
                }

                self::assertSame($channel, $document);
            });

        $documentManager
            ->expects($this->once())
            ->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$content]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($content)
            ->willReturn([]);
        $contentReverseReferenceCleaner = new ContentReverseReferenceCleaner($documentManager, $cleanupSearch);

        $processor = $this->createProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            $this->createDispatcherWithoutContentDeleteListeners()
        );

        $processor->process($channel, true);

        self::assertInstanceOf(ArrayCollection::class, $authorsProperty->getValue($content));
    }

    /**
     * @param Publication[] $publications
     * @param Brand[]       $brands
     *
     * @return DocumentManager&MockObject
     */
    private function createDocumentManager(array $publications, array $brands): DocumentManager
    {
        $publicationRepository = new class($publications) implements ObjectRepository {
            public function __construct(
                private readonly array $publications,
            ) {
            }

            public function find($id): ?object
            {
                return null;
            }

            public function findAll(): array
            {
                return [];
            }

            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                return [];
            }

            public function findOneBy(array $criteria): ?object
            {
                return null;
            }

            public function getClassName(): string
            {
                return Publication::class;
            }

            public function createQueryBuilder(): object
            {
                return new class($this->publications) {
                    public function __construct(
                        private readonly array $publications,
                    ) {
                    }

                    public function field(string $field): self
                    {
                        TestCase::assertSame('channel.$id', $field);

                        return $this;
                    }

                    public function equals(string $channelId): self
                    {
                        TestCase::assertNotSame('', $channelId);

                        return $this;
                    }

                    public function getQuery(): object
                    {
                        return new class($this->publications) {
                            public function __construct(
                                private readonly array $publications,
                            ) {
                            }

                            public function toArray(): array
                            {
                                return $this->publications;
                            }
                        };
                    }
                };
            }
        };

        $brandRepository = new class($brands) implements ObjectRepository {
            public function __construct(
                private readonly array $brands,
            ) {
            }

            public function find($id): ?object
            {
                return null;
            }

            public function findAll(): array
            {
                return $this->brands;
            }

            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                return [];
            }

            public function findOneBy(array $criteria): ?object
            {
                return null;
            }

            public function getClassName(): string
            {
                return Brand::class;
            }
        };

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->willReturnCallback(function (string $class) use ($publicationRepository, $brandRepository): object {
                return match ($class) {
                    Publication::class => $publicationRepository,
                    Brand::class => $brandRepository,
                    default => throw new \RuntimeException(\sprintf('Unexpected repository lookup: %s', $class)),
                };
            });

        return $documentManager;
    }

    private function createProcessor(
        DocumentManager $documentManager,
        SearchContentReferenced $searchContentReferenced,
        ContentReverseReferenceCleaner $contentReverseReferenceCleaner,
        EventDispatcherInterface $dispatcher,
    ): ChannelDeletionProcessor {
        return new ChannelDeletionProcessor(
            $documentManager,
            $searchContentReferenced,
            $contentReverseReferenceCleaner,
            $dispatcher,
            new ChannelDeletionSelfHealer()
        );
    }

    private function createChannel(string $id): Channel
    {
        $channel = new Channel();
        $channel->setId($id);
        $channel->setName($id);

        return $channel;
    }

    private function createDispatcherWithoutContentDeleteListeners(): EventDispatcherInterface
    {
        return new class implements EventDispatcherInterface {
            public function dispatch(object $event, ?string $eventName = null): object
            {
                return $event;
            }

            public function addListener(string $eventName, callable $listener, int $priority = 0): void
            {
            }

            public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber): void
            {
            }

            public function removeListener(string $eventName, callable $listener): void
            {
            }

            public function removeSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber): void
            {
            }

            public function getListeners(?string $eventName = null): array
            {
                return [];
            }

            public function getListenerPriority(string $eventName, callable $listener): ?int
            {
                return null;
            }

            public function hasListeners(?string $eventName = null): bool
            {
                return false;
            }
        };
    }

    private function createDispatcherThatThrowsOnChannelDeleted(string $message): EventDispatcherInterface
    {
        return new class($message) implements EventDispatcherInterface {
            public function __construct(
                private readonly string $message,
            ) {
            }

            public function dispatch(object $event, ?string $eventName = null): object
            {
                if ($eventName === \Integrated\Common\Channel\Events::CHANNEL_DELETED) {
                    throw new \RuntimeException($this->message);
                }

                return $event;
            }

            public function addListener(string $eventName, callable $listener, int $priority = 0): void
            {
            }

            public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber): void
            {
            }

            public function removeListener(string $eventName, callable $listener): void
            {
            }

            public function removeSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber): void
            {
            }

            public function getListeners(?string $eventName = null): array
            {
                return [];
            }

            public function getListenerPriority(string $eventName, callable $listener): ?int
            {
                return null;
            }

            public function hasListeners(?string $eventName = null): bool
            {
                return false;
            }
        };
    }
}

final class TestPage extends AbstractPage
{
    public function setId(string $id): void
    {
        $this->id = $id;
    }
}

final class ThrowingBrand extends Brand
{
    public function __construct(
        string $id,
        private readonly string $message,
    ) {
        parent::__construct();
        $this->setId($id);
    }

    public function getChannelLinks(): \Doctrine\Common\Collections\Collection
    {
        throw new \RuntimeException($this->message);
    }
}

final class PartiallyFailingBrand extends Brand
{
    private bool $hasThrown = false;

    public function __construct(string $id)
    {
        parent::__construct();
        $this->setId($id);
    }

    public function removeChannelLink(ChannelLink $link): void
    {
        parent::removeChannelLink($link);

        if ($this->hasThrown) {
            return;
        }

        $this->hasThrown = true;

        throw new \RuntimeException('removeChannelLink failed');
    }
}
