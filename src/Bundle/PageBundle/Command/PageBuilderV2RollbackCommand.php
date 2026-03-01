<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pagebuilder:v2:rollback')]
final class PageBuilderV2RollbackCommand extends Command
{
    protected static $defaultName = 'pagebuilder:v2:rollback';

    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Rollback pagebuilder v2 migration from a snapshot id')
            ->addOption('from-snapshot', null, InputOption::VALUE_REQUIRED, 'Snapshot id to rollback from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $snapshotId = (string) $input->getOption('from-snapshot');
        if ($snapshotId === '') {
            $output->writeln('<error>--from-snapshot is required</error>');

            return self::INVALID;
        }

        $restored = 0;

        foreach ($this->loadPages() as $page) {
            $legacy = $page->getLegacy();
            $snapshot = $legacy['snapshots'][$snapshotId] ?? null;

            if (!\is_array($snapshot)) {
                continue;
            }

            $layoutVersion = (int) ($snapshot['layoutVersion'] ?? 1);
            $layoutPayload = \is_array($snapshot['layoutPayload'] ?? null) ? $snapshot['layoutPayload'] : [];
            $layoutMeta = \is_array($snapshot['layoutMeta'] ?? null) ? $snapshot['layoutMeta'] : [];

            $page->setLayoutVersion($layoutVersion);
            $page->setLayoutPayload($layoutPayload);
            $page->setLayoutMeta($layoutMeta);

            if ($layoutVersion === 2) {
                $page->updateBlockIdsFromLayoutPayload();
            } else {
                $page->updateBlockIdsFromGrids();
            }

            $this->documentManager->persist($page);
            $restored++;
        }

        if ($restored === 0) {
            $output->writeln(sprintf('<error>No pages matched snapshot %s</error>', $snapshotId));

            return self::FAILURE;
        }

        $this->documentManager->flush();
        $output->writeln(sprintf('pagebuilder:v2:rollback snapshot=%s restored=%d', $snapshotId, $restored));

        return self::SUCCESS;
    }

    /**
     * @return array<int, AbstractPage>
     */
    private function loadPages(): array
    {
        $pages = [];

        foreach ((array) $this->documentManager->getRepository(Page::class)->findAll() as $page) {
            if ($page instanceof AbstractPage) {
                $pages[] = $page;
            }
        }

        foreach ((array) $this->documentManager->getRepository(ContentTypePage::class)->findAll() as $page) {
            if ($page instanceof AbstractPage) {
                $pages[] = $page;
            }
        }

        return $pages;
    }
}
