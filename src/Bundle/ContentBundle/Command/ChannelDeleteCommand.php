<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionProcessor;
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
            $summary = $this->channelDeletionProcessor->process($channel, $deleteReferenced);
        } catch (\Throwable $exception) {
            $this->logger->error('Background channel deletion failed', [
                'channel_id' => $channelId,
                'delete_referenced' => $deleteReferenced,
                'exception' => $exception,
            ]);
            $output->writeln('<error>Channel deletion failed: '.$exception->getMessage().'</error>');

            return self::FAILURE;
        }

        $output->writeln(\sprintf(
            'Channel "%s" deleted. removed_content=%d detached_content=%d removed_pages=%d removed_publications=%d updated_brands=%d',
            $channelId,
            $summary['removed_content'],
            $summary['detached_content'],
            $summary['removed_pages'],
            $summary['removed_publications'],
            $summary['updated_brands']
        ));

        return self::SUCCESS;
    }
}
