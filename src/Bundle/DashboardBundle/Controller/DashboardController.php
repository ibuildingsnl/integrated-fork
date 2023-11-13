<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\DashboardBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\DashboardBundle\Document\WidgetConfig;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Integrated\Common\Content\Channel\ChannelInterface;
use function Deployer\writeln;

class DashboardController extends AbstractController
{
    private array $widgets;

    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly DocumentManager         $manager,
        private readonly iterable                $allWidgets,
    )
    {
        /** @var WidgetInterface $widget */
        foreach ($this->allWidgets as $widget) {
            $this->widgets[$widget->name()] = $widget;
        }
    }

    public function index(Request $request): Response
    {
        $this->CheckWidgetDataBase();

        $user = $this->getUser();
        $selectChannelForm = $this->createForm(ChannelChoiceType::class, null, [
            'multiple' => false,
            'return_object' => true, // true = object, false = ID
        ]);
        $channel = $this->getChannel($request);
        $widgetAllData = $this->renderWidgets($channel, $user, $request);
        return $this->renderDashboardView($channel->getName(), $widgetAllData, $selectChannelForm);
    }

    private function CheckWidgetDataBase()
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
    }

    private function getChannel($request): ChannelInterface
    {
        $selectedByFormChannel = $request->query->get('integrated_channel_choice');
        return $this->manager->getRepository(Channel::class)->find($selectedByFormChannel) ?? $this->channelContext->getChannel();
    }

    /**
     * @throws MongoDBException
     */
    private function renderWidgets(ChannelInterface $channel, $user, Request $request): array
    {
        $widgetAllData = [];
        $widgetConfigs = $this->manager->getRepository(WidgetConfig::class)
            ->createQueryBuilder()
            ->sort('order', 'asc')
            ->getQuery()
            ->execute();

        foreach ($widgetConfigs as $config) {
            $widget = $this->widgets[$config->getWidgetName()] ?? null;
            if ($widget) {
                $widgetAllData[] = [
                    'id' => $config->getWidgetId(),
                    'name' => $config->getWidgetName(),
                    'renderedView' => $this->renderView($widget->view(), $widget->params($channel, $user, $request)),
                ];
            }
        }
        return $widgetAllData;
    }

    private function renderDashboardView(string $channelName, array $widgetAllData, $selectChannelForm): Response
    {
        return $this->render('@IntegratedDashboard/index.html.twig', [
            "channelName" => $channelName,
            "channelForm" => $selectChannelForm->createView(),
            "widgetAllData" => $widgetAllData
        ]);
    }
}
