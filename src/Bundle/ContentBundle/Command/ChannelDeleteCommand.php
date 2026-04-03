<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionProcessor;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionReport;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'integrated:content:channel:delete',
    description: 'Delete a channel and optionally remove related documents in background processing',
)]
final class ChannelDeleteCommand extends Command
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly ChannelDeletionProcessor $channelDeletionProcessor,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('channel-id', null, InputOption::VALUE_REQUIRED, 'Channel ID to delete')
            ->addOption('delete-referenced', null, InputOption::VALUE_NONE, 'Also remove related documents');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $channelId = trim((string) $input->getOption('channel-id'));
        if ($channelId === '') {
            $output->writeln('<error>Missing required option: --channel-id</error>');

            return self::FAILURE;
        }

        $channel = $this->documentManager->getRepository(Channel::class)->find($channelId);
        if (!$channel instanceof Channel) {
            $output->writeln(\sprintf('<info>Channel "%s" no longer exists; skipping.</info>', $channelId));

            return self::SUCCESS;
        }

        $deleteReferenced = (bool) $input->getOption('delete-referenced');

        try {
            $report = $this->channelDeletionProcessor->process($channel, $deleteReferenced);
        } catch (\Throwable $exception) {
            $this->logger->error('Background channel deletion failed', [
                'channel_id' => $channelId,
                'delete_referenced' => $deleteReferenced,
                'exception' => $exception,
            ]);
            $output->writeln('<error>Channel deletion failed: '.$exception->getMessage().'</error>');

            return self::FAILURE;
        }

        $line = self::formatReportLine($report, $output->isVerbose());

        match ($report->getStatus()) {
            'success' => $output->writeln('<info>'.$line.'</info>'),
            'success_with_warnings' => $output->writeln('<comment>'.$line.'</comment>'),
            default => $output->writeln('<error>'.$line.'</error>'),
        };

        return self::resolveExitCode($report);
    }

    private static function resolveExitCode(ChannelDeletionReport $report): int
    {
        return match ($report->getStatus()) {
            'failed' => self::FAILURE,
            default => self::SUCCESS,
        };
    }

    private static function formatReportLine(ChannelDeletionReport $report, bool $verbose): string
    {
        $line = \sprintf(
            'Channel "%s" deleted. status=%s removed_content=%d detached_content=%d removed_pages=%d removed_publications=%d updated_brands=%d warnings=%d',
            $report->getChannelId(),
            $report->getStatus(),
            $report->getRemovedContent(),
            $report->getDetachedContent(),
            $report->getRemovedPages(),
            $report->getRemovedPublications(),
            $report->getUpdatedBrands(),
            $report->getWarningCount()
        );

        if (!$verbose || $report->getWarnings() === []) {
            return $line;
        }

        $warningSummary = array_map(
            static fn ($warning): string => \sprintf(
                '%s %s(%s): %s',
                $warning->getStep(),
                $warning->getDocumentClass(),
                $warning->getDocumentId(),
                $warning->getMessage()
            ),
            $report->getWarnings()
        );

        return $line.' warning_summary="'.implode('; ', $warningSummary).'"';
    }
}
