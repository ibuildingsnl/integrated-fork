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

class WidgetDatabaseCommand extends Command
{
    private array $widgets;
    public function __construct(
        private readonly DocumentManager  $manager,
        private readonly iterable $allWidgets,

    )
    {
        parent::__construct();
        /** @var WidgetInterface $widget */
        foreach ($this->allWidgets as $widget) {
            $this->widgets[$widget->name()] = $widget;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('widget:database')
            ->setDescription('Fill or update WidgetConfig table');
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $widgetDB = $this->manager->getRepository(WidgetConfig::class)->findAll();
        if (count($widgetDB) == 0)
        {
            $order = 1;
            foreach ($this->widgets as $widget)
            {
                $widgetConfig = new WidgetConfig($widget->id(), $widget->name(), $order);
                $this->manager->persist($widgetConfig);
                $order++;
            }
            $this->manager->flush();
        }
        return 0;
    }
}
