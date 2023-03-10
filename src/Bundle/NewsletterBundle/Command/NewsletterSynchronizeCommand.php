<?php

namespace Integrated\Bundle\NewsletterBundle\Command;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\NewsletterUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NewsletterSynchronizeCommand extends Command
{
    public function __construct(
        private readonly NewsletterUpdater $updater,
        private readonly ObjectRepository $newsletters,
    ) {
        $this->setDescription('Synchronizes the newsletters with the provider.');
        parent::__construct('newsletter:synchronize');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->newsletters->findAll() as $newsletter) {
            assert($newsletter instanceof Newsletter);
            $this->updater->update($newsletter);
        }

        return 0;
    }
}
