<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\ContentEditDraft;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'integrated:content:draft:cleanup',
    description: 'Cleanup stale content drafts and prune draft versions',
)]
class ContentDraftCleanupCommand extends Command
{
    private const DEFAULT_DRAFT_MAX_AGE_DAYS = 90;
    private const DEFAULT_VERSION_MAX_AGE_DAYS = 30;
    private const DEFAULT_MAX_VERSIONS = 25;

    public function __construct(private readonly DocumentManager $documentManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'draft-max-age-days',
                null,
                InputOption::VALUE_OPTIONAL,
                'Remove draft documents older than this amount of days',
                (string) self::DEFAULT_DRAFT_MAX_AGE_DAYS
            )
            ->addOption(
                'version-max-age-days',
                null,
                InputOption::VALUE_OPTIONAL,
                'Prune draft versions older than this amount of days',
                (string) self::DEFAULT_VERSION_MAX_AGE_DAYS
            )
            ->addOption(
                'max-versions',
                null,
                InputOption::VALUE_OPTIONAL,
                'Keep at most this many versions per draft',
                (string) self::DEFAULT_MAX_VERSIONS
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Calculate cleanup result without writing changes'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $draftMaxAgeDays = max(1, (int) $input->getOption('draft-max-age-days'));
        $versionMaxAgeDays = max(1, (int) $input->getOption('version-max-age-days'));
        $maxVersions = max(1, (int) $input->getOption('max-versions'));
        $dryRun = (bool) $input->getOption('dry-run');

        $draftThreshold = new \DateTimeImmutable(\sprintf('-%d days', $draftMaxAgeDays));
        $versionThreshold = new \DateTimeImmutable(\sprintf('-%d days', $versionMaxAgeDays));

        $scanned = 0;
        $removedDrafts = 0;
        $prunedVersions = 0;

        $result = $this->documentManager
            ->createQueryBuilder(ContentEditDraft::class)
            ->getQuery()
            ->execute();

        if (!\is_iterable($result)) {
            $result = [];
        }

        foreach ($result as $draft) {
            if (!$draft instanceof ContentEditDraft) {
                continue;
            }
            ++$scanned;

            if ($draft->getUpdatedAt()->getTimestamp() < $draftThreshold->getTimestamp()) {
                ++$removedDrafts;
                if (!$dryRun) {
                    $this->documentManager->remove($draft);
                }

                continue;
            }

            $removed = $draft->pruneVersions($versionThreshold, $maxVersions);
            $prunedVersions += $removed;

            if ($removed > 0 && !$dryRun) {
                $this->documentManager->persist($draft);
            }
        }

        if (!$dryRun) {
            $this->documentManager->flush();
        }

        $output->writeln(\sprintf('Drafts scanned: %d', $scanned));
        $output->writeln(\sprintf('Drafts removed: %d', $removedDrafts));
        $output->writeln(\sprintf('Versions pruned: %d', $prunedVersions));
        $output->writeln(\sprintf('Dry-run: %s', $dryRun ? 'yes' : 'no'));

        return self::SUCCESS;
    }
}
