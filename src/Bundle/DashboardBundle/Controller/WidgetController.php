<?php

namespace Bundle\DashboardBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


class WidgetController extends AbstractController
{
    private $widgets;
    private $manager;

    public function __construct(iterable $widgets, DocumentManager $manager)
    {
        $this->widgets = $widgets;
        $this->manager = $manager;
    }

    public function index(ChannelInterface $channel): Response
    {
        $configs = $this->manager->findAll();

        $widgetParams = [];

        foreach ($configs as $config) {
            $widgetName = $config->getWidgetName();
            if (isset($this->widgets[$widgetName]))
            {
                $widget = $this->widgets[$widgetName];
                $widgetParams[$widgetName] = $widget->params($channel);
            }
        }

        return $this->render('widget/index.html.twig', [
            'widgetParams' => $widgetParams,
        ]);
    }
}
