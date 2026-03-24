<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\PageBuilder\V2;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\PageBundle\Command\PageBuilderV2MigrateCommand;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Migration\LayoutV1ToV2Mapper;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Migration\MigrationJournal;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class PageBuilderV2MigrateCommandTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    private LayoutPayloadValidator $validator;
    private LayoutV1ToV2Mapper $mapper;
    private MigrationJournal $journal;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->validator = new LayoutPayloadValidator(__DIR__.'/../../../Resources/schema/pagebuilder/v2');
        $this->mapper = new LayoutV1ToV2Mapper();
        $this->journal = new MigrationJournal();
    }

    public function testDefinesRequiredMigrationOptions(): void
    {
        $command = $this->createCommand();
        $definition = $command->getDefinition();

        self::assertTrue($definition->hasOption('dry-run'));
        self::assertTrue($definition->hasOption('execute'));
        self::assertTrue($definition->hasOption('batch'));
        self::assertTrue($definition->hasOption('resume-from'));
    }

    public function testDryRunEvaluatesPageWithoutWriting(): void
    {
        $page = new Page();
        $page->setGrids([new Grid('main')]);
        $this->setPageId($page, 'page-1');

        $this->stubRepositories([$page], []);

        $this->documentManager->expects(self::never())->method('persist');
        $this->documentManager->expects(self::never())->method('flush');

        $tester = new CommandTester($this->createCommand());
        $exitCode = $tester->execute(['--dry-run' => true]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('processed=1', $tester->getDisplay());
        self::assertStringContainsString('migrated=1', $tester->getDisplay());
    }

    public function testExecuteMigratesPageAndStoresSnapshot(): void
    {
        $page = new Page();
        $page->setGrids([new Grid('main')]);
        $this->setPageId($page, 'page-2');

        $this->stubRepositories([$page], []);

        $this->documentManager->expects(self::once())->method('persist')->with($page);
        $this->documentManager->expects(self::once())->method('flush');

        $tester = new CommandTester($this->createCommand());
        $exitCode = $tester->execute(['--execute' => true, '--snapshot-id' => 'snap-1']);

        self::assertSame(0, $exitCode);
        self::assertSame(2, $page->getLayoutVersion());
        self::assertSame('container', $page->getLayoutPayload()['root']['type']);
        self::assertArrayHasKey('snap-1', $page->getLegacy()['snapshots']);
    }

    private function createCommand(): PageBuilderV2MigrateCommand
    {
        return new PageBuilderV2MigrateCommand(
            $this->documentManager,
            $this->mapper,
            $this->validator,
            $this->journal
        );
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

    private function setPageId(Page $page, string $id): void
    {
        $reflection = new \ReflectionProperty($page, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($page, $id);
    }
}
