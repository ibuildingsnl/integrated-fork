<?php

namespace Integrated\Bundle\TaxonomyBundle\Command;

use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CleanupCommand extends Command
{
    public function __construct(
        private readonly TaxonomyRepositoryInterface $taxonomies,
        private readonly ObjectManager $manager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('taxonomy:cleanup')
            ->addArgument('type', InputArgument::REQUIRED, 'The taxonomy content type to clean up')
            ->addArgument('max', InputArgument::OPTIONAL, 'The maximum usage count to be considered unused', 0)
            ->addArgument('batch', InputArgument::OPTIONAL, 'The batch size (change in case of memory errors)', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $max = $input->getArgument('max');
        $batch = $input->getArgument('batch');
        $type = $input->getArgument('type');
        $i = 0;
        do {
            $items = $this->taxonomies->paged($type, $i, $batch);
            foreach ($items as $taxonomy) {
                if ($this->taxonomies->countUsages($taxonomy) <= $max) {
                    $this->manager->remove($taxonomy);
                }
            }
            $this->manager->flush();
            $i += $batch;
        } while (!empty($items));
        $this->manager->flush();

        return 0;
    }
}
