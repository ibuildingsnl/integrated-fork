<?php

namespace Integrated\Bundle\DashboardBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Client;
use Integrated\Bundle\DashboardBundle\Document\WidgetConfig;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Deployer\output;
use function Deployer\writeln;

class WidgetDatabaseCommand extends Command
{
    private array $widgets;

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly iterable        $allWidgets,

    )
    {
        parent::__construct();
        /** @var WidgetInterface $widget */
        foreach ($this->allWidgets as $widget) {
            $this->widgets[$widget->getName()] = $widget;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('widget:database')
            ->setDescription('Fill or update WidgetConfig table')
            ->addOption('update', 'u', null, "Delete table content and refill it");
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $widgets = $this->manager->getRepository(WidgetConfig::class)->findAll();
        if ($input->hasOption('update')) {
            foreach ($widgets as $widget)             {
                $this->manager->remove($widget);
            }
            $this->manager->flush();
        }
        $this->fillDB($output, $widgets);
        return 0;
    }

    private function fillDB(OutputInterface $output, $widgets)
    {
        if (count($widgets) == 0) {
            $order = 1;
            foreach ($this->widgets as $widget) {
                $widgetConfig = new WidgetConfig($widget->getId(), $widget->getName(), $order);
                $this->manager->persist($widgetConfig);
                $order++;
            }
            $this->manager->flush();
        } else {
            $output->writeln('The WidgetConfig table is not empty, run the --u option to refill it');
        }
    }
}
