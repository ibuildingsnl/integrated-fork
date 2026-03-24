<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\PageBuilder\V2;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\PageBundle\Command\PageBuilderV2RollbackCommand;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class PageBuilderV2RollbackCommandTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
    }

    public function testRollbackRestoresPagesFromSnapshot(): void
    {
        $page = new Page();
        $page->setLayoutVersion(2);
        $page->setLayoutPayload([
            'root' => [
                'type' => 'container',
                'children' => [],
            ],
        ]);
        $page->setLayoutMeta(['migratedAt' => '2026-03-01T00:00:00+00:00']);
        $page->setLegacy([
            'snapshots' => [
                'snap-1' => [
                    'layoutVersion' => 1,
                    'layoutPayload' => [],
                    'layoutMeta' => [],
                ],
            ],
        ]);

        $this->stubRepositories([$page], []);
        $this->documentManager->expects(self::once())->method('persist')->with($page);
        $this->documentManager->expects(self::once())->method('flush');

        $tester = new CommandTester(new PageBuilderV2RollbackCommand($this->documentManager));
        $exitCode = $tester->execute(['--from-snapshot' => 'snap-1']);

        self::assertSame(0, $exitCode);
        self::assertSame(1, $page->getLayoutVersion());
        self::assertSame([], $page->getLayoutPayload());
    }

    public function testRollbackFailsWhenSnapshotIsNotFound(): void
    {
        $page = new Page();
        $page->setLayoutVersion(2);
        $page->setLayoutPayload([
            'root' => [
                'type' => 'container',
                'children' => [],
            ],
        ]);

        $this->stubRepositories([$page], []);
        $this->documentManager->expects(self::never())->method('persist');
        $this->documentManager->expects(self::never())->method('flush');

        $tester = new CommandTester(new PageBuilderV2RollbackCommand($this->documentManager));
        $exitCode = $tester->execute(['--from-snapshot' => 'missing']);

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('No pages matched snapshot', $tester->getDisplay());
    }

    /**
     * @param array<int, Page> $pages
     * @param array<int, ContentTypePage> $contentTypePages
     */
    private function stubRepositories(array $pages, array $contentTypePages): void
    {
        $pageRepository = $this->createMock(DocumentRepository::class);
        $pageRepository->method('findAll')->willReturn($pages);

        $contentTypePageRepository = $this->createMock(DocumentRepository::class);
        $contentTypePageRepository->method('findAll')->willReturn($contentTypePages);

        $this->documentManager
            ->method('getRepository')
            ->willReturnMap([
                [Page::class, $pageRepository],
                [ContentTypePage::class, $contentTypePageRepository],
            ]);
    }
}

