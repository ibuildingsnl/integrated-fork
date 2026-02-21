<?php

namespace Integrated\Bundle\ThemeBundle\Command;

use Integrated\Bundle\ThemeBundle\Scraper\Scraper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'scraper:run',
    description: 'Scrape scraper pages',
)]
class ScraperRunCommand extends Command
{
    private Scraper $scraper;

    public function __construct(Scraper $scraper)
    {
        $this->scraper = $scraper;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->scraper->run();

        return self::SUCCESS;
    }
}
