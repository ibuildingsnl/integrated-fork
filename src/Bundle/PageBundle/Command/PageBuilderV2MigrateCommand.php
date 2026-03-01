<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Migration\LayoutV1ToV2Mapper;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Migration\MigrationJournal;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;

#[AsCommand(name: 'pagebuilder:v2:migrate')]
final class PageBuilderV2MigrateCommand extends Command
{
    protected static $defaultName = 'pagebuilder:v2:migrate';

    private DocumentManager $documentManager;
    private LayoutV1ToV2Mapper $mapper;
    private LayoutPayloadValidator $validator;
    private MigrationJournal $journal;

    public function __construct(
        DocumentManager $documentManager,
        LayoutV1ToV2Mapper $mapper,
        LayoutPayloadValidator $validator,
        MigrationJournal $journal
    ) {
        $this->documentManager = $documentManager;
        $this->mapper = $mapper;
        $this->validator = $validator;
        $this->journal = $journal;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run pagebuilder v2 migration (dry-run or execute)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate migration mappings without writing')
            ->addOption('execute', null, InputOption::VALUE_NONE, 'Execute migration writes')
            ->addOption('batch', null, InputOption::VALUE_REQUIRED, 'Batch size', '250')
            ->addOption('resume-from', null, InputOption::VALUE_REQUIRED, 'Resume from migration cursor', '')
            ->addOption('snapshot-id', null, InputOption::VALUE_REQUIRED, 'Snapshot id for execute mode', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $execute = (bool) $input->getOption('execute');

        if ($dryRun === $execute) {
            $output->writeln('<error>Choose exactly one mode: --dry-run or --execute</error>');

            return self::INVALID;
        }

        $mode = $dryRun ? 'dry-run' : 'execute';
        $batch = (int) $input->getOption('batch');
        $resumeFrom = trim((string) $input->getOption('resume-from'));
        $snapshotId = trim((string) $input->getOption('snapshot-id'));
        if ($batch <= 0) {
            $output->writeln('<error>Batch must be a positive integer</error>');

            return self::INVALID;
        }

        if ($execute && $snapshotId === '') {
            $snapshotId = sprintf('snapshot-%s', gmdate('YmdHis'));
        }

        $output->writeln(sprintf(
            'pagebuilder:v2:migrate mode=%s batch=%d resume-from=%s',
            $mode,
            $batch,
            $resumeFrom !== '' ? $resumeFrom : '<start>'
        ));

        if ($execute) {
            $output->writeln(sprintf('pagebuilder:v2:migrate snapshot-id=%s', $snapshotId));
        }

        $pages = $this->loadPages();
        $summary = [
            'processed' => 0,
            'migrated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $cursorReached = $resumeFrom === '';

        foreach ($pages as $index => $page) {
            $pageId = trim((string) $page->getId());

            if (!$cursorReached) {
                if ($pageId !== $resumeFrom) {
                    continue;
                }

                $cursorReached = true;

                continue;
            }

            $summary['processed']++;

            if ($page->getLayoutVersion() === 2) {
                $errors = $this->validator->validate($page->getLayoutPayload(), 'default');
                if (\count($errors) === 0) {
                    $summary['skipped']++;
                    $this->journal->mark($pageId !== '' ? $pageId : sprintf('page-%d', $index), 'skipped', 'already_v2');

                    continue;
                }

                $summary['failed']++;
                $this->journal->mark($pageId !== '' ? $pageId : sprintf('page-%d', $index), 'failed', $errors[0]->message);

                continue;
            }

            $mapped = $this->mapper->map([
                'grids' => $this->serializeGrids($page),
            ]);
            $errors = $this->validator->validate((array) ($mapped['payload'] ?? []), 'default');

            if (\count($errors) > 0) {
                $summary['failed']++;
                $this->journal->mark($pageId !== '' ? $pageId : sprintf('page-%d', $index), 'failed', $errors[0]->message);

                continue;
            }

            if ($execute) {
                $this->applyMigration($page, $mapped, $snapshotId);
                $this->documentManager->persist($page);
            }

            $summary['migrated']++;
            $this->journal->mark($pageId !== '' ? $pageId : sprintf('page-%d', $index), 'migrated', $dryRun ? 'dry-run' : 'executed');

            if ($execute && $summary['migrated'] % $batch === 0) {
                $this->documentManager->flush();
            }
        }

        if ($resumeFrom !== '' && !$cursorReached) {
            $output->writeln(sprintf('<error>Resume cursor "%s" not found</error>', $resumeFrom));

            return self::INVALID;
        }

        if ($execute && $summary['migrated'] > 0 && $summary['migrated'] % $batch !== 0) {
            $this->documentManager->flush();
        }

        $output->writeln(sprintf(
            'pagebuilder:v2:migrate summary processed=%d migrated=%d skipped=%d failed=%d',
            $summary['processed'],
            $summary['migrated'],
            $summary['skipped'],
            $summary['failed']
        ));

        if ($summary['failed'] > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @param array{payload?: array<string, mixed>, meta?: array<string, mixed>} $mapped
     */
    private function applyMigration(AbstractPage $page, array $mapped, string $snapshotId): void
    {
        $legacy = $page->getLegacy();
        $snapshots = $legacy['snapshots'] ?? [];
        if (!\is_array($snapshots)) {
            $snapshots = [];
        }

        $snapshots[$snapshotId] = [
            'layoutVersion' => $page->getLayoutVersion(),
            'layoutPayload' => $page->getLayoutPayload(),
            'layoutMeta' => $page->getLayoutMeta(),
            'grids' => $this->serializeGrids($page),
        ];

        $legacy['snapshots'] = $snapshots;
        if (!isset($legacy['grids']) || !\is_array($legacy['grids'])) {
            $legacy['grids'] = $this->serializeGrids($page);
        }

        $meta = \is_array($mapped['meta'] ?? null) ? $mapped['meta'] : [];
        $meta['snapshotId'] = $snapshotId;

        $page->setLayoutVersion(2);
        $page->setLayoutPayload(\is_array($mapped['payload'] ?? null) ? $mapped['payload'] : []);
        $page->setLayoutMeta($meta);
        $page->setLegacy($legacy);
        $page->updateBlockIdsFromLayoutPayload();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializeGrids(AbstractPage $page): array
    {
        $grids = [];
        foreach ($page->getGrids() as $grid) {
            if (\is_object($grid) && method_exists($grid, 'toArray')) {
                $grids[] = $grid->toArray();

                continue;
            }

            if (\is_array($grid)) {
                $grids[] = $grid;
            }
        }

        return $grids;
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
