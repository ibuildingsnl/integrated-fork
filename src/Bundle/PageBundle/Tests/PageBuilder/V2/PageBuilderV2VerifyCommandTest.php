<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\PageBuilder\V2;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\PageBundle\Command\PageBuilderV2VerifyCommand;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class PageBuilderV2VerifyCommandTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    private LayoutPayloadValidator $validator;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->validator = new LayoutPayloadValidator(__DIR__.'/../../../Resources/schema/pagebuilder/v2');
    }

    public function testVerifyReturnsSuccessWhenAllPagesAreValidV2(): void
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

        $tester = new CommandTester(new PageBuilderV2VerifyCommand($this->documentManager, $this->validator));
        $exitCode = $tester->execute([]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('invalid=0', $tester->getDisplay());
        self::assertStringContainsString('pending=0', $tester->getDisplay());
    }

    public function testVerifyReturnsFailureWhenPendingV1PagesExist(): void
    {
        $page = new Page();
        $page->setLayoutVersion(1);

        $this->stubRepositories([$page], []);

        $tester = new CommandTester(new PageBuilderV2VerifyCommand($this->documentManager, $this->validator));
        $exitCode = $tester->execute([]);

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('pending=1', $tester->getDisplay());
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

