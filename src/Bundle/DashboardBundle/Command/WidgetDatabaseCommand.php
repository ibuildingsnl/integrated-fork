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
    private OutputInterface $output;

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
            ->setDescription('Fill WidgetConfig table');
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $widgets = $this->manager->getRepository(WidgetConfig::class)->findAll();
        $this->flushDB($widgets);
        $this->fillDB();
        return 0;
    }

    private function fillDB()
    {
        $order = 1;
        foreach ($this->widgets as $widget) {
            $widgetConfig = new WidgetConfig($widget->getId(), $widget->getName(), $order);
            $this->manager->persist($widgetConfig);
            $this->output->writeln('- Adding ' . $widget->getName() . ' to the database');
            $order++;
        }
        $this->manager->flush();
    }

    private function flushDB($widgets)
    {
        foreach ($widgets as $widget) {
            $this->manager->remove($widget);
        }
        $this->manager->flush();
    }
}
