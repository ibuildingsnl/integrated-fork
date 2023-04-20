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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $max = $input->getArgument('max');
        $batch = $input->getArgument('batch');
        $i = 0;
        foreach ($this->taxonomies->byType($input->getArgument('type')) as $taxonomy) {
            if ($this->taxonomies->countUsages($taxonomy) <= $max) {
                $this->manager->remove($taxonomy);
            } else {
                $this->manager->detach($taxonomy);
            }
            ++$i;
            if ($i > $batch) {
                $this->manager->flush();
                $i = 0;
            }
        }
        $this->manager->flush();

        return 0;
    }
}
