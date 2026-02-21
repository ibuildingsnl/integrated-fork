<?php

namespace Integrated\Bundle\BrandBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'integrated:brand:cleanup-channel-links',
    description: 'Remove brand channel links that have null or a specific channel',
)]
class CleanupBrandChannelLinksCommand extends Command
{
    protected static $defaultName = 'integrated:brand:cleanup-channel-links';

    public function __construct(private DocumentManager $documentManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Remove brand channel links that have null or a specific channel')
            ->addOption('channel-id', null, InputOption::VALUE_OPTIONAL, 'Remove links for a specific channel id')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be removed without saving');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $channelId = $input->getOption('channel-id');
        $dryRun = (bool) $input->getOption('dry-run');

        $brands = $this->documentManager->getRepository(Brand::class)->findAll();
        $brandsTouched = 0;
        $linksRemoved = 0;
        $report = [];

        foreach ($brands as $brand) {
            $changed = false;
            foreach ($brand->getChannelLinks()->toArray() as $link) {
                if (!$link->channel) {
                    $report[] = sprintf(
                        'brand=%s (%s) link=%s type=%s channel=null',
                        $brand->getId(),
                        $brand->getName(),
                        $link->getId(),
                        $link->getName()
                    );
                    $brand->removeChannelLink($link);
                    $linksRemoved++;
                    $changed = true;
                    continue;
                }
                if ($channelId && $link->channel->getId() === $channelId) {
                    $report[] = sprintf(
                        'brand=%s (%s) link=%s type=%s channel=%s (%s)',
                        $brand->getId(),
                        $brand->getName(),
                        $link->getId(),
                        $link->getName(),
                        $link->channel->getId(),
                        $link->channel->getName()
                    );
                    $brand->removeChannelLink($link);
                    $linksRemoved++;
                    $changed = true;
                }
            }
            if ($changed) {
                $brandsTouched++;
                if (!$dryRun) {
                    $this->documentManager->persist($brand);
                }
            }
        }

        if (!$dryRun && $brandsTouched > 0) {
            $this->documentManager->flush();
        }

        if ($report) {
            $output->writeln('Links:');
            foreach ($report as $line) {
                $output->writeln(' - ' . $line);
            }
        }

        if ($dryRun) {
            $output->writeln(sprintf('Dry run: would remove %d link(s) from %d brand(s).', $linksRemoved, $brandsTouched));
        } else {
            $output->writeln(sprintf('Removed %d link(s) from %d brand(s).', $linksRemoved, $brandsTouched));
        }

        return Command::SUCCESS;
    }
}
