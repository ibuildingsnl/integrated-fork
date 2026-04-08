<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Item;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageBlockCloner;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageCopyRequest;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageCopyRequestFactory;
use Integrated\Bundle\PageBundle\Services\PageCopyService;
use Integrated\Bundle\PageBundle\Services\RouteCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PageCopyServiceTest extends TestCase
{
    public function testCloneOperationRequiresNewBlockId(): void
    {
        $service = $this->createService($this->createConfiguredDocumentManager([
            'channel' => $this->createChannel('target'),
            'pages' => [$this->createPageWithBlock('source-page', '/page', 'source-block')],
            'existingPage' => null,
            'existingBlock' => null,
        ]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing target block id');

        $service->copyPages($this->createRequest([
            'sourceChannel' => 'source',
            'targetChannel' => 'target',
            'pages' => [
                'pagesource-page' => [
                    'selected' => true,
                    'blocks' => [
                        'block_source-block' => [
                            'operation' => 'clone',
                            'newBlockId' => '',
                        ],
                    ],
                ],
            ],
        ]));
    }

    public function testCloneOperationRebuildsCopiedPageBlockIds(): void
    {
        $persisted = [];
        $service = $this->createService($this->createConfiguredDocumentManager([
            'channel' => $this->createChannel('target'),
            'pages' => [$this->createPageWithBlock('source-page', '/page', 'source-block')],
            'existingPage' => null,
            'existingBlock' => null,
            'persist' => static function (object $document) use (&$persisted): void {
                $persisted[] = $document;
            },
        ]));

        $service->copyPages($this->createRequest([
            'sourceChannel' => 'source',
            'targetChannel' => 'target',
            'pages' => [
                'pagesource-page' => [
                    'selected' => true,
                    'blocks' => [
                        'block_source-block' => [
                            'operation' => 'clone',
                            'newBlockId' => 'target-block',
                        ],
                    ],
                ],
            ],
        ]));

        $copiedPage = $this->findPersistedPage($persisted);

        self::assertSame(['target-block'], $copiedPage->getBlockIds());
    }

    public function testOverwriteFlushesRemovalBeforePersistingReplacementPage(): void
    {
        $operations = [];
        $service = $this->createService($this->createConfiguredDocumentManager([
            'channel' => $this->createChannel('target'),
            'pages' => [$this->createPageWithBlock('source-page', '/page', 'source-block')],
            'existingPage' => $this->createPageWithBlock('existing-page', '/page', 'existing-block'),
            'existingBlock' => null,
            'remove' => static function () use (&$operations): void {
                $operations[] = 'remove';
            },
            'persist' => static function (object $document) use (&$operations): void {
                $operations[] = $document instanceof Page ? 'persist_page' : 'persist_block';
            },
            'flush' => static function () use (&$operations): void {
                $operations[] = 'flush';
            },
        ]));

        $service->copyPages($this->createRequest([
            'sourceChannel' => 'source',
            'targetChannel' => 'target',
            'pages' => [
                'pagesource-page' => [
                    'selected' => true,
                    'copyAction' => 'overwrite',
                    'blocks' => [],
                ],
            ],
        ]));

        self::assertContains('remove', $operations);
        self::assertContains('persist_page', $operations);
        self::assertGreaterThan(
            array_search('remove', $operations, true),
            array_search('flush', $operations, true)
        );
        self::assertGreaterThan(
            array_search('flush', $operations, true),
            array_search('persist_page', $operations, true)
        );
    }

    public function testCreateActionSkipsExistingTargetPageWithoutRemovingIt(): void
    {
        $operations = [];
        $service = $this->createService($this->createConfiguredDocumentManager([
            'channel' => $this->createChannel('target'),
            'pages' => [$this->createPageWithBlock('source-page', '/page', 'source-block')],
            'existingPage' => $this->createPageWithBlock('existing-page', '/page', 'existing-block'),
            'existingBlock' => null,
            'remove' => static function () use (&$operations): void {
                $operations[] = 'remove';
            },
            'persist' => static function (object $document) use (&$operations): void {
                $operations[] = $document instanceof Page ? 'persist_page' : 'persist_block';
            },
            'flush' => static function () use (&$operations): void {
                $operations[] = 'flush';
            },
        ]));

        $result = $service->copyPages($this->createRequest([
            'sourceChannel' => 'source',
            'targetChannel' => 'target',
            'pages' => [
                'pagesource-page' => [
                    'selected' => true,
                    'copyAction' => 'create',
                    'blocks' => [],
                ],
            ],
        ]));

        self::assertSame(0, $result->getCopiedPages());
        self::assertTrue($result->hasSkippedExistingPages());
        self::assertSame(['/page'], $result->getSkippedExistingPaths());
        self::assertNotContains('remove', $operations);
        self::assertNotContains('persist_page', $operations);
    }

    public function testRouteCacheIsClearedOncePerCopyBatch(): void
    {
        $routeCache = $this->createMock(RouteCache::class);
        $routeCache
            ->expects(self::once())
            ->method('clear');

        $service = new PageCopyService($this->createConfiguredDocumentManager([
            'channel' => $this->createChannel('target'),
            'pages' => [
                $this->createPageWithBlock('source-page-a', '/page-a', 'source-block-a'),
                $this->createPageWithBlock('source-page-b', '/page-b', 'source-block-b'),
            ],
            'existingPage' => null,
            'existingBlock' => null,
        ]), $routeCache, new PageBlockCloner());

        $service->copyPages($this->createRequest([
            'sourceChannel' => 'source',
            'targetChannel' => 'target',
            'pages' => [
                'pagesource-page-a' => [
                    'selected' => true,
                    'blocks' => [],
                ],
                'pagesource-page-b' => [
                    'selected' => true,
                    'blocks' => [],
                ],
            ],
        ]));
    }

    /**
     * @param array{
     *     channel: Channel|null,
     *     pages: list<Page>,
     *     existingPage: Page|null,
     *     existingBlock: object|null,
     *     persist?: callable(object): void,
     *     remove?: callable(object): void,
     *     flush?: callable(): void
     * } $config
     */
    private function createConfiguredDocumentManager(array $config): DocumentManager
    {
        $channelRepository = $this->createMock(ObjectRepository::class);
        $channelRepository
            ->method('find')
            ->willReturn($config['channel']);

        $pageRepository = $this->createMock(ObjectRepository::class);
        $pageRepository
            ->method('findBy')
            ->willReturn($config['pages']);
        $pageRepository
            ->method('findOneBy')
            ->willReturn($config['existingPage']);

        $blockRepository = $this->createMock(ObjectRepository::class);
        $blockRepository
            ->method('findOneBy')
            ->willReturn($config['existingBlock']);

        /** @var ClassMetadata<object>&MockObject $classMetadata */
        $classMetadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getName'])
            ->getMock();
        $classMetadata
            ->method('getName')
            ->willReturn(TextBlock::class);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->willReturnCallback(static function (string $class) use ($channelRepository, $pageRepository, $blockRepository): ObjectRepository {
                return match ($class) {
                    Channel::class => $channelRepository,
                    Page::class => $pageRepository,
                    \Integrated\Bundle\BlockBundle\Document\Block\Block::class => $blockRepository,
                    default => throw new \RuntimeException('Unexpected repository '.$class),
                };
            });
        $documentManager
            ->method('getClassMetadata')
            ->with(TextBlock::class)
            ->willReturn($classMetadata);

        $documentManager
            ->method('detach')
            ->willReturnCallback(static function (): void {
            });

        $documentManager
            ->method('persist')
            ->willReturnCallback($config['persist'] ?? static function (): void {
            });

        $documentManager
            ->method('remove')
            ->willReturnCallback($config['remove'] ?? static function (): void {
            });

        $documentManager
            ->method('flush')
            ->willReturnCallback($config['flush'] ?? static function (): void {
            });

        return $documentManager;
    }

    private function createService(DocumentManager $documentManager): PageCopyService
    {
        $routeCache = $this->createMock(RouteCache::class);
        $routeCache
            ->method('clear')
            ->willReturn(null);

        return new PageCopyService($documentManager, $routeCache, new PageBlockCloner());
    }

    private function createChannel(string $id): Channel
    {
        $channel = new Channel();
        $channel->setId($id);
        $channel->setName($id);

        return $channel;
    }

    private function createPageWithBlock(string $id, string $path, string $blockId): Page
    {
        $block = new TextBlock();
        $block->setId($blockId);
        $block->setTitle($blockId);

        $item = (new Item())->setBlock($block);
        $grid = (new Grid('main'))->setItems([$item]);

        $page = new Page();
        $page->setPath($path);
        $page->setLayout('default.html.twig');
        $page->setTitle($id);
        $page->setGrids([$grid]);
        $property = new \ReflectionProperty(Page::class, 'id');
        $property->setValue($page, $id);

        return $page;
    }

    /**
     * @param list<object> $persisted
     */
    private function findPersistedPage(array $persisted): Page
    {
        foreach ($persisted as $document) {
            if ($document instanceof Page) {
                return $document;
            }
        }

        self::fail('No copied page was persisted.');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createRequest(array $data): PageCopyRequest
    {
        return (new PageCopyRequestFactory())->createFromFormData($data);
    }
}
